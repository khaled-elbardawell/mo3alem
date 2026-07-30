<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function show(Request $request, Competition $competition): View
    {
        Gate::authorize('view', $competition);

        $competition->load([
            'savedWheel' => function (BelongsTo $savedWheel) use ($competition): void {
                $savedWheel
                    ->where('user_id', $competition->user_id)
                    ->select(['id', 'user_id', 'title', 'names_count']);
            },
        ]);

        $nameSearch = $request->string('names_search')->squish()->toString();
        $nameSort = match ($request->string('names_sort')->toString()) {
            'ascending' => 'ascending',
            'descending' => 'descending',
            default => 'original',
        };
        $resultSearch = $request->string('results_search')->squish()->toString();
        $resultRound = $request->integer('results_round') > 0
            ? $request->integer('results_round')
            : null;

        $names = $this->paginate(
            $this->filterNames($competition->names, $nameSearch, $nameSort),
            $request,
            'names_page',
            100,
        );
        $results = $this->paginate(
            $this->filterResults($competition->results, $resultSearch, $resultRound),
            $request,
            'results_page',
            50,
        );

        return view('users.competitions.show', compact(
            'competition',
            'nameSearch',
            'nameSort',
            'names',
            'resultRound',
            'resultSearch',
            'results',
        ));
    }

    /**
     * @param  array<int, string>  $names
     * @return Collection<int, array{index:int,name:string}>
     */
    private function filterNames(array $names, string $search, string $sort): Collection
    {
        $filteredNames = collect($names)
            ->map(fn (string $name, int $index): array => [
                'index' => $index,
                'name' => $name,
            ])
            ->when(
                $search !== '',
                fn (Collection $names): Collection => $names->filter(
                    fn (array $entry): bool => Str::contains(
                        Str::lower($entry['name']),
                        Str::lower($search),
                    ),
                ),
            );

        return match ($sort) {
            'ascending' => $filteredNames->sortBy(
                fn (array $entry): string => Str::lower($entry['name']),
                SORT_NATURAL,
            )->values(),
            'descending' => $filteredNames->sortByDesc(
                fn (array $entry): string => Str::lower($entry['name']),
                SORT_NATURAL,
            )->values(),
            default => $filteredNames->values(),
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     * @return Collection<int, array{round:int,name:string,position:mixed,won_at:CarbonImmutable}>
     */
    private function filterResults(array $results, string $search, ?int $round): Collection
    {
        return collect($results)
            ->map(fn (array $result): array => [
                'round' => (int) data_get($result, 'round'),
                'name' => (string) data_get($result, 'name'),
                'position' => data_get($result, 'position'),
                'won_at' => CarbonImmutable::parse(data_get($result, 'date')),
            ])
            ->when(
                $search !== '',
                fn (Collection $results): Collection => $results->filter(
                    fn (array $result): bool => Str::contains(
                        Str::lower($result['name']),
                        Str::lower($search),
                    ),
                ),
            )
            ->when(
                $round !== null,
                fn (Collection $results): Collection => $results->where('round', $round),
            )
            ->sortByDesc('round')
            ->values();
    }

    /**
     * @template TValue
     *
     * @param  Collection<int, TValue>  $items
     * @return LengthAwarePaginator<int, TValue>
     */
    private function paginate(
        Collection $items,
        Request $request,
        string $pageName,
        int $perPage,
    ): LengthAwarePaginator {
        $currentPage = LengthAwarePaginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            $items->forPage($currentPage, $perPage)->values(),
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => $pageName,
            ],
        );
    }
}
