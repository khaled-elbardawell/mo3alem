<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\Certificate;
use App\Models\Competition;
use App\Models\DailyMetric;
use App\Models\QrCode;
use App\Models\SavedWheel;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = CarbonImmutable::today();
        $todayMetric = DailyMetric::query()->whereDate('date', $today)->first();
        $metrics = [
            'users' => User::query()->count(),
            'competitions' => Competition::query()->count(),
            'saved_wheels' => SavedWheel::query()->count(),
            'qr_codes' => QrCode::query()->count(),
            'certificates' => Certificate::query()->count(),
            'active_campaigns' => AdCampaign::query()->eligible()->count(),
            ...collect(DailyMetric::COLUMNS)->mapWithKeys(
                fn (string $column): array => ["{$column}_today" => (int) ($todayMetric?->{$column} ?? 0)],
            )->all(),
        ];

        return view('admin.dashboard', compact('metrics', 'today'));
    }
}
