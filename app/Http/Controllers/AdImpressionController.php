<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use App\Services\MetricService;
use App\Services\VisitorIdentity;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdImpressionController extends Controller
{
    public function __invoke(
        Request $request,
        AdCampaign $adCampaign,
        MetricService $metrics,
        VisitorIdentity $visitors,
    ): Response {
        abort_unless(AdCampaign::query()->eligible()->whereKey($adCampaign)->exists(), 404);

        $metrics->recordAdImpression($adCampaign->id, $visitors->for($request));

        return response()->noContent();
    }
}
