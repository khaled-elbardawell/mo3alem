<?php

use App\Models\AdCampaign;
use App\Models\AdminAuditLog;
use App\Models\DailyMetric;
use App\Models\SavedWheel;
use App\Models\User;
use App\UserRole;
use App\UserStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

test('non administrators cannot open the admin panel', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('an administrator can render every administration screen', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $user = User::factory()->create();
    $wheel = SavedWheel::factory()->create();
    $campaign = AdCampaign::factory()->create();

    $routes = [
        route('admin.dashboard'),
        route('admin.users.index'),
        route('admin.users.create'),
        route('admin.users.edit', $user),
        route('admin.saved-wheels.index'),
        route('admin.saved-wheels.edit', $wheel),
        route('admin.ad-campaigns.index'),
        route('admin.ad-campaigns.create'),
        route('admin.ad-campaigns.edit', $campaign),
        route('admin.analytics'),
        route('admin.seo.edit'),
        route('admin.audit-logs'),
    ];

    foreach ($routes as $route) {
        $this->actingAs($admin)->get($route)->assertSuccessful();
    }
});

test('an administrator may create a verified user account', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'مستخدم جديد',
            'email' => 'NEW.USER@EXAMPLE.COM',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
            'role' => UserRole::User->value,
            'status' => UserStatus::Active->value,
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('status', 'تم إنشاء الحساب بنجاح.');

    $user = User::query()->where('email', 'new.user@example.com')->firstOrFail();

    expect($user->name)->toBe('مستخدم جديد')
        ->and($user->role)->toBe(UserRole::User)
        ->and($user->status)->toBe(UserStatus::Active)
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(Hash::check('SecurePass123', $user->password))->toBeTrue()
        ->and(AdminAuditLog::query()->where('action', 'user.created')->where('subject_id', $user->id)->exists())->toBeTrue()
        ->and((int) DailyMetric::query()->whereDate('date', today())->value('registrations'))->toBe(1);
});

test('user creation validates unique emails password confirmation role and status', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $existingUser = User::factory()->create();

    $this->actingAs($admin)
        ->from(route('admin.users.create'))
        ->post(route('admin.users.store'), [
            'name' => 'حساب غير صالح',
            'email' => $existingUser->email,
            'password' => 'SecurePass123',
            'password_confirmation' => 'different-password',
            'role' => 'owner',
            'status' => 'disabled',
        ])
        ->assertRedirect(route('admin.users.create'))
        ->assertSessionHasErrors(['email', 'password', 'role', 'status']);
});

test('the admin header links back to the main site instead of the wheel', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertSee('id="adminBackToSiteLink"', false)
        ->assertSee('id="adminMobileBackToSiteLink"', false)
        ->assertSee('العودة للموقع')
        ->assertDontSee('العودة للعجلة');
});

test('an administrator may suspend a user and their sessions are ended and audited', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $user = User::factory()->create();

    DB::table(config('session.table', 'sessions'))->insert([
        'id' => 'user-session',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => UserRole::User->value,
            'status' => UserStatus::Suspended->value,
        ])
        ->assertRedirect(route('admin.users.index'));

    expect($user->fresh()->status)->toBe(UserStatus::Suspended)
        ->and(DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->exists())->toBeFalse()
        ->and(AdminAuditLog::query()->where('action', 'user.updated')->exists())->toBeTrue();
});

test('an administrator may soft delete and restore a saved wheel', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $wheel = SavedWheel::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.saved-wheels.destroy', $wheel))
        ->assertRedirect();

    $this->assertSoftDeleted($wheel);

    $this->actingAs($admin)
        ->patch(route('admin.saved-wheels.restore', $wheel->id))
        ->assertRedirect();

    $this->assertNotSoftDeleted($wheel->fresh());
    expect(AdminAuditLog::query()->count())->toBe(2);
});

test('an administrator cannot restore a saved wheel above the user limit', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $user = User::factory()->create();
    $wheel = SavedWheel::factory()->for($user)->create();
    $wheel->delete();
    SavedWheel::factory()->count((int) config('resource_limits.saved_wheels'))->for($user)->create();

    $this->actingAs($admin)
        ->from(route('admin.saved-wheels.index'))
        ->patch(route('admin.saved-wheels.restore', $wheel->id))
        ->assertRedirect(route('admin.saved-wheels.index'))
        ->assertSessionHasErrors('title');

    $this->assertSoftDeleted($wheel);
});

test('an administrator may empty saved names without changing legacy results', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $legacyResults = [['name' => 'فائز قديم', 'date' => now()->toISOString()]];
    $wheel = SavedWheel::factory()->create(['results' => $legacyResults]);

    $this->actingAs($admin)
        ->put(route('admin.saved-wheels.update', $wheel), [
            'title' => $wheel->title,
            'names_text' => '',
        ])
        ->assertRedirect(route('admin.saved-wheels.index'));

    $wheel->refresh();

    expect($wheel->names)->toBe([])
        ->and($wheel->names_count)->toBe(0)
        ->and($wheel->results)->toBe($legacyResults);
});
