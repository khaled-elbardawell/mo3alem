<?php

namespace App\Http\Controllers\Site;

use App\AdPlacement;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompetitionResource;
use App\Http\Resources\SavedWheelResource;
use App\Models\Competition;
use App\Models\DailyMetric;
use App\Models\SavedWheel;
use App\Models\SeoSetting;
use App\Models\User;
use App\Services\AdCampaignSelector;
use App\Services\MetricService;
use App\Services\VisitorIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

use function Illuminate\Support\defer;

class WheelController extends Controller
{
    public function __invoke(
        Request $request,
        AdCampaignSelector $ads,
        MetricService $metrics,
        VisitorIdentity $visitors,
    ): View {
        $visitorIdentifier = $visitors->for($request);
        defer(function () use ($metrics, $visitorIdentifier): void {
            $metrics->recordSiteVisit($visitorIdentifier);
        });

        $loadedCompetition = null;
        $loadedWheel = null;

        if ($request->filled('competition') && $request->user()) {
            $loadedCompetition = Competition::query()->findOrFail($request->integer('competition'));
            Gate::authorize('view', $loadedCompetition);
            $loadedCompetition->forceFill(['last_opened_at' => now()])->save();
        } elseif ($request->filled('wheel') && $request->user()) {
            $loadedWheel = SavedWheel::query()->findOrFail($request->integer('wheel'));
            Gate::authorize('view', $loadedWheel);
            $loadedWheel->forceFill(['last_opened_at' => now()])->save();
        }

        $seoValues = Cache::remember('seo:home', 300, function (): array {
            $seo = SeoSetting::query()->first() ?? new SeoSetting;

            return $seo->getAttributes();
        });
        $seo = (new SeoSetting)->forceFill($seoValues);
        $publicStats = Cache::remember('public:stats', 300, fn (): array => [
            'users' => User::query()->count(),
            'wheels' => SavedWheel::query()->count(),
            'names' => (int) SavedWheel::query()->sum('names_count'),
            'spins' => (int) DailyMetric::query()->sum('spins'),
        ]);

        $campaigns = [
            'top' => $ads->select(AdPlacement::Top),
            'side' => $ads->select(AdPlacement::Side),
            'bottom' => $ads->select(AdPlacement::Bottom),
        ];

        $wheelConfig = [
            'authenticated' => (bool) $request->user(),
            'verified' => $request->user()?->hasVerifiedEmail() ?? false,
            'limits' => [
                'savedWheels' => (int) config('resource_limits.saved_wheels', 5),
                'namesPerSavedWheel' => (int) config('resource_limits.names_per_saved_wheel', 2000),
            ],
            'usage' => [
                'savedWheels' => $request->user()?->savedWheels()->count() ?? 0,
            ],
            'competition' => $loadedCompetition
                ? CompetitionResource::make($loadedCompetition)->resolve($request)
                : null,
            'savedWheel' => $loadedWheel
                ? SavedWheelResource::make($loadedWheel)->resolve($request)
                : null,
            'copyMode' => $request->boolean('copy'),
            'routes' => [
                'login' => route('login'),
                'register' => route('register'),
                'dashboard' => $request->user() ? route('dashboard') : null,
                'index' => $request->user() ? route('saved-wheels.index') : null,
                'store' => $request->user() ? route('saved-wheels.store') : null,
                'showBase' => $request->user() ? url('/saved-wheels') : null,
                'updateBase' => $request->user() ? url('/saved-wheels') : null,
                'competitions' => [
                    'index' => $request->user() ? route('competitions.index') : null,
                    'store' => $request->user() ? route('competitions.store') : null,
                    'showBase' => $request->user() ? url('/competitions') : null,
                    'updateBase' => $request->user() ? url('/competitions') : null,
                ],
                'metrics' => route('activity-metrics.store'),
            ],
            'csrfToken' => csrf_token(),
        ];

        return view('public.tools.wheel', compact('seo', 'publicStats', 'campaigns', 'wheelConfig'));
    }
}
