<?php

namespace App\Http\Controllers\Site;

use App\AdPlacement;
use App\Http\Controllers\Controller;
use App\SeoPage;
use App\Services\AdCampaignSelector;
use App\Services\MetricService;
use App\Services\SeoManager;
use App\Services\VisitorIdentity;
use Illuminate\Http\Request;
use Illuminate\View\View;

use function Illuminate\Support\defer;

class ShortUrlToolController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        Request $request,
        AdCampaignSelector $ads,
        MetricService $metrics,
        SeoManager $seoManager,
        VisitorIdentity $visitors,
    ): View {
        $visitorIdentifier = $visitors->for($request);
        defer(fn () => $metrics->recordSiteVisit($visitorIdentifier));

        $shortLink = null;

        if ($request->filled('short_link') && $request->user()) {
            $shortLink = $request->user()->shortLinks()->findOrFail($request->integer('short_link'));
        }

        $seo = $seoManager->forPage(SeoPage::ShortUrl);
        $campaigns = [
            'top' => $ads->select(AdPlacement::Top),
            'side' => $ads->select(AdPlacement::Side),
            'bottom' => $ads->select(AdPlacement::Bottom),
        ];

        return view('public.tools.short-url', compact('seo', 'campaigns', 'shortLink'));
    }
}
