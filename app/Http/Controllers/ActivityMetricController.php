<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityMetricRequest;
use App\Services\MetricService;
use Illuminate\Http\Response;

class ActivityMetricController extends Controller
{
    public function __invoke(ActivityMetricRequest $request, MetricService $metrics): Response
    {
        $metric = match ($request->validated('event')) {
            'spin' => 'spins',
            'import' => 'imports',
            'qr_generate' => 'qr_generated',
            'certificate_generate' => 'certificate_generated',
        };

        $metrics->increment($metric);

        if ($request->user()) {
            $metrics->recordActiveUser($request->user());
        }

        return response()->noContent();
    }
}
