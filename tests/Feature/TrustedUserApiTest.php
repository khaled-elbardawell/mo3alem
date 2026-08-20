<?php

use App\Models\AdminAuditLog;
use App\Models\ApiClient;
use App\Models\DailyMetric;
use App\Models\ExternalUserLink;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

function trustedApiToken(ApiClient $apiClient, array $abilities = [ApiClient::AbilityCreateUsers], mixed $expiresAt = null): string
{
    return $apiClient->createToken('test-integration', $abilities, $expiresAt ?? now()->addDays(30))->plainTextToken;
}

test('unexpected api exceptions use the unified response without leaking details', function () {
    Route::get('/api/test-unexpected-error', function (): void {
        throw new RuntimeException('Sensitive exception details.');
    });

    $this->getJson('/api/test-unexpected-error')
        ->assertServerError()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'حدث خطأ غير متوقع.')
        ->assertJsonPath('data', null)
        ->assertJsonPath('errors', null)
        ->assertJsonPath('code', 'server_error')
        ->assertJsonPath('request_id', fn (string $requestId): bool => $requestId !== '')
        ->assertJsonMissingPath('exception')
        ->assertJsonMissingPath('trace');
});

test('the user creation api requires a bearer token', function () {
    $this->postJson(route('api.v1.users.store'), [
        'external_id' => 'remote-1',
        'name' => 'مستخدم خارجي',
        'email' => 'external@example.com',
    ])->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('data', null)
        ->assertJsonPath('errors', null)
        ->assertJsonPath('code', 'unauthenticated')
        ->assertJsonPath('request_id', fn (string $requestId): bool => $requestId !== '')
        ->assertJsonMissingPath('exception')
        ->assertJsonMissingPath('trace');
});

test('only an active trusted api client with the correct ability and ip may create users', function () {
    $payload = [
        'external_id' => 'remote-2',
        'name' => 'مستخدم خارجي',
        'email' => 'external2@example.com',
    ];

    $inactiveClient = ApiClient::factory()->create(['is_active' => false]);
    $this->withToken(trustedApiToken($inactiveClient))->postJson(route('api.v1.users.store'), $payload)
        ->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'api_client_forbidden');
    Auth::forgetGuards();

    $wrongAbilityClient = ApiClient::factory()->create();
    $this->withToken(trustedApiToken($wrongAbilityClient, ['users:read']))->postJson(route('api.v1.users.store'), $payload)
        ->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'token_ability_missing');
    Auth::forgetGuards();

    $restrictedClient = ApiClient::factory()->create(['allowed_ips' => ['203.0.113.25']]);
    $this->withToken(trustedApiToken($restrictedClient))->postJson(route('api.v1.users.store'), $payload)
        ->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'ip_not_allowed');
});

test('a browser user session cannot be used as an api client', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('api.v1.users.store'), [
            'external_id' => 'browser-session-user',
            'name' => 'مستخدم',
            'email' => 'browser-session@example.com',
        ])
        ->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'api_client_forbidden');
});

test('a trusted client creates a verified account and receives a temporary password once', function () {
    Notification::fake();
    $apiClient = ApiClient::factory()->create(['allowed_ips' => ['127.0.0.1']]);
    $plainTextToken = trustedApiToken($apiClient);

    $response = $this->withToken($plainTextToken)
        ->withHeader('X-Request-ID', 'request-user-1001')
        ->postJson(route('api.v1.users.store'), [
            'external_id' => 'remote-user-1001',
            'name' => 'مستخدم من الشريك',
            'email' => 'NEW.API.USER@EXAMPLE.COM',
        ])
        ->assertCreated()
        ->assertHeader('X-Request-ID', 'request-user-1001')
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'تم إنشاء المستخدم بنجاح.')
        ->assertJsonPath('data.external_id', 'remote-user-1001')
        ->assertJsonPath('data.email', 'new.api.user@example.com')
        ->assertJsonPath('data.created', true)
        ->assertJsonPath('errors', null)
        ->assertJsonPath('code', null)
        ->assertJsonPath('request_id', 'request-user-1001')
        ->assertJsonMissingPath('data.password');

    $user = User::query()->where('email', 'new.api.user@example.com')->firstOrFail();
    $link = ExternalUserLink::query()->whereBelongsTo($apiClient)->firstOrFail();
    $temporaryPassword = $response->json('data.temporary_password');

    expect($response->json('data.id'))->toBe($user->id)
        ->and($temporaryPassword)->toBeString()->toHaveLength(20)
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->must_change_password)->toBeTrue()
        ->and(Hash::check($temporaryPassword, $user->password))->toBeTrue()
        ->and($link->user_id)->toBe($user->id)
        ->and(AdminAuditLog::query()->where('action', 'api.user.created')->where('subject_id', $user->id)->exists())->toBeTrue()
        ->and((int) DailyMetric::query()->whereDate('date', today())->value('registrations'))->toBe(1);

    Notification::assertNothingSent();
});

