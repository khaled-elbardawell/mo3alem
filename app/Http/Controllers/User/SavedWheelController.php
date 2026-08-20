<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SavedWheel;
use App\Models\SavedWheelName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        $names = $savedWheel->nameEntries()
            ->select(['id', 'saved_wheel_id', 'name', 'position'])
            ->when($nameSearch !== '', fn ($query) => $query->where('name', 'like', "%{$nameSearch}%"))
            ->when(
                $nameSort === 'ascending',
                fn ($query) => $query->orderBy('name')->orderBy('position'),
            )
            ->when(
                $nameSort === 'descending',
                fn ($query) => $query->orderByDesc('name')->orderBy('position'),
            )
            ->when($nameSort === 'original', fn ($query) => $query->orderBy('position'))
            ->paginate(100, pageName: 'names_page')
            ->withQueryString()
            ->through(fn (SavedWheelName $entry): array => [
                'index' => $entry->position,
                'name' => $entry->name,
            ]);

        return view('users.saved-wheels.show', compact('nameSearch', 'nameSort', 'names', 'savedWheel'));
    }
}
