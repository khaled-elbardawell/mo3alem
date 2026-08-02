<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QrAuthRedirectController extends Controller
{
    public function __invoke(Request $request, string $action): RedirectResponse
    {
        abort_unless(in_array($action, ['login', 'register'], true), 404);
        $request->session()->put('url.intended', route('tools.qr'));

        return redirect()->route($action);
    }
}
