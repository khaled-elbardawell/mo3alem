<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\DailyMetric;
use App\Models\SavedWheel;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $metrics = Cache::remember('admin:overview:v2', 120, function (): array {
            $today = DailyMetric::query()->where('date', today()->toDateString())->first();

            return [
                'users' => User::withTrashed()->count(),
                'active_users' => User::query()->count(),
                'saved_wheels' => SavedWheel::withTrashed()->count(),
                'active_campaigns' => AdCampaign::query()->eligible()->count(),
                'site_visits_today' => (int) $today?->site_visits,
                'spins_today' => (int) $today?->spins,
                'registrations_today' => (int) $today?->registrations,
                'ad_impressions_today' => (int) $today?->ad_impressions,
                'ad_clicks_today' => (int) $today?->ad_clicks,
            ];
        });

        return view('admin.dashboard', compact('metrics'));
    }
}
