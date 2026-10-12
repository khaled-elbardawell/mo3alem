<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\SmartBoard;
use App\SeoPage;
use App\Services\SeoManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SmartBoardToolController extends Controller
{
    public function __invoke(Request $request, SeoManager $seoManager): View
    {
        $smartBoard = null;

        if ($request->filled('board') && $request->user()) {
            $smartBoard = SmartBoard::query()->findOrFail($request->integer('board'));
            Gate::authorize('view', $smartBoard);
            $smartBoard->forceFill(['last_opened_at' => now()])->save();
        }

        $seo = $seoManager->forPage(SeoPage::SmartBoard);
        $smartBoardConfig = [
            'authenticated' => (bool) $request->user(),
            'verified' => $request->user()?->hasVerifiedEmail() ?? false,
            'board' => $smartBoard?->only(['id', 'title', 'state', 'version']),
            'limits' => [
                'boards' => (int) config('resource_limits.smart_boards', 20),
                'widgets' => (int) config('resource_limits.smart_board_widgets', 40),
            ],
            'routes' => [
                'login' => route('login'),
                'register' => route('register'),
                'dashboard' => $request->user() ? route('dashboard', ['section' => 'boards']) : null,
                'boards' => $request->user() ? route('smart-boards.index') : null,
                'savedLists' => $request->user() ? route('saved-wheels.index') : null,
                'sessions' => route('smart-board-sessions.store'),
                'qrRender' => route('tools.qr.render'),
            ],
            'csrfToken' => csrf_token(),
        ];

        return view('public.tools.smart-board', compact('seo', 'smartBoardConfig'));
    }
}
