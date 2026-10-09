<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ShortLink;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ShortLinkController extends Controller
{
    public function show(Request $request, ShortLink $shortLink): View
    {
        Gate::authorize('view', $shortLink);

        $period = in_array($request->string('period')->toString(), ['7', '30', '90', 'all'], true)
            ? $request->string('period')->toString()
            : '30';
        $dailyStats = $shortLink->dailyStats()
            ->when($period !== 'all', fn ($query) => $query->whereDate('date', '>=', today()->subDays(((int) $period) - 1)))
            ->oldest('date')
            ->get(['date', 'visits', 'unique_visits']);
        $chartData = $this->chartData($dailyStats, $period);

        return view('users.short-links.show', [
            'shortLink' => $shortLink,
            'period' => $period,
            'chartData' => $chartData,
            'periodVisits' => $chartData->sum('visits'),
            'periodUniqueVisits' => $chartData->sum('unique_visits'),
            'maximumDailyVisits' => max(1, (int) $chartData->max('visits')),
        ]);
    }

    /**
     * @param  Collection<int, mixed>  $dailyStats
     * @return Collection<int, array{date: string, label: string, visits: int, unique_visits: int}>
     */
    private function chartData(Collection $dailyStats, string $period): Collection
    {
        if ($period === 'all') {
            return $dailyStats->map(fn ($stat): array => [
                'date' => $stat->date->toDateString(),
                'label' => $stat->date->translatedFormat('j M Y'),
                'visits' => (int) $stat->visits,
                'unique_visits' => (int) $stat->unique_visits,
            ])->values();
        }

        $statsByDate = $dailyStats->keyBy(fn ($stat): string => $stat->date->toDateString());
        $days = (int) $period;

        return collect(range(0, $days - 1))->map(function (int $offset) use ($days, $statsByDate): array {
            $date = today()->subDays($days - 1)->addDays($offset);
            $stat = $statsByDate->get($date->toDateString());

            return [
                'date' => $date->toDateString(),
                'label' => $date->translatedFormat('j M'),
                'visits' => (int) ($stat?->visits ?? 0),
                'unique_visits' => (int) ($stat?->unique_visits ?? 0),
            ];
        });
    }
}
