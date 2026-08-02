<?php

use App\Models\Competition;
use App\Models\SavedWheel;
use App\Models\User;

test('the dashboard starts with competitions and keeps saved lists in a separate tab', function () {
    $user = User::factory()->create();
    Competition::factory()->for($user)->create(['title' => 'مسابقة العلوم']);
    SavedWheel::factory()->for($user)->create([
        'title' => 'قائمة الصف الخامس',
        'active_title' => 'قائمة الصف الخامس',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertViewIs('users.dashboard')
        ->assertSee('aria-label="التنقل الرئيسي"', false)
        ->assertSee('صُممت لتجعل يوم المعلم أسهل.')
        ->assertSee('مسابقاتي وقوائمي')
        ->assertSee('مسابقة العلوم')
        ->assertSee(route('user.competitions.show', $user->competitions()->first()), false)
        ->assertDontSee('قائمة الصف الخامس');

    $this->actingAs($user)
        ->get(route('dashboard', ['section' => 'lists']))
        ->assertSuccessful()
        ->assertSee('قائمة الصف الخامس')
        ->assertSee(route('user.saved-wheels.show', $user->savedWheels()->first()), false)
        ->assertDontSee('مسابقة العلوم');
});

test('dashboard competition search and deletion stay scoped to the current user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $matchingCompetition = Competition::factory()->for($user)->create([
        'title' => 'المسابقة الخاصة',
    ]);
    Competition::factory()->for($user)->create(['title' => 'مسابقة أخرى']);
    $otherCompetition = Competition::factory()->for($otherUser)->create([
        'title' => 'المسابقة الخاصة بالمستخدم الآخر',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard', ['search' => 'الخاصة']))
        ->assertSuccessful()
        ->assertSee('المسابقة الخاصة')
        ->assertDontSee('مسابقة أخرى')
        ->assertDontSee('المسابقة الخاصة بالمستخدم الآخر');

    $this->actingAs($user)
        ->delete(route('competitions.destroy', $matchingCompetition))
        ->assertRedirect();

    $this->assertSoftDeleted($matchingCompetition);

    $this->actingAs($user)
        ->delete(route('competitions.destroy', $otherCompetition))
        ->assertForbidden();
});
