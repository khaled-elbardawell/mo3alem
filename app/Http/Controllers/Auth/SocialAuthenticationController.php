<?php

namespace App\Http\Controllers\Auth;

use App\Actions\AuthenticateSocialUser;
use App\Exceptions\SocialAuthenticationException;
use App\Http\Controllers\Controller;
use App\Services\MetricService;
use App\SocialProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Socialite;
use Throwable;

class SocialAuthenticationController extends Controller
{
    public function __construct(
        private readonly AuthenticateSocialUser $authenticateSocialUser,
        private readonly MetricService $metrics,
    ) {}

    public function redirect(SocialProvider $provider): RedirectResponse
    {
        if (! $this->isConfigured($provider)) {
            return to_route('login')->withErrors([
                'social' => 'تسجيل الدخول عبر هذا المزوّد غير مهيأ حاليًا.',
            ]);
        }

        return Socialite::driver($provider->value)->redirect();
    }

    public function callback(Request $request, SocialProvider $provider): RedirectResponse
    {
        if (! $this->isConfigured($provider)) {
            return to_route('login')->withErrors([
                'social' => 'تسجيل الدخول عبر هذا المزوّد غير مهيأ حاليًا.',
            ]);
        }

        try {
            $socialiteUser = Socialite::driver($provider->value)->user();
            $user = $this->authenticateSocialUser->handle($provider, $socialiteUser);
        } catch (SocialAuthenticationException $exception) {
            return to_route('login')->withErrors(['social' => $exception->getMessage()]);
        } catch (Throwable $throwable) {
            report($throwable);

            return to_route('login')->withErrors([
                'social' => 'لم يكتمل تسجيل الدخول. حاول مرة أخرى وتأكد من السماح بمشاركة البريد الإلكتروني.',
            ]);
        }

        $isNewRegistration = $user->wasRecentlyCreated;

        $user->forceFill(['last_login_at' => now()])->save();

        Auth::login($user);
        $request->session()->regenerate();

        if ($isNewRegistration) {
            $this->metrics->increment('registrations');
        }

        $this->metrics->recordActiveUser($user);

        return redirect()->intended(route('home'));
    }

    private function isConfigured(SocialProvider $provider): bool
    {
        $configuration = config("services.{$provider->value}");

        return filled($configuration['client_id'] ?? null)
            && filled($configuration['client_secret'] ?? null)
            && filled($configuration['redirect'] ?? null);
    }
}
