<?php

namespace App\Http\Controllers;

use App\Models\SmartBoardSession;
use App\Services\SmartBoardSessionService;
use Illuminate\View\View;

class SmartBoardJoinController extends Controller
{
    public function __invoke(
        SmartBoardSession $smartBoardSession,
        SmartBoardSessionService $service,
    ): View {
        $sessionData = $service->publicPayload($smartBoardSession);

        return view('public.smart-board-join', compact('smartBoardSession', 'sessionData'));
    }
}
