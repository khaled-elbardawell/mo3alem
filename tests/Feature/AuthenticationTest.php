<?php

use App\Models\User;
use App\UserStatus;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('a guest may register without email verification', function () {
    Notification::fake();

    $response = $this->post(route('register'), [
        'name' => 'أحمد',
        'email' => 'ahmad@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', 'ahmad@example.com')->firstOrFail();

    $response->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user);
    expect($user->hasVerifiedEmail())->toBeTrue();
    Notification::assertNotSentTo($user, VerifyEmail::class);
});

test('an active user may login and a suspended user may not', function () {
    $activeUser = User::factory()->create();
    $suspendedUser = User::factory()->create(['status' => UserStatus::Suspended]);

    $this->post(route('login'), [
        'email' => $activeUser->email,
        'password' => 'password',
    ])->assertRedirect(route('home'));

    $this->post(route('logout'))->assertRedirect(route('home'));

    $this->post(route('login'), [
        'email' => $suspendedUser->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('a user with a temporary password must change it before using protected features', function () {
    $user = User::factory()->create();
    $user->forceFill(['must_change_password' => true])->save();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('profile.edit'));

    $this->get(route('dashboard'))
        ->assertRedirect(route('profile.edit'));

    $this->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('يجب تغيير كلمة المرور المؤقتة');

    $this->from(route('profile.edit'))
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'NewSecurePassword123',
            'password_confirmation' => 'NewSecurePassword123',
        ])
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh()->must_change_password)->toBeFalse()
        ->and(Hash::check('NewSecurePassword123', $user->fresh()->password))->toBeTrue();
});

test('password reset routes are disabled because email delivery is unavailable', function () {
    expect(app('router')->getRoutes()->getByName('password.request'))->toBeNull()
        ->and(app('router')->getRoutes()->getByName('password.email'))->toBeNull()
        ->and(app('router')->getRoutes()->getByName('password.reset'))->toBeNull()
        ->and(app('router')->getRoutes()->getByName('password.update'))->toBeNull();
});

test('changing the profile email requires verification again', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('user-profile-information.update'), [
            'name' => $user->name,
            'email' => 'changed@example.com',
        ])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->email_verified_at)->toBeNull();
    Notification::assertSentTo($user, VerifyEmail::class);
});

test('authentication routes are handled by Fortify', function () {
    expect(app('router')->getRoutes()->getByName('login')?->getActionName())
        ->toContain('Laravel\\Fortify');
    expect(app('router')->getRoutes()->getByName('register.store')?->getActionName())
        ->toContain('Laravel\\Fortify');
});

test('Fortify authentication pages render successfully', function () {
    $this->get(route('login'))->assertOk();
    $this->get(route('register'))->assertOk();

    $this->get(route('login'))
        ->assertDontSee('نسيت كلمة السر؟');

    $this->actingAs(User::factory()->create())
        ->get(route('password.confirm'))
        ->assertOk();
});
