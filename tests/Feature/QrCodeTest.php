<?php

use App\Models\DailyMetric;
use App\Models\QrCode;
use App\Models\QrCodeDailyStat;
use App\Models\User;
use App\Services\VisitorIdentity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function qrPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'title' => 'رابط نشاط العلوم',
        'content_type' => 'url',
        'payload' => ['url' => 'https://example.com/activity'],
        'design' => [
            'style' => 'rounded',
            'foreground_color' => '#111827',
            'eye_color' => '#6d28d9',
            'background_color' => '#ffffff',
            'frame' => 'template-1',
            'center_type' => 'none',
            'center_text' => null,
        ],
    ], $overrides);
}

test('guests can render a styled qr preview without storing content', function () {
    $this->postJson(route('tools.qr.render'), qrPayload())
        ->assertSuccessful()
        ->assertJsonPath('svg', fn (string $svg): bool => str_contains($svg, '<svg'));

    expect(QrCode::query()->count())->toBe(0);
});

test('a verified user can save update and delete an encrypted qr design', function () {
    $user = User::factory()->create();

    $created = $this->actingAs($user)
        ->postJson(route('qr-codes.store'), qrPayload())
        ->assertCreated()
        ->assertJsonPath('data.title', 'رابط نشاط العلوم')
        ->assertJsonPath('data.version', 1)
        ->json('data');

    $qrCode = QrCode::query()->findOrFail($created['id']);
    expect($qrCode->payload)->toBe(['url' => 'https://example.com/activity'])
        ->and($qrCode->getRawOriginal('payload'))->not->toContain('example.com');

    $this->actingAs($user)
        ->patchJson(route('qr-codes.update', $qrCode), [
            ...qrPayload(['title' => 'النشاط المعدّل']),
            'version' => 1,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.version', 2)
        ->assertJsonPath('data.title', 'النشاط المعدّل');

    $this->actingAs($user)
        ->deleteJson(route('qr-codes.destroy', $qrCode))
        ->assertNoContent();

    $this->assertSoftDeleted($qrCode);
});

test('qr writes require a verified owner', function () {
    $unverified = User::factory()->unverified()->create();
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $qrCode = QrCode::factory()->for($owner)->create();

    $this->actingAs($unverified)
        ->postJson(route('qr-codes.store'), qrPayload())
        ->assertForbidden();

    $this->actingAs($intruder)
        ->getJson(route('qr-codes.show', $qrCode))
        ->assertForbidden();

    $this->actingAs($intruder)
        ->patchJson(route('qr-codes.update', $qrCode), [...qrPayload(), 'version' => 1])
        ->assertForbidden();
});

test('stale qr updates return the latest server copy', function () {
    $user = User::factory()->create();
    $qrCode = QrCode::factory()->for($user)->create(['version' => 3]);

    $this->actingAs($user)
        ->patchJson(route('qr-codes.update', $qrCode), [...qrPayload(), 'version' => 2])
        ->assertConflict()
        ->assertJsonPath('conflict', true)
        ->assertJsonPath('data.version', 3);
});

test('a user cannot save more than ten qr codes but may update an existing one', function () {
    $user = User::factory()->create();
    $maximumQrCodes = (int) config('resource_limits.qr_codes');
    $qrCodes = QrCode::factory()->count($maximumQrCodes)->for($user)->create();

    $this->actingAs($user)
        ->postJson(route('qr-codes.store'), qrPayload(['title' => 'رمز إضافي']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('title');

    $this->actingAs($user)
        ->patchJson(route('qr-codes.update', $qrCodes->first()), [
            ...qrPayload(['title' => 'رمز معدل']),
            'version' => 1,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'رمز معدل');
});

test('logos are validated stored privately and authorized', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($user)->post(route('qr-codes.store'), [
        ...qrPayload([
            'design' => ['center_type' => 'image'],
        ]),
        'logo' => UploadedFile::fake()->image('logo.png', 400, 400),
    ], ['Accept' => 'application/json']);

    $response->assertCreated();
    $qrCode = QrCode::query()->findOrFail($response->json('data.id'));
    Storage::disk('local')->assertExists($qrCode->logo_path);

    $this->actingAs($user)->get(route('qr-codes.logo', $qrCode))->assertSuccessful();
    $this->actingAs($otherUser)->get(route('qr-codes.logo', $qrCode))->assertForbidden();
});

test('qr generation and saving update separate platform metrics', function () {
    $user = User::factory()->create();

    $this->postJson(route('activity-metrics.store'), ['event' => 'qr_generate'])->assertNoContent();
    $this->actingAs($user)->postJson(route('qr-codes.store'), qrPayload())->assertCreated();

    $metrics = DailyMetric::query()->where('date', today()->toDateString())->firstOrFail();
    expect($metrics->qr_generated)->toBe(1)
        ->and($metrics->qr_saved)->toBe(1);
});

test('qr payload types and design options are validated', function () {
    $this->postJson(route('tools.qr.render'), qrPayload([
        'content_type' => 'url',
        'payload' => ['url' => 'javascript:alert(1)'],
        'design' => [
            'style' => 'elegant',
            'foreground_color' => 'transparent',
            'frame' => 'education',
        ],
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['payload.url', 'design.style', 'design.foreground_color', 'design.frame']);
});

test('a dynamic qr keeps one public url while its destination changes', function () {
    $user = User::factory()->create();

    $created = $this->actingAs($user)
        ->postJson(route('qr-codes.store'), qrPayload([
            'mode' => 'dynamic',
            'payload' => ['url' => 'https://example.com/first'],
            'is_active' => true,
        ]))
        ->assertCreated()
        ->assertJsonPath('data.mode', 'dynamic')
        ->assertJsonPath('data.is_active', true)
        ->json('data');

    $qrCode = QrCode::query()->findOrFail($created['id']);
    $publicUrl = route('qr.redirect', $qrCode->public_code);

    expect($created['public_url'])->toBe($publicUrl)
        ->and($qrCode->public_code)->toHaveLength(26);

    $this->actingAs($user)
        ->postJson(route('tools.qr.render'), qrPayload([
            'mode' => 'dynamic',
            'qr_code_id' => $qrCode->id,
            'payload' => ['url' => 'https://example.com/first'],
        ]))
        ->assertSuccessful()
        ->assertJsonPath('svg', fn (string $svg): bool => str_contains($svg, '<svg'));

    $this->actingAs($user)
        ->patchJson(route('qr-codes.update', $qrCode), [
            ...qrPayload([
                'mode' => 'dynamic',
                'payload' => ['url' => 'https://example.com/second'],
                'is_active' => true,
            ]),
            'version' => 1,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.public_url', $publicUrl);

    expect($qrCode->refresh()->public_code)->toBe($qrCode->public_code);

    $this->get($publicUrl)
        ->assertRedirect('https://example.com/second')
        ->assertHeader('Cache-Control', 'no-store, private');
});

test('dynamic qr scans record total and daily unique analytics', function () {
    $qrCode = QrCode::factory()->dynamic()->create();
    $visitorId = (string) Str::uuid();

    $this->withCookie(VisitorIdentity::COOKIE_NAME, $visitorId)
        ->get(route('qr.redirect', $qrCode->public_code))
        ->assertRedirect($qrCode->payload['url']);

    $this->withCookie(VisitorIdentity::COOKIE_NAME, $visitorId)
        ->get(route('qr.redirect', $qrCode->public_code))
        ->assertRedirect($qrCode->payload['url']);

    $dailyStat = QrCodeDailyStat::query()->whereBelongsTo($qrCode)->firstOrFail();

    expect($qrCode->refresh()->scan_count)->toBe(2)
        ->and($qrCode->unique_scan_count)->toBe(1)
        ->and($dailyStat->scans)->toBe(2)
        ->and($dailyStat->unique_scans)->toBe(1);
});

test('dynamic qr rejects non url content and unavailable links return gone', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('qr-codes.store'), qrPayload([
            'mode' => 'dynamic',
            'content_type' => 'text',
            'payload' => ['text' => 'Dynamic text'],
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('content_type');

    $inactiveQrCode = QrCode::factory()->dynamic()->create(['is_active' => false]);
    $expiredQrCode = QrCode::factory()->dynamic()->create(['expires_at' => now()->subMinute()]);

    $this->get(route('qr.redirect', $inactiveQrCode->public_code))->assertGone();
    $this->get(route('qr.redirect', $expiredQrCode->public_code))->assertGone();
});

test('a saved qr mode cannot be changed', function () {
    $user = User::factory()->create();
    $qrCode = QrCode::factory()->dynamic()->for($user)->create();

    $this->actingAs($user)
        ->patchJson(route('qr-codes.update', $qrCode), [
            ...qrPayload(['mode' => 'static']),
            'version' => 1,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('mode');
});
