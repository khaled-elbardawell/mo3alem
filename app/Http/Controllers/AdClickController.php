<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use App\Services\MetricService;
use App\Services\VisitorIdentity;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdClickController extends Controller
{
    public function __invoke(
        Request $request,
        AdCampaign $adCampaign,
        MetricService $metrics,
        VisitorIdentity $visitors,
    ): Response {
        abort_unless(AdCampaign::query()->eligible()->whereKey($adCampaign)->exists(), 404);

        $visitorIdentifier = $visitors->for($request);
        $metrics->recordAdImpression($adCampaign->id, $visitorIdentifier);
        $metrics->recordAdClick($adCampaign->id, $visitorIdentifier);

        return response()->noContent();
    }
}
