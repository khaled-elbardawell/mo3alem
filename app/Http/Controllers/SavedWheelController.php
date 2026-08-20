<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexSavedWheelRequest;
use App\Http\Requests\StoreSavedWheelRequest;
use App\Http\Requests\UpdateSavedWheelRequest;
use App\Http\Resources\SavedWheelResource;
use App\Models\SavedWheel;
use App\Services\SavedWheelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SavedWheelController extends Controller
{
    public function index(IndexSavedWheelRequest $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();

        $savedWheels = $request->user()->savedWheels()
            ->select(['id', 'title', 'names_count', 'version', 'last_opened_at', 'updated_at'])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->latest('updated_at')
            ->latest('id')
            ->cursorPaginate(40);

        return response()->json([
            'data' => SavedWheelResource::collection($savedWheels->items())->resolve($request),
            'next_cursor' => $savedWheels->nextCursor()?->encode(),
            'has_more' => $savedWheels->hasMorePages(),
        ]);
    }

    public function store(StoreSavedWheelRequest $request, SavedWheelService $service): JsonResponse
    {
        $savedWheel = $service->create($request->user(), $request->validated());

        return response()->json([
            'message' => 'تم حفظ القائمة.',
            'data' => SavedWheelResource::make($savedWheel)->resolve($request),
        ], 201);
    }

    public function show(SavedWheel $savedWheel): JsonResponse
    {
        Gate::authorize('view', $savedWheel);

        $savedWheel->forceFill(['last_opened_at' => now()])->save();
        $savedWheel->load('nameEntries');

        return response()->json([
            'data' => SavedWheelResource::make($savedWheel)->resolve(request()),
        ]);
    }

    public function update(
        UpdateSavedWheelRequest $request,
        SavedWheel $savedWheel,
        SavedWheelService $service,
    ): JsonResponse {
        $updatedWheel = $service->update($savedWheel, $request->validated());

        if (! $updatedWheel) {
            return response()->json([
                'message' => 'عُدّلت القائمة من جهاز آخر.',
                'conflict' => true,
                'data' => SavedWheelResource::make($savedWheel->fresh()->load('nameEntries'))->resolve($request),
            ], 409);
        }

        return response()->json([
            'message' => 'تم الحفظ.',
            'data' => SavedWheelResource::make($updatedWheel)->resolve($request),
        ]);
    }

    public function destroy(Request $request, SavedWheel $savedWheel): JsonResponse|RedirectResponse
    {
        Gate::authorize('delete', $savedWheel);
        $savedWheel->delete();

        return $request->expectsJson()
            ? response()->json(['message' => 'تم حذف القائمة.'])
            : back()->with('status', 'تم حذف القائمة.');
    }
}
