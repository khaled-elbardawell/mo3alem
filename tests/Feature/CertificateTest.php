<?php

use App\Models\Certificate;
use App\Models\DailyMetric;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function certificatePayload(array $overrides = []): array
{
    return array_replace_recursive([
        'title' => 'شهادة تفوق الفصل الأول',
        'template_key' => 'b6',
        'design' => [
            'width' => 1120,
            'height' => 790,
            'elements' => [[
                'id' => 'student-name',
                'type' => 'text',
                'text' => 'سارة خالد',
                'x' => 210,
                'y' => 320,
                'width' => 700,
                'height' => 90,
                'font_size' => 54,
                'font_family' => 'Tajawal',
                'font_weight' => 900,
                'color' => '#6d28d9',
                'text_align' => 'center',
                'direction' => 'rtl',
                'rotation' => 0,
                'opacity' => 1,
                'locked' => false,
            ]],
        ],
    ], $overrides);
}

test('a verified user can save update and delete an encrypted certificate design', function () {
    $user = User::factory()->create();

    $created = $this->actingAs($user)
        ->postJson(route('certificates.store'), certificatePayload())
        ->assertCreated()
        ->assertJsonPath('data.title', 'شهادة تفوق الفصل الأول')
        ->assertJsonPath('data.version', 1)
        ->json('data');

    $certificate = Certificate::query()->findOrFail($created['id']);
    expect($certificate->design['elements'][0]['text'])->toBe('سارة خالد')
        ->and($certificate->getRawOriginal('design'))->not->toContain('سارة خالد');

    $this->actingAs($user)
        ->patchJson(route('certificates.update', $certificate), [
            ...certificatePayload(['title' => 'شهادة التفوق المعدّلة']),
            'version' => 1,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.version', 2)
        ->assertJsonPath('data.title', 'شهادة التفوق المعدّلة');

    $this->actingAs($user)
        ->deleteJson(route('certificates.destroy', $certificate))
        ->assertNoContent();

    $this->assertSoftDeleted($certificate);
});

test('certificate writes require a verified owner', function () {
    $unverified = User::factory()->unverified()->create();
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $certificate = Certificate::factory()->for($owner)->create();

    $this->actingAs($unverified)
        ->postJson(route('certificates.store'), certificatePayload())
        ->assertForbidden();

    $this->actingAs($intruder)
        ->getJson(route('certificates.show', $certificate))
        ->assertForbidden();

    $this->actingAs($intruder)
        ->patchJson(route('certificates.update', $certificate), [...certificatePayload(), 'version' => 1])
        ->assertForbidden();
});

test('stale certificate updates return the latest server copy', function () {
    $user = User::factory()->create();
    $certificate = Certificate::factory()->for($user)->create(['version' => 3]);

    $this->actingAs($user)
        ->patchJson(route('certificates.update', $certificate), [...certificatePayload(), 'version' => 2])
        ->assertConflict()
        ->assertJsonPath('conflict', true)
        ->assertJsonPath('data.version', 3);
});

test('custom backgrounds are validated stored privately and authorized', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($user)->post(route('certificates.store'), [
        ...certificatePayload([
            'template_key' => 'custom',
            'design' => ['width' => 1200, 'height' => 800],
        ]),
        'background' => UploadedFile::fake()->image('background.png', 1200, 800),
    ], ['Accept' => 'application/json']);

    $response->assertCreated();
    $certificate = Certificate::query()->findOrFail($response->json('data.id'));
    Storage::disk('local')->assertExists($certificate->background_path);

    $this->actingAs($user)->get(route('certificates.background', $certificate))->assertSuccessful();
    $this->actingAs($otherUser)->get(route('certificates.background', $certificate))->assertForbidden();

    $this->actingAs($user)
        ->postJson(route('certificates.store'), certificatePayload(['template_key' => 'custom']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('background');
});

test('certificate design fields and uploads are constrained', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('certificates.store'), certificatePayload([
            'template_key' => 'outside',
            'design' => [
                'elements' => [[
                    'font_family' => 'Remote Font',
                    'color' => 'transparent',
                    'font_size' => 400,
                ]],
            ],
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'template_key',
            'design.elements.0.font_family',
            'design.elements.0.color',
            'design.elements.0.font_size',
        ]);
});

test('certificate generation and saving update separate platform metrics', function () {
    $user = User::factory()->create();

    $this->postJson(route('activity-metrics.store'), ['event' => 'certificate_generate'])->assertNoContent();
    $this->actingAs($user)->postJson(route('certificates.store'), certificatePayload())->assertCreated();

    $metrics = DailyMetric::query()->where('date', today()->toDateString())->firstOrFail();
    expect($metrics->certificate_generated)->toBe(1)
        ->and($metrics->certificate_saved)->toBe(1);
});
