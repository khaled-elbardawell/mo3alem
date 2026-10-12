<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSmartBoardSessionRequest;
use App\Http\Requests\UpdateSmartBoardSessionRequest;
use App\Models\SmartBoardSession;
use App\Services\SmartBoardSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmartBoardSessionController extends Controller
{
    public function store(StoreSmartBoardSessionRequest $request, SmartBoardSessionService $service): JsonResponse
    {
        $created = $service->create($request->user(), $request->validated());

        return response()->json([
            'data' => [
                ...$service->publicPayload($created['session']),
                'owner_token' => $created['ownerToken'],
                'join_url' => route('smart-board.join', $created['session']),
            ],
        ], 201);
    }

    public function show(SmartBoardSession $smartBoardSession, SmartBoardSessionService $service): JsonResponse
    {
        return response()->json(['data' => $service->publicPayload($smartBoardSession)]);
    }

    public function update(
        UpdateSmartBoardSessionRequest $request,
        SmartBoardSession $smartBoardSession,
        SmartBoardSessionService $service,
    ): JsonResponse {
        $session = $service->update($request, $smartBoardSession, $request->validated());

        return response()->json(['data' => $service->publicPayload($session)]);
    }

    public function destroy(
        Request $request,
        SmartBoardSession $smartBoardSession,
        SmartBoardSessionService $service,
    ): JsonResponse {
        $service->close($request, $smartBoardSession);

        return response()->json(['message' => 'تم إغلاق الجلسة.']);
    }
}
