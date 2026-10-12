<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSmartBoardRequest;
use App\Http\Requests\UpdateSmartBoardRequest;
use App\Http\Resources\SmartBoardResource;
use App\Models\SmartBoard;
use App\Services\MetricService;
use App\Services\SmartBoardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SmartBoardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', SmartBoard::class);

        $smartBoards = $request->user()->smartBoards()
            ->select(['id', 'title', 'version', 'last_opened_at', 'updated_at'])
            ->latest('updated_at')
            ->limit((int) config('resource_limits.smart_boards', 20))
            ->get();

        return response()->json(['data' => SmartBoardResource::collection($smartBoards)->resolve($request)]);
    }

    public function store(
        StoreSmartBoardRequest $request,
        SmartBoardService $service,
        MetricService $metrics,
    ): JsonResponse {
        $smartBoard = $service->create($request->user(), $request->validated());
        $metrics->increment('smart_boards_created');

        return response()->json([
            'message' => 'تم حفظ السبورة.',
            'data' => SmartBoardResource::make($smartBoard)->resolve($request),
        ], 201);
    }

    public function show(SmartBoard $smartBoard, Request $request): JsonResponse
    {
        Gate::authorize('view', $smartBoard);
        $smartBoard->forceFill(['last_opened_at' => now()])->save();

        return response()->json(['data' => SmartBoardResource::make($smartBoard)->resolve($request)]);
    }

    public function update(
        UpdateSmartBoardRequest $request,
        SmartBoard $smartBoard,
        SmartBoardService $service,
    ): JsonResponse {
        $updatedBoard = $service->update($smartBoard, $request->validated());

        if (! $updatedBoard) {
            return response()->json([
                'message' => 'عُدّلت السبورة من جهاز آخر.',
                'conflict' => true,
                'data' => SmartBoardResource::make($smartBoard->fresh())->resolve($request),
            ], 409);
        }

        return response()->json([
            'message' => 'تم الحفظ.',
            'data' => SmartBoardResource::make($updatedBoard)->resolve($request),
        ]);
    }

    public function destroy(SmartBoard $smartBoard, SmartBoardService $service): JsonResponse
    {
        Gate::authorize('delete', $smartBoard);
        $service->delete($smartBoard);

        return response()->json(['message' => 'تم حذف السبورة.']);
    }
}
