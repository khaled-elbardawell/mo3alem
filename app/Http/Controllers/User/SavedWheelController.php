<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SavedWheel;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SavedWheelController extends Controller
{
    public function show(Request $request, SavedWheel $savedWheel): View
    {
        Gate::authorize('view', $savedWheel);

        $nameSearch = $request->string('names_search')->squish()->toString();
        $nameSort = match ($request->string('names_sort')->toString()) {
            'ascending' => 'ascending',
            'descending' => 'descending',
            default => 'original',
        };
        $names = $this->paginate(
            $this->filterNames($savedWheel->names, $nameSearch, $nameSort),
            $request,
            'names_page',
            100,
        );

        return view('users.saved-wheels.show', compact('nameSearch', 'nameSort', 'names', 'savedWheel'));
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
