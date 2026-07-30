<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnalyticsRequest;
use App\Models\DailyMetric;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __invoke(AnalyticsRequest $request): View
    {
        [$from, $to, $range] = $this->dates($request);
        $cacheKey = "admin:analytics:v2:{$from->toDateString()}:{$to->toDateString()}";

        $rows = Cache::remember($cacheKey, 300, fn (): array => DailyMetric::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->oldest('date')
            ->get()
            ->toArray());

        $totals = collect($rows)->reduce(function (array $totals, array $row): array {
            foreach (array_keys($totals) as $column) {
                $totals[$column] += (int) $row[$column];
            }

            return $totals;
        }, [
            'site_visits' => 0,
            'registrations' => 0,
            'active_users' => 0,
            'saved_wheels' => 0,
            'names_saved' => 0,
            'spins' => 0,
            'imports' => 0,
            'ad_impressions' => 0,
            'ad_clicks' => 0,
        ]);

        return view('admin.analytics', compact('rows', 'totals', 'from', 'to', 'range'));
    }

    /**
     * @return array{CarbonImmutable, CarbonImmutable, string}
     */
    private function dates(AnalyticsRequest $request): array
    {
        $range = $request->validated('range', '30');
        $to = CarbonImmutable::today();

        $from = match ($range) {
            '7' => $to->subDays(6),
            'year' => $to->startOfYear(),
            'custom' => CarbonImmutable::parse($request->validated('from')),
            default => $to->subDays(29),
        };

        if ($range === 'custom') {
            $to = CarbonImmutable::parse($request->validated('to'));
        }

        abort_if($from->greaterThan($to) || $from->diffInDays($to) > 730, 422, 'نطاق التاريخ غير صالح.');

        return [$from, $to, $range];
    }
}
