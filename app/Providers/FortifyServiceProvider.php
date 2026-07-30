<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\RegisterResponse;
use App\Models\User;
use App\Services\MetricService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(MetricService $metrics): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', [
            'email' => $request->string('email')->toString(),
            'token' => $request->route('token'),
        ]));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));

        Fortify::authenticateUsing(function (Request $request) use ($metrics): ?User {
            $password = $request->string('password')->toString();
            $user = User::query()
                ->where('email', Str::lower($request->string(Fortify::username())->toString()))
                ->first();

            if (! $user || ! $user->isActive() || ! Hash::check($password, $user->password)) {
                return null;
            }

            $attributes = ['last_login_at' => now()];

            if (Hash::needsRehash($user->password)) {
                $attributes['password'] = Hash::make($password);
            }

            $user->forceFill($attributes)->save();
            $metrics->recordActiveUser($user);

            return $user;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('verification', function (Request $request): array {
            $identifier = $request->user()?->id ?? $request->ip();

            return [
                Limit::perMinute(6)->by('minute:'.$identifier),
                Limit::perHour(20)->by('hour:'.$identifier),
            ];
        });
    }
}
