<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSmartBoardPollResponseRequest;
use App\Models\SmartBoardSession;
use App\Services\SmartBoardSessionService;
use Illuminate\Http\JsonResponse;

class SmartBoardPollResponseController extends Controller
{
    public function __invoke(
        StoreSmartBoardPollResponseRequest $request,
        SmartBoardSession $smartBoardSession,
        SmartBoardSessionService $service,
    ): JsonResponse {
        $service->respond($smartBoardSession, $request->validated());

        return response()->json([
            'message' => 'تم تسجيل إجابتك.',
            'data' => $service->publicPayload($smartBoardSession->refresh()),
        ]);
    }
}
