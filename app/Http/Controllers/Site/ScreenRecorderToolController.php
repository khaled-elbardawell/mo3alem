<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\SeoPage;
use App\Services\SeoManager;
use Illuminate\View\View;

class ScreenRecorderToolController extends Controller
{
    public function __invoke(SeoManager $seoManager): View
    {
        $seo = $seoManager->forPage(SeoPage::ScreenRecorder);
        $screenRecorderConfig = [
            'metricsEndpoint' => route('activity-metrics.store'),
            'maxDurationSeconds' => 3600,
            'storageReserveBytes' => 268_435_456,
        ];

        return view('public.tools.screen-recorder', compact('seo', 'screenRecorderConfig'));
    }
}
