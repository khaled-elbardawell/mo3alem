<?php

namespace App\Http\Controllers\Site;

use App\AdPlacement;
use App\Http\Controllers\Controller;
use App\Models\DailyMetric;
use App\Models\SeoSetting;
use App\Services\AdCampaignSelector;
use App\Services\MetricService;
use App\Services\VisitorIdentity;
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
        $platformActivity = Cache::remember(MetricService::PLATFORM_ACTIVITY_CACHE_KEY, 300, function (): array {
            $totals = DailyMetric::query()
                ->toBase()
                ->selectRaw('COALESCE(SUM(spins), 0) as spins')
                ->selectRaw('COALESCE(SUM(names_saved), 0) as names_saved')
                ->selectRaw('COALESCE(SUM(qr_generated + qr_saved), 0) as qr_operations')
                ->selectRaw('COALESCE(SUM(certificate_generated + certificate_saved), 0) as certificate_operations')
                ->first();

            return [
                'spins' => (int) $totals->spins,
                'names' => (int) $totals->names_saved,
                'qrOperations' => (int) $totals->qr_operations,
                'certificateOperations' => (int) $totals->certificate_operations,
            ];
        });
        $campaigns = [
            'top' => $ads->select(AdPlacement::Top),
            'side' => $ads->select(AdPlacement::Side),
            'bottom' => $ads->select(AdPlacement::Bottom),
        ];

        return view('public.home', compact('seo', 'platformActivity', 'campaigns'));
    }
}
