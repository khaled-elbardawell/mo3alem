<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnalyticsRequest;
use App\Models\DailyMetric;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __invoke(AnalyticsRequest $request): View
    {
        [$from, $to, $range] = $this->dates($request);
        $storedMetrics = DailyMetric::query()
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->oldest('date')
            ->get()
            ->keyBy(fn (DailyMetric $metric): string => $metric->date->toDateString());

        $rows = $this->dailyRows($from, $to, $storedMetrics);

        $totals = $rows->reduce(function (array $totals, array $row): array {
            foreach (DailyMetric::COLUMNS as $column) {
                $totals[$column] += (int) $row[$column];
            }

            return $totals;
        }, array_fill_keys(DailyMetric::COLUMNS, 0));

        return view('admin.analytics', compact('rows', 'totals', 'from', 'to', 'range'));
    }

    /**
     * @param  Collection<string, DailyMetric>  $storedMetrics
     * @return Collection<int, array<string, CarbonImmutable|int>>
     */
    private function dailyRows(CarbonImmutable $from, CarbonImmutable $to, Collection $storedMetrics): Collection
    {
        return collect(CarbonPeriod::create($from, $to))->map(function ($date) use ($storedMetrics): array {
            $immutableDate = CarbonImmutable::instance($date);
            $metric = $storedMetrics->get($immutableDate->toDateString());
            $row = ['date' => $immutableDate];

            foreach (DailyMetric::COLUMNS as $column) {
                $row[$column] = (int) ($metric?->{$column} ?? 0);
            }

            return $row;
        })->reverse()->values();
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