test('repeating the same external id is idempotent and does not return credentials again', function () {
    Notification::fake();
    $apiClient = ApiClient::factory()->create();
    $plainTextToken = trustedApiToken($apiClient);
    $payload = [
        'external_id' => 'stable-external-id',
        'name' => 'مستخدم ثابت',
        'email' => 'stable@example.com',
    ];

    $this->withToken($plainTextToken)->postJson(route('api.v1.users.store'), $payload)->assertCreated();
    $user = User::query()->where('email', 'stable@example.com')->firstOrFail();

    $this->withToken($plainTextToken)->postJson(route('api.v1.users.store'), $payload)
        ->assertSuccessful()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.created', false)
        ->assertJsonMissingPath('data.temporary_password');

    expect(User::query()->where('email', 'stable@example.com')->count())->toBe(1)
        ->and(ExternalUserLink::query()->where('external_id', 'stable-external-id')->count())->toBe(1);

    Notification::assertNothingSent();
});

test('the api rejects conflicting identifiers existing emails and supplied passwords', function () {
    $apiClient = ApiClient::factory()->create();
    $plainTextToken = trustedApiToken($apiClient);

    $this->withToken($plainTextToken)->postJson(route('api.v1.users.store'), [
        'external_id' => 'conflict-id',
        'name' => 'المستخدم الأول',
        'email' => 'first@example.com',
    ])->assertCreated();

    $this->withToken($plainTextToken)->postJson(route('api.v1.users.store'), [
        'external_id' => 'conflict-id',
        'name' => 'المستخدم الثاني',
        'email' => 'second@example.com',
    ])->assertConflict()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'معرّف المستخدم الخارجي مستخدم مسبقًا ببيانات مختلفة.')
        ->assertJsonPath('data', null)
        ->assertJsonPath('errors', null)
        ->assertJsonPath('code', 'conflict')
        ->assertJsonMissingPath('exception')
        ->assertJsonMissingPath('trace');

    User::factory()->create(['email' => 'existing@example.com']);

    $this->withToken($plainTextToken)->postJson(route('api.v1.users.store'), [
        'external_id' => 'another-id',
        'name' => 'مستخدم موجود',
        'email' => 'existing@example.com',
    ])->assertConflict()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'conflict');

    $this->withToken($plainTextToken)->postJson(route('api.v1.users.store'), [
        'external_id' => 'password-id',
        'name' => 'مستخدم بكلمة مرور',
        'email' => 'password@example.com',
        'password' => 'DoNotAcceptThis123',
    ])->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('data', null)
        ->assertJsonPath('code', 'validation_error')
        ->assertJsonValidationErrors('password');
});

test('expired sanctum tokens cannot access the api', function () {
    $apiClient = ApiClient::factory()->create();
    $expiredToken = trustedApiToken($apiClient, expiresAt: now()->subMinute());

    $this->withToken($expiredToken)->postJson(route('api.v1.users.store'), [
        'external_id' => 'expired-token-user',
        'name' => 'مستخدم',
        'email' => 'expired@example.com',
    ])->assertUnauthorized();
});
