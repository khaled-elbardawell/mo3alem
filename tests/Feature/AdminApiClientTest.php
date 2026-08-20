<?php

use App\Models\AdminAuditLog;
use App\Models\ApiClient;
use App\Models\User;
use App\UserRole;
use Laravel\Sanctum\PersonalAccessToken;

test('an administrator creates an api client and receives its scoped token once', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post(route('admin.api-clients.store'), [
        'name' => 'الموقع التعليمي',
        'allowed_ips' => "203.0.113.10\n2001:db8::10",
        'token_expiration_days' => 30,
        'is_active' => 1,
    ])->assertRedirect(route('admin.api-clients.index'))
        ->assertSessionHas('plain_api_token');

    $apiClient = ApiClient::query()->where('name', 'الموقع التعليمي')->firstOrFail();
    $token = $apiClient->tokens()->sole();
    $plainTextToken = $response->getSession()->get('plain_api_token');

    expect($apiClient->allowed_ips)->toBe(['203.0.113.10', '2001:db8::10'])
        ->and($token->abilities)->toBe([ApiClient::AbilityCreateUsers])
        ->and($token->expires_at->isBetween(now()->addDays(29), now()->addDays(31)))->toBeTrue()
        ->and($token->token)->toBe(hash('sha256', str($plainTextToken)->after('|')->toString()))
        ->and($plainTextToken)->toStartWith($token->id.'|mo3alem_')
        ->and(AdminAuditLog::query()->where('action', 'api-client.created')->exists())->toBeTrue();
});

test('disabling an api client revokes every token immediately', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $apiClient = ApiClient::factory()->create();
    $apiClient->createToken('first', [ApiClient::AbilityCreateUsers]);
    $apiClient->createToken('second', [ApiClient::AbilityCreateUsers]);

    $this->actingAs($admin)->put(route('admin.api-clients.update', $apiClient), [
        'name' => $apiClient->name,
        'allowed_ips' => [],
        'token_expiration_days' => 30,
        'is_active' => 0,
    ])->assertRedirect(route('admin.api-clients.index'));

    expect($apiClient->fresh()->is_active)->toBeFalse()
        ->and($apiClient->tokens()->count())->toBe(0);
});

test('creating an inactive api client does not issue a usable token', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->post(route('admin.api-clients.store'), [
        'name' => 'موقع غير مفعّل',
        'allowed_ips' => [],
        'token_expiration_days' => 30,
        'is_active' => 0,
    ])->assertRedirect(route('admin.api-clients.index'))
        ->assertSessionMissing('plain_api_token');

    expect(ApiClient::query()->where('name', 'موقع غير مفعّل')->firstOrFail()->tokens()->count())->toBe(0);
});

test('rotating a token revokes the old token before returning a new one', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $apiClient = ApiClient::factory()->create();
    $oldToken = $apiClient->createToken('old', [ApiClient::AbilityCreateUsers])->accessToken;

    $response = $this->actingAs($admin)
        ->post(route('admin.api-clients.token.rotate', $apiClient))
        ->assertRedirect()
        ->assertSessionHas('plain_api_token');

    expect(PersonalAccessToken::query()->whereKey($oldToken)->exists())->toBeFalse()
        ->and($apiClient->tokens()->count())->toBe(1)
        ->and($response->getSession()->get('plain_api_token'))->not->toContain($oldToken->token);
});

test('api client administration validates ip addresses and is forbidden to regular users', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.api-clients.index'))->assertForbidden();

    $this->actingAs($admin)
        ->from(route('admin.api-clients.create'))
        ->post(route('admin.api-clients.store'), [
            'name' => 'ربط غير صالح',
            'allowed_ips' => 'not-an-ip',
            'token_expiration_days' => 365,
            'is_active' => 1,
        ])
        ->assertRedirect(route('admin.api-clients.create'))
        ->assertSessionHasErrors(['allowed_ips.0', 'token_expiration_days']);
});
