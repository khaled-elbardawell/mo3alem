<?php

use App\Models\Competition;
use App\Models\SavedWheel;
use App\Models\User;

test('a user can view complete competition details grouped by round', function () {
    $user = User::factory()->create();
    $savedWheel = SavedWheel::factory()->for($user)->create([
        'title' => 'قائمة المشاركين',
        'names' => ['أحمد', 'سارة', 'محمد'],
        'names_count' => 3,
    ]);
    $competition = Competition::factory()->for($user)->create([
        'saved_wheel_id' => $savedWheel->id,
        'title' => 'مسابقة العلوم',
        'names' => ['محمد'],
        'names_count' => 1,
        'results' => [
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
        ],
        'results_count' => 2,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->get(route('user.competitions.show', $competition))
        ->assertSuccessful()
        ->assertViewIs('users.competitions.show')
        ->assertSee('محمد')
        ->assertSee('قائمة المشاركين')
        ->assertSee(route('tools.wheel', ['competition' => $competition]), false);

    expect(collect($response->viewData('results')->items())->pluck('name')->all())
        ->toBe(['سارة', 'أحمد']);
});

test('competition names and results use separate pagination parameters', function () {
    $user = User::factory()->create();
    $names = collect(range(1, 105))->map(fn (int $number): string => "الاسم {$number}")->all();
    $results = collect(range(1, 55))->map(fn (int $round): array => [
        'round' => $round,
        'name' => "الفائز {$round}",
        'date' => now()->toISOString(),
        'position' => $round,
    ])->all();
    $competition = Competition::factory()->for($user)->create([
        'names' => $names,
        'names_count' => count($names),
        'results' => $results,
        'results_count' => count($results),
    ]);

    $response = $this->actingAs($user)->get(route('user.competitions.show', [
        'competition' => $competition,
        'names_page' => 2,
        'results_page' => 2,
    ]));

    $response->assertSuccessful();

    expect($response->viewData('names'))
        ->count()->toBe(5)
        ->and($response->viewData('names')->currentPage())->toBe(2)
        ->and($response->viewData('results'))
        ->count()->toBe(5)
        ->and($response->viewData('results')->currentPage())->toBe(2);
});

test('a user can view a saved list with all names and without competition results', function () {
    $user = User::factory()->create();
    $savedWheel = SavedWheel::factory()->for($user)->create([
        'title' => 'قائمة الفصل',
        'names' => ['ليان', 'عمر', 'نور'],
        'names_count' => 3,
    ]);

    $this->actingAs($user)
        ->get(route('user.saved-wheels.show', $savedWheel))
        ->assertSuccessful()
        ->assertViewIs('users.saved-wheels.show')
        ->assertSeeInOrder(['ليان', 'عمر', 'نور'])
        ->assertDontSee('نتيجة قديمة لا تخص مسابقة')
        ->assertSee('الفائزون ونتائج اللفات محفوظون داخل كل مسابقة بشكل مستقل')
        ->assertSee(route('tools.wheel', ['wheel' => $savedWheel]), false);
});

test('detail filters search the complete names and results collections', function () {
    $user = User::factory()->create();
    $competition = Competition::factory()->for($user)->create([
        'names' => ['سارة', 'محمد خالد', 'أحمد', 'محمد علي'],
        'names_count' => 4,
        'results' => [
            ['round' => 1, 'name' => 'ليان', 'date' => now()->toISOString(), 'position' => 1],
            ['round' => 2, 'name' => 'نور', 'date' => now()->toISOString(), 'position' => 2],
        ],
        'results_count' => 2,
    ]);

    $response = $this->actingAs($user)->get(route('user.competitions.show', [
        'competition' => $competition,
        'names_search' => 'محمد',
        'names_sort' => 'descending',
        'results_search' => 'نور',
        'results_round' => 2,
    ]));

    $response->assertSuccessful()
        ->assertSee('2 مطابق')
        ->assertSee('1 مطابق')
        ->assertSee('h-[32rem]', false);

    expect(collect($response->viewData('names')->items())->pluck('name')->all())
        ->toBe(['محمد علي', 'محمد خالد'])
        ->and(collect($response->viewData('results')->items())->pluck('name')->all())
        ->toBe(['نور']);
});

test('a user can add and remove a name from competition details', function () {
    $user = User::factory()->create();
    $competition = Competition::factory()->for($user)->create([
        'names' => ['أحمد', 'سارة'],
        'names_count' => 2,
        'version' => 4,
    ]);

    $this->actingAs($user)
        ->post(route('user.competitions.names.store', $competition), [
            'name' => '  محمد   خالد  ',
            'version' => 4,
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'تمت إضافة الاسم إلى المسابقة.');

    expect($competition->fresh()->names)
        ->toBe(['أحمد', 'سارة', 'محمد خالد'])
        ->and($competition->fresh()->version)->toBe(5);

    $this->actingAs($user)
        ->delete(route('user.competitions.names.destroy', [
            'competition' => $competition,
            'nameIndex' => 1,
        ]), ['version' => 5])
        ->assertRedirect()
        ->assertSessionHas('status', 'تم حذف الاسم من المسابقة.');

    expect($competition->fresh()->names)
        ->toBe(['أحمد', 'محمد خالد'])
        ->and($competition->fresh()->names_count)->toBe(2)
        ->and($competition->fresh()->version)->toBe(6);
});

test('a user can add and remove a name from saved list details', function () {
    $user = User::factory()->create();
    $savedWheel = SavedWheel::factory()->for($user)->create([
        'names' => ['ليان'],
        'names_count' => 1,
        'version' => 2,
    ]);

    $this->actingAs($user)
        ->post(route('user.saved-wheels.names.store', $savedWheel), [
            'name' => 'نور',
            'version' => 2,
        ])
        ->assertRedirect();

    expect($savedWheel->fresh()->names)->toBe(['ليان', 'نور']);

    $this->actingAs($user)
        ->delete(route('user.saved-wheels.names.destroy', [
            'savedWheel' => $savedWheel,
            'nameIndex' => 0,
        ]), ['version' => 3])
        ->assertRedirect();

    expect($savedWheel->fresh()->names)
        ->toBe(['نور'])
        ->and($savedWheel->fresh()->names_count)->toBe(1)
        ->and($savedWheel->fresh()->version)->toBe(4);
});

test('name management detects stale versions and enforces ownership', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $competition = Competition::factory()->for($owner)->create([
        'names' => ['أحمد'],
        'names_count' => 1,
        'version' => 3,
    ]);

    $this->actingAs($owner)
        ->post(route('user.competitions.names.store', $competition), [
            'name' => 'سارة',
            'version' => 2,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('name');

    expect($competition->fresh()->names)->toBe(['أحمد']);

    $this->actingAs($intruder)
        ->post(route('user.competitions.names.store', $competition), [
            'name' => 'اسم غير مسموح',
            'version' => 3,
        ])
        ->assertForbidden();

    $this->actingAs($intruder)
        ->delete(route('user.competitions.names.destroy', [
            'competition' => $competition,
            'nameIndex' => 0,
        ]), ['version' => 3])
        ->assertForbidden();
});

test('a user cannot view another users competition or saved list details', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $competition = Competition::factory()->for($owner)->create();
    $savedWheel = SavedWheel::factory()->for($owner)->create([
        'title' => 'قائمة خاصة بالمالك',
    ]);
    $competitionWithForeignSource = Competition::factory()->for($intruder)->create([
        'saved_wheel_id' => $savedWheel->id,
    ]);

    $this->actingAs($intruder)
        ->get(route('user.competitions.show', $competition))
        ->assertForbidden();

    $this->actingAs($intruder)
        ->get(route('user.saved-wheels.show', $savedWheel))
        ->assertForbidden();

    $this->actingAs($intruder)
        ->get(route('user.competitions.show', $competitionWithForeignSource))
        ->assertSuccessful()
        ->assertDontSee('قائمة خاصة بالمالك');
});
