<?php

namespace App\Providers;

use App\Models\ApiClient;
use App\View\Composers\FooterComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());
        View::composer('components.public.site-footer', FooterComposer::class);

        RateLimiter::for('social-authentication', function (Request $request): array {
            return [
                Limit::perMinute(10)->by('minute:'.$request->ip()),
                Limit::perHour(50)->by('hour:'.$request->ip()),
            ];
        });

        RateLimiter::for('saved-wheel-creation', function (Request $request): Limit {
            $userIdentifier = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perDay(30)
                ->by("day:{$userIdentifier}")
                ->response(static fn (Request $request, array $headers): JsonResponse => response()->json([
                    'message' => 'وصلت إلى الحد اليومي لإنشاء القوائم وهو 30 قائمة. يمكنك المحاولة لاحقًا.',
                ], 429, $headers));
        });

        RateLimiter::for('competition-creation', function (Request $request): array {
            $userIdentifier = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return [
                Limit::perMinute(5)->by("minute:{$userIdentifier}"),
                Limit::perDay(30)->by("day:{$userIdentifier}"),
            ];
        });

        RateLimiter::for('qr-code-creation', function (Request $request): array {
            $userIdentifier = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return [
                Limit::perMinute(10)->by("minute:{$userIdentifier}"),
                Limit::perDay(100)->by("day:{$userIdentifier}"),
            ];
        });

        RateLimiter::for('qr-redirect', function (Request $request): array {
            return [
                Limit::perMinute(120)->by('minute:'.$request->ip()),
                Limit::perHour(2000)->by('hour:'.$request->ip()),
            ];
        });

        RateLimiter::for('certificate-creation', function (Request $request): array {
            $userIdentifier = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return [
                Limit::perMinute(10)->by("minute:{$userIdentifier}"),
                Limit::perDay(100)->by("day:{$userIdentifier}"),
            ];
        });

        RateLimiter::for('trusted-user-creation', function (Request $request): array {
            $clientIdentifier = $request->user() instanceof ApiClient
                ? $request->user()->getAuthIdentifier()
                : $request->ip();

            return [
                Limit::perMinute(30)->by("minute:api-client:{$clientIdentifier}"),
                Limit::perDay(1000)->by("day:api-client:{$clientIdentifier}"),
            ];
        });
    }
}
