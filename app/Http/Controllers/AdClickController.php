<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use App\Services\MetricService;
use App\Services\VisitorIdentity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdClickController extends Controller
{
    public function __invoke(
        Request $request,
        AdCampaign $adCampaign,
        MetricService $metrics,
        VisitorIdentity $visitors,
    ): RedirectResponse {
        abort_unless(AdCampaign::query()->eligible()->whereKey($adCampaign)->exists(), 404);

        $scheme = parse_url($adCampaign->target_url, PHP_URL_SCHEME);
        abort_unless(in_array($scheme, ['http', 'https'], true), 400);

        $visitorIdentifier = $visitors->for($request);
        $metrics->recordAdImpression($adCampaign->id, $visitorIdentifier);
        $metrics->recordAdClick($adCampaign->id, $visitorIdentifier);

        return redirect()->away($adCampaign->target_url);
    }
}
