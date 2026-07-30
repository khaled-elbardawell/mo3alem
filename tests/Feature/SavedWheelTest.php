<?php

use App\Models\SavedWheel;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

function wheelPayload(array $overrides = []): array
{
    return array_replace([
        'title' => 'الصف الثاني - أ',
        'names' => ['أحمد', 'سارة', 'أحمد'],
        'results' => [['name' => 'سارة', 'date' => now()->toISOString()]],
    ], $overrides);
}

test('a verified user may create and update a saved wheel', function () {
    $user = User::factory()->create();

    $created = $this->actingAs($user)
        ->postJson(route('saved-wheels.store'), wheelPayload())
        ->assertCreated()
        ->json('data');

    expect($created['names_count'])->toBe(3)
        ->and($created['version'])->toBe(1)
        ->and($created)->not->toHaveKey('results');

    $this->actingAs($user)
        ->patchJson(route('saved-wheels.update', $created['id']), wheelPayload([
            'title' => 'الصف الثاني - ب',
            'version' => 1,
        ]))
        ->assertSuccessful()
        ->assertJsonPath('data.version', 2);
});

test('stale saved wheel updates return a conflict response', function () {
    $user = User::factory()->create();
    $wheel = SavedWheel::factory()->for($user)->create(['version' => 3]);

    $this->actingAs($user)
        ->patchJson(route('saved-wheels.update', $wheel), wheelPayload(['version' => 2]))
        ->assertConflict()
        ->assertJsonPath('conflict', true);
});

test('a user cannot read or modify another users wheel', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $wheel = SavedWheel::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->getJson(route('saved-wheels.show', $wheel))
        ->assertForbidden();

    $this->actingAs($intruder)
        ->patchJson(route('saved-wheels.update', $wheel), wheelPayload(['version' => 1]))
        ->assertForbidden();
});

test('an unverified user may read but cannot write saved wheels', function () {
    $user = User::factory()->unverified()->create();
    $wheel = SavedWheel::factory()->for($user)->create();

    $this->actingAs($user)
        ->getJson(route('saved-wheels.show', $wheel))
        ->assertSuccessful();

    $this->actingAs($user)
        ->postJson(route('saved-wheels.store'), wheelPayload())
        ->assertForbidden();
});

test('saved wheel limits and active title uniqueness are enforced', function () {
    $user = User::factory()->create();

    SavedWheel::factory()->count(100)->for($user)->sequence(
        fn ($sequence) => [
            'title' => "قائمة {$sequence->index}",
            'active_title' => "قائمة {$sequence->index}",
        ],
    )->create();

    $this->actingAs($user)
        ->postJson(route('saved-wheels.store'), wheelPayload(['title' => 'قائمة إضافية']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('title');

    $otherUser = User::factory()->create();
    SavedWheel::factory()->for($otherUser)->create([
        'title' => 'مكرر',
        'active_title' => 'مكرر',
    ]);

    $this->actingAs($otherUser)
        ->postJson(route('saved-wheels.store'), wheelPayload(['title' => 'مكرر']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('title');
});

test('a saved wheel accepts an empty list and at most two thousand names', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('saved-wheels.store'), wheelPayload([
            'title' => 'قائمة فارغة',
            'names' => [],
        ]))
        ->assertCreated()
        ->assertJsonPath('data.names_count', 0);

    $this->actingAs($user)
        ->postJson(route('saved-wheels.store'), wheelPayload([
            'title' => 'قائمة كبيرة',
            'names' => array_fill(0, 2001, 'اسم'),
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('names');

    $this->actingAs($user)
        ->postJson(route('saved-wheels.store'), wheelPayload([
            'title' => 'قائمة عند الحد',
            'names' => array_fill(0, 2000, 'اسم'),
        ]))
        ->assertCreated()
        ->assertJsonPath('data.names_count', 2000);
});

test('each saved name is limited to one hundred and twenty characters', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('saved-wheels.store'), wheelPayload([
            'names' => [str_repeat('س', 121)],
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('names.0');
});

test('results are not persisted or returned with a saved list', function () {
    $user = User::factory()->create();

    $created = $this->actingAs($user)
        ->postJson(route('saved-wheels.store'), wheelPayload([
            'title' => 'قائمة مستقلة',
            'results' => [['name' => 'فائز', 'date' => now()->toISOString()]],
        ]))
        ->assertCreated()
        ->assertJsonMissingPath('data.results')
        ->json('data');

    $wheel = SavedWheel::query()->findOrFail($created['id']);

    expect($wheel->results)->toBe([]);

    $this->actingAs($user)
        ->getJson(route('saved-wheels.show', $wheel))
        ->assertSuccessful()
        ->assertJsonMissingPath('data.results');
});

test('saved lists use searchable cursor pagination with forty items per page', function () {
    $user = User::factory()->create();

    SavedWheel::factory()->count(45)->for($user)->sequence(
        fn ($sequence) => [
            'title' => $sequence->index === 44 ? 'قائمة البحث الخاصة' : "قائمة {$sequence->index}",
            'active_title' => $sequence->index === 44 ? 'قائمة البحث الخاصة' : "قائمة {$sequence->index}",
            'updated_at' => now()->subSeconds($sequence->index),
        ],
    )->create();

    $firstPage = $this->actingAs($user)
        ->getJson(route('saved-wheels.index'))
        ->assertSuccessful()
        ->assertJsonCount(40, 'data')
        ->assertJsonPath('has_more', true);

    $cursor = $firstPage->json('next_cursor');

    $secondPage = $this->actingAs($user)
        ->getJson(route('saved-wheels.index', ['cursor' => $cursor]))
        ->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('has_more', false);

    expect(collect($firstPage->json('data'))->pluck('id')
        ->intersect(collect($secondPage->json('data'))->pluck('id')))
        ->toBeEmpty();

    $this->actingAs($user)
        ->getJson(route('saved-wheels.index', ['search' => 'البحث الخاصة']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'قائمة البحث الخاصة');
});

test('saved list creation is limited to five per minute', function () {
    $user = User::factory()->create();
    RateLimiter::clear(md5("saved-wheel-creationminute:{$user->id}"));
    RateLimiter::clear(md5("saved-wheel-creationday:{$user->id}"));

    foreach (range(1, 5) as $number) {
        $this->actingAs($user)
            ->postJson(route('saved-wheels.store'), wheelPayload(['title' => "سريعة {$number}"]))
            ->assertCreated();
    }

    $this->actingAs($user)
        ->postJson(route('saved-wheels.store'), wheelPayload(['title' => 'السادسة']))
        ->assertTooManyRequests();
});

test('saved list creation is limited to thirty per day', function () {
    $user = User::factory()->create();
    RateLimiter::clear(md5("saved-wheel-creationminute:{$user->id}"));
    RateLimiter::clear(md5("saved-wheel-creationday:{$user->id}"));

    foreach (range(1, 30) as $number) {
        RateLimiter::clear(md5("saved-wheel-creationminute:{$user->id}"));

        $this->actingAs($user)
            ->postJson(route('saved-wheels.store'), wheelPayload(['title' => "يومية {$number}"]))
            ->assertCreated();
    }

    RateLimiter::clear(md5("saved-wheel-creationminute:{$user->id}"));

    $this->actingAs($user)
        ->postJson(route('saved-wheels.store'), wheelPayload(['title' => 'الحادية والثلاثون']))
        ->assertTooManyRequests();
});
