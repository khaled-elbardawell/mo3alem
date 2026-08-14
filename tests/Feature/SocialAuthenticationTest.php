<?php

use App\Models\User;
use App\SocialProvider;
use App\UserStatus;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function () {
    foreach (SocialProvider::cases() as $provider) {
        config()->set("services.{$provider->value}", [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'redirect' => route('social.callback', ['provider' => $provider->value]),
        ]);
    }
});

test('authentication pages offer google and facebook login', function () {
    foreach (['login', 'register'] as $page) {
        $this->get(route($page))
            ->assertSuccessful()
            ->assertSee(route('social.redirect', ['provider' => 'google']), false)
            ->assertSee(route('social.redirect', ['provider' => 'facebook']), false);
    }
});

test('a guest can be redirected to a supported social provider', function (SocialProvider $provider) {
    Socialite::fake($provider->value);

    $this->get(route('social.redirect', ['provider' => $provider->value]))
        ->assertRedirect("https://socialite.fake/{$provider->value}/authorize");
})->with(SocialProvider::cases());

test('the oauth redirect uses session state protection', function () {
    $response = $this->get(route('social.redirect', ['provider' => 'google']));
    $location = $response->headers->get('Location');
    parse_str((string) parse_url((string) $location, PHP_URL_QUERY), $query);

    $response->assertRedirectContains('accounts.google.com/o/oauth2/auth');
    expect(session('state'))->not->toBeEmpty()
        ->and($query['state'] ?? null)->toBe(session('state'));
});

test('a callback without valid session state is rejected safely', function () {
    $this->get(route('social.callback', ['provider' => 'google']))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('social');

    $this->assertGuest();
});

test('a guest can register and login with a social provider', function (SocialProvider $provider) {
    Socialite::fake($provider->value, SocialiteUser::fake([
        'id' => "{$provider->value}-123",
        'name' => 'أحمد محمد',
        'email' => "{$provider->value}@example.com",
    ]));

    $response = $this->get(route('social.callback', ['provider' => $provider->value]));

    $user = User::query()->where('email', "{$provider->value}@example.com")->firstOrFail();

    $response->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user);
    expect($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->{$provider->userIdColumn()})->toBe("{$provider->value}-123")
        ->and($user->last_login_at)->not->toBeNull();
})->with(SocialProvider::cases());

test('an existing social identity can login without changing its local email', function (SocialProvider $provider) {
    $user = User::factory()->create([
        $provider->userIdColumn() => "{$provider->value}-123",
    ]);
    Socialite::fake($provider->value, SocialiteUser::fake([
        'id' => "{$provider->value}-123",
        'name' => 'Changed Name',
        'email' => 'changed@example.com',
    ]));

    $this->get(route('social.callback', ['provider' => $provider->value]))
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->email)->toBe($user->email);
})->with(SocialProvider::cases());

test('an existing active account is linked and logged in by matching social email', function (SocialProvider $provider) {
    $user = User::factory()->unverified()->create(['email' => 'registered@example.com']);
    Socialite::fake($provider->value, SocialiteUser::fake([
        'id' => "{$provider->value}-456",
        'name' => 'أحمد محمد',
        'email' => $user->email,
    ]));

    $this->get(route('social.callback', ['provider' => $provider->value]))
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
    $linkedUser = $user->fresh();

    expect($linkedUser->{$provider->userIdColumn()})->toBe("{$provider->value}-456")
        ->and($linkedUser->hasVerifiedEmail())->toBeTrue()
        ->and(User::query()->where('email', $user->email)->count())->toBe(1);
})->with(SocialProvider::cases());

test('an existing provider link cannot be replaced by a different social identity', function (SocialProvider $provider) {
    $user = User::factory()->create([
        'email' => 'linked@example.com',
        $provider->userIdColumn() => "{$provider->value}-original",
    ]);
    Socialite::fake($provider->value, SocialiteUser::fake([
        'id' => "{$provider->value}-different",
        'name' => 'أحمد محمد',
        'email' => $user->email,
    ]));

    $this->get(route('social.callback', ['provider' => $provider->value]))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('social');

    $this->assertGuest();
    expect($user->fresh()->{$provider->userIdColumn()})->toBe("{$provider->value}-original");
})->with(SocialProvider::cases());

test('a suspended account cannot be linked by matching social email', function (SocialProvider $provider) {
    User::factory()->create([
        'email' => 'suspended@example.com',
        'status' => UserStatus::Suspended,
    ]);
    Socialite::fake($provider->value, SocialiteUser::fake([
        'id' => "{$provider->value}-789",
        'name' => 'أحمد محمد',
        'email' => 'suspended@example.com',
    ]));

    $this->get(route('social.callback', ['provider' => $provider->value]))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('social');

    $this->assertGuest();
})->with(SocialProvider::cases());

test('a deleted account cannot be linked by matching social email', function (SocialProvider $provider) {
    $user = User::factory()->create(['email' => 'deleted@example.com']);
    $user->delete();
    Socialite::fake($provider->value, SocialiteUser::fake([
        'id' => "{$provider->value}-deleted",
        'name' => 'أحمد محمد',
        'email' => $user->email,
    ]));

    $this->get(route('social.callback', ['provider' => $provider->value]))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('social');

    $this->assertGuest();
    expect($user->fresh()->{$provider->userIdColumn()})->toBeNull();
})->with(SocialProvider::cases());

test('unsupported social providers are rejected', function () {
    $this->get('/auth/linkedin/redirect')->assertNotFound();
});

test('unconfigured social providers fail safely', function () {
    config()->set('services.google.client_secret');

    $this->get(route('social.redirect', ['provider' => 'google']))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('social');
});
