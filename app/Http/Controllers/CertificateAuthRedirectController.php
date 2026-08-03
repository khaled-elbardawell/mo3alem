<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CertificateAuthRedirectController extends Controller
{
    public function __invoke(Request $request, string $action): RedirectResponse
    {
        abort_unless(in_array($action, ['login', 'register'], true), 404);
        $request->session()->put('url.intended', route('tools.certificates'));

        return redirect()->route($action);
    }
}
