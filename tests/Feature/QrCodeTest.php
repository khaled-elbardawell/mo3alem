<?php

use App\Models\DailyMetric;
use App\Models\QrCode;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
