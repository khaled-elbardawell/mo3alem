<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityMetricRequest;
use App\Services\MetricService;
use Illuminate\Http\Response;

class ActivityMetricController extends Controller
{
    public function __invoke(ActivityMetricRequest $request, MetricService $metrics): Response
    {
        $metrics->increment($request->validated('event') === 'spin' ? 'spins' : 'imports');

        if ($request->user()) {
            $metrics->recordActiveUser($request->user());
        }

        return response()->noContent();
    }
}
