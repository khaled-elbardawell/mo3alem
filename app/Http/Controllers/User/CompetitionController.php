<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionParticipant;
use App\Models\CompetitionResult;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

        $names = $competition->activeParticipants()
            ->select(['id', 'competition_id', 'name', 'position'])
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
            ->through(fn (CompetitionParticipant $participant): array => [
                'index' => $participant->position,
                'name' => $participant->name,
            ]);
        $results = $competition->resultEntries()
            ->select([
                'id',
                'competition_id',
                'round',
                'name_snapshot',
                'position',
                'won_at',
            ])
            ->when($resultSearch !== '', fn ($query) => $query->where('name_snapshot', 'like', "%{$resultSearch}%"))
            ->when($resultRound !== null, fn ($query) => $query->where('round', $resultRound))
            ->orderByDesc('round')
            ->paginate(50, pageName: 'results_page')
            ->withQueryString()
            ->through(fn (CompetitionResult $result): array => [
                'round' => $result->round,
                'name' => $result->name_snapshot,
                'position' => $result->position,
                'won_at' => $result->won_at,
            ]);

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
}
