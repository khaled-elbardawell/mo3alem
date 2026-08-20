<?php

use App\Models\Competition;
use App\Models\CompetitionParticipant;
use App\Models\CompetitionResult;
use App\Models\SavedWheel;
use App\Models\SavedWheelName;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

test('wheel names and competition results use normalized tables', function () {
    expect(Schema::hasTable('saved_wheel_names'))->toBeTrue()
        ->and(Schema::hasTable('saved_wheel_results'))->toBeFalse()
        ->and(Schema::hasTable('competition_participants'))->toBeTrue()
        ->and(Schema::hasTable('competition_results'))->toBeTrue()
        ->and(Schema::hasColumn('saved_wheels', 'names'))->toBeFalse()
        ->and(Schema::hasColumn('saved_wheels', 'results'))->toBeFalse()
        ->and(Schema::hasColumn('competitions', 'names'))->toBeFalse()
        ->and(Schema::hasColumn('competitions', 'results'))->toBeFalse();
});

test('saved lists preserve duplicate names and their order in separate rows', function () {
    $savedWheel = SavedWheel::factory()->create([
        'names' => ['أحمد', 'سارة', 'أحمد'],
        'names_count' => 3,
    ]);

    expect($savedWheel->fresh()->names)->toBe(['أحمد', 'سارة', 'أحمد'])
        ->and(SavedWheelName::query()
            ->whereBelongsTo($savedWheel)
            ->orderBy('position')
            ->pluck('position', 'name')
            ->all())
        ->toBe([
            'أحمد' => 2,
            'سارة' => 1,
        ]);
});

test('a removed duplicate winner remains linked to its result snapshot', function () {
    $user = User::factory()->create();
    $competition = Competition::factory()->for($user)->create([
        'names' => ['أحمد', 'أحمد', 'سارة'],
        'names_count' => 3,
        'results' => [],
        'results_count' => 0,
        'version' => 2,
    ]);
    $wonAt = now()->toISOString();

    $this->actingAs($user)
        ->patchJson(route('competitions.update', $competition), [
            'version' => 2,
            'names' => ['أحمد', 'سارة'],
            'results' => [[
                'round' => 1,
                'name' => 'أحمد',
                'date' => $wonAt,
                'position' => 2,
            ]],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.names', ['أحمد', 'سارة'])
        ->assertJsonPath('data.results.0.name', 'أحمد');

    $result = CompetitionResult::query()->whereBelongsTo($competition)->firstOrFail();
    $participant = CompetitionParticipant::query()->findOrFail($result->competition_participant_id);

    expect($result->name_snapshot)->toBe('أحمد')
        ->and($result->position)->toBe(2)
        ->and($participant->name)->toBe('أحمد')
        ->and($participant->is_active)->toBeFalse()
        ->and($competition->fresh()->names)->toBe(['أحمد', 'سارة']);
});
