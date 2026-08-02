<?php

namespace App\Http\Controllers\Site;

use App\AdPlacement;
use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\DailyMetric;
use App\Models\SeoSetting;
use App\Models\User;
use App\Services\AdCampaignSelector;
use App\Services\MetricService;
use App\Services\VisitorIdentity;
use App\UserStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

use function Illuminate\Support\defer;

class HomeController extends Controller
{
    public function __invoke(
        Request $request,
        AdCampaignSelector $ads,
        MetricService $metrics,
        VisitorIdentity $visitors,
    ): View|RedirectResponse {
        if ($request->filled('competition') || $request->filled('wheel')) {
            return redirect()->route('tools.wheel', $request->only(['competition', 'wheel', 'copy']));
        }

        $visitorIdentifier = $visitors->for($request);
        defer(fn () => $metrics->recordSiteVisit($visitorIdentifier));

        $seoValues = Cache::remember('seo:home', 300, function (): array {
            $seo = SeoSetting::query()->first() ?? new SeoSetting;

            return $seo->getAttributes();
        });

        $seo = (new SeoSetting)->forceFill($seoValues);
        $platformActivity = Cache::remember('public:platform-activity', 300, fn (): array => [
            'qrCodes' => (int) DailyMetric::query()->sum('qr_generated'),
            'certificates' => 0,
            'competitions' => Competition::query()->count(),
            'activeUsers' => User::query()->where('status', UserStatus::Active)->count(),
        ]);
        $campaigns = [
            'top' => $ads->select(AdPlacement::Top),
            'side' => $ads->select(AdPlacement::Side),
            'bottom' => $ads->select(AdPlacement::Bottom),
        ];

        return view('public.home', compact('seo', 'platformActivity', 'campaigns'));
    }
}
