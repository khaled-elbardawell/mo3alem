<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexCompetitionRequest;
use App\Http\Requests\StoreCompetitionRequest;
use App\Http\Requests\UpdateCompetitionRequest;
use App\Http\Resources\CompetitionResource;
use App\Models\Competition;
use App\Services\CompetitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CompetitionController extends Controller
{
    public function index(IndexCompetitionRequest $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();

        $competitions = $request->user()->competitions()
            ->select([
                'id',
                'saved_wheel_id',
                'title',
                'names_count',
                'results_count',
                'version',
                'status',
                'sync_source_list',
                'last_opened_at',
                'updated_at',
            ])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->latest('updated_at')
            ->latest('id')
            ->cursorPaginate(40);

        return response()->json([
            'data' => CompetitionResource::collection($competitions->items())->resolve($request),
            'next_cursor' => $competitions->nextCursor()?->encode(),
            'has_more' => $competitions->hasMorePages(),
        ]);
    }

    public function store(StoreCompetitionRequest $request, CompetitionService $service): JsonResponse
    {
        $competition = $service->create($request->user(), $request->validated());

        return response()->json([
            'message' => 'تم إنشاء المسابقة.',
            'data' => CompetitionResource::make($competition)->resolve($request),
        ], 201);
    }

    public function show(Competition $competition): JsonResponse
    {
        Gate::authorize('view', $competition);
        $competition->forceFill(['last_opened_at' => now()])->save();
        $competition->load(['activeParticipants', 'resultEntries']);

        return response()->json([
            'data' => CompetitionResource::make($competition)->resolve(request()),
        ]);
    }

    public function update(
        UpdateCompetitionRequest $request,
        Competition $competition,
        CompetitionService $service,
    ): JsonResponse {
        $updatedCompetition = $service->update($competition, $request->validated());

        if (! $updatedCompetition) {
            return response()->json([
                'message' => 'عُدّلت المسابقة من جهاز آخر.',
                'conflict' => true,
                'data' => CompetitionResource::make(
                    $competition->fresh()->load(['activeParticipants', 'resultEntries']),
                )->resolve($request),
            ], 409);
        }

        return response()->json([
            'message' => 'تم الحفظ.',
            'data' => CompetitionResource::make($updatedCompetition)->resolve($request),
        ]);
    }

    public function destroy(Request $request, Competition $competition): JsonResponse|RedirectResponse
    {
        Gate::authorize('delete', $competition);
        $competition->delete();

        return $request->expectsJson()
            ? response()->json(['message' => 'تم حذف المسابقة.'])
            : back()->with('status', 'تم حذف المسابقة.');
    }
}
