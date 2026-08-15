<?php

use App\Models\Competition;
use App\Models\DailyMetric;
use App\Models\SavedWheel;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

function competitionPayload(SavedWheel $savedWheel, array $overrides = []): array
{
    return array_replace([
        'title' => 'مسابقة الفصل',
        'saved_wheel_id' => $savedWheel->id,
    ], $overrides);
}

test('a competition starts with a snapshot of the selected saved list', function () {
    $user = User::factory()->create();
    $savedWheel = SavedWheel::factory()->for($user)->create([
        'names' => ['أحمد', 'سارة'],
        'names_count' => 2,
    ]);

    $created = $this->actingAs($user)
        ->postJson(route('competitions.store'), competitionPayload($savedWheel))
        ->assertCreated()
        ->assertJsonPath('data.title', 'مسابقة الفصل')
        ->assertJsonPath('data.names', ['أحمد', 'سارة'])
        ->assertJsonPath('data.results', [])
        ->json('data');

    $savedWheel->update([
        'names' => ['اسم آخر'],
        'names_count' => 1,
    ]);

    expect(Competition::query()->findOrFail($created['id'])->names)
        ->toBe(['أحمد', 'سارة'])
        ->and(DailyMetric::query()->where('date', today()->toDateString())->value('competitions'))
        ->toBe(1);
});

test('competition results are stored as a complete round history', function () {
    $user = User::factory()->create();
    $savedWheel = SavedWheel::factory()->for($user)->create();
    $competition = Competition::factory()->for($user)->create([
        'saved_wheel_id' => $savedWheel->id,
        'version' => 3,
    ]);
    $results = [
        [
            'round' => 1,
            'name' => 'أحمد',
            'date' => now()->subMinute()->toISOString(),
            'position' => 1,
        ],
        [
            'round' => 2,
            'name' => 'سارة',
            'date' => now()->toISOString(),
            'position' => 2,
        ],
    ];

    $this->actingAs($user)
        ->patchJson(route('competitions.update', $competition), [
            'version' => 3,
            'names' => ['أحمد', 'سارة', 'ليان'],
            'results' => $results,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.version', 4)
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.results_count', 2)
        ->assertJsonPath('data.results.1.round', 2);

    expect($competition->fresh()->results)->toBe($results);
});

test('blank rows from imported competition names are ignored', function () {
    $user = User::factory()->create();
    $competition = Competition::factory()->for($user)->create();

    $this->actingAs($user)
        ->patchJson(route('competitions.update', $competition), [
            'version' => $competition->version,
            'names' => ['أحمد', '', '   ', null, ' سارة '],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.names', ['أحمد', 'سارة'])
        ->assertJsonPath('data.names_count', 2);
});

test('a stale competition update returns the latest server state', function () {
    $user = User::factory()->create();
    $competition = Competition::factory()->for($user)->create(['version' => 4]);

    $this->actingAs($user)
        ->patchJson(route('competitions.update', $competition), [
            'version' => 3,
            'names' => ['قديم'],
        ])
        ->assertConflict()
        ->assertJsonPath('conflict', true)
        ->assertJsonPath('data.version', 4);
});

test('a user cannot access another users competition or source list', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $savedWheel = SavedWheel::factory()->for($owner)->create();
    $competition = Competition::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->getJson(route('competitions.show', $competition))
        ->assertForbidden();

    $this->actingAs($intruder)
        ->postJson(route('competitions.store'), competitionPayload($savedWheel))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('saved_wheel_id');
});

test('a newly created source list stays synchronized until the first result', function () {
    $user = User::factory()->create();
    $competition = $this->actingAs($user)
        ->postJson(route('competitions.store'), [
            'title' => 'مسابقة بقائمة جديدة',
            'new_list_title' => 'قائمة المسابقة الجديدة',
        ])
        ->assertCreated()
        ->json('data');
    $savedWheel = SavedWheel::query()->findOrFail($competition['saved_wheel_id']);

    $this->actingAs($user)
        ->patchJson(route('competitions.update', $competition['id']), [
            'version' => 1,
            'names' => ['أحمد', 'سارة'],
        ])
        ->assertSuccessful();

    $savedWheel->refresh();

    expect($savedWheel->names)->toBe(['أحمد', 'سارة'])
        ->and($savedWheel->version)->toBe(2);

    $this->actingAs($user)
        ->patchJson(route('competitions.update', $competition['id']), [
            'version' => 2,
            'names' => ['أحمد', 'سارة'],
            'results' => [[
                'round' => 1,
                'name' => 'أحمد',
                'date' => now()->toISOString(),
                'position' => 1,
            ]],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.sync_source_list', false);

    $this->actingAs($user)
        ->patchJson(route('competitions.update', $competition['id']), [
            'version' => 3,
            'names' => ['أحمد', 'سارة', 'ليان'],
        ])
        ->assertSuccessful();

    expect($savedWheel->fresh()->names)->toBe(['أحمد', 'سارة']);
});

test('competitions use searchable cursor pagination with forty items per page', function () {
    $user = User::factory()->create();

    Competition::factory()->count(45)->for($user)->sequence(
        fn ($sequence) => [
            'title' => $sequence->index === 44 ? 'مسابقة البحث الخاصة' : "مسابقة {$sequence->index}",
            'updated_at' => now()->subSeconds($sequence->index),
        ],
    )->create();

    $firstPage = $this->actingAs($user)
        ->getJson(route('competitions.index'))
        ->assertSuccessful()
        ->assertJsonCount(40, 'data')
        ->assertJsonPath('has_more', true);

    $this->actingAs($user)
        ->getJson(route('competitions.index', ['cursor' => $firstPage->json('next_cursor')]))
        ->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('has_more', false);

    $this->actingAs($user)
        ->getJson(route('competitions.index', ['search' => 'البحث الخاصة']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'مسابقة البحث الخاصة');
});

test('competition payload and account limits are enforced', function () {
    $user = User::factory()->create();
    $savedWheel = SavedWheel::factory()->for($user)->create();
    $competition = Competition::factory()->for($user)->create();

    $this->actingAs($user)
        ->patchJson(route('competitions.update', $competition), [
            'version' => 1,
            'names' => array_fill(0, 2001, 'اسم'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('names');

    $this->actingAs($user)
        ->postJson(route('competitions.store'), [
            'title' => 'مصدران غير مسموحين',
            'saved_wheel_id' => $savedWheel->id,
            'new_list_title' => 'قائمة جديدة',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('saved_wheel_id');

    Competition::factory()->count(99)->for($user)->create();

    $this->actingAs($user)
        ->postJson(route('competitions.store'), competitionPayload($savedWheel, [
            'title' => 'المسابقة الإضافية',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('title');
});

test('competition creation is limited to five per minute', function () {
    $user = User::factory()->create();
    $savedWheel = SavedWheel::factory()->for($user)->create();
    RateLimiter::clear(md5("competition-creationminute:{$user->id}"));
    RateLimiter::clear(md5("competition-creationday:{$user->id}"));

    foreach (range(1, 5) as $number) {
        $this->actingAs($user)
            ->postJson(route('competitions.store'), competitionPayload($savedWheel, [
                'title' => "مسابقة {$number}",
            ]))
            ->assertCreated();
    }

    $this->actingAs($user)
        ->postJson(route('competitions.store'), competitionPayload($savedWheel, [
            'title' => 'المسابقة السادسة',
        ]))
        ->assertTooManyRequests();
});
