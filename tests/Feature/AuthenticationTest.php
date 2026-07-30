<?php

use App\Models\User;
use App\UserStatus;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

test('a guest may register and receives an email verification notification', function () {
    Notification::fake();

    $response = $this->post(route('register'), [
        'name' => 'أحمد',
        'email' => 'ahmad@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', 'ahmad@example.com')->firstOrFail();

    $response->assertRedirect(route('verification.notice'));
    $this->assertAuthenticatedAs($user);
    expect($user->hasVerifiedEmail())->toBeFalse();
    Notification::assertSentTo($user, VerifyEmail::class);
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

test('a password reset link can be requested', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertSessionHasNoErrors();

    Notification::assertSentTo($user, ResetPassword::class);
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
    expect(app('router')->getRoutes()->getByName('password.email')?->getActionName())
        ->toContain('Laravel\\Fortify');
});

test('Fortify authentication pages render successfully', function () {
    $this->get(route('login'))->assertOk();
    $this->get(route('register'))->assertOk();
    $this->get(route('password.request'))->assertOk();

    $this->actingAs(User::factory()->create())
        ->get(route('password.confirm'))
        ->assertOk();
});
