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

class TypingTrainerController extends Controller
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

        $seo = $seoManager->forPage(SeoPage::TypingTrainer);
        $campaigns = [
            'top' => $ads->select(AdPlacement::Top),
            'side' => $ads->select(AdPlacement::Side),
            'bottom' => $ads->select(AdPlacement::Bottom),
        ];
        $typingTrainerConfig = [
            'randomPassageUrl' => route('tools.typing-trainer.passages.random'),
        ];

        return view('public.tools.typing-trainer', compact('seo', 'campaigns', 'typingTrainerConfig'));
    }
}
