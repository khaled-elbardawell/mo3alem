<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response for a successful login.
     */
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return response()->json([
                'two_factor' => false,
                'password_change_required' => (bool) $request->user()?->must_change_password,
            ]);
        }

        if ($request->user()?->must_change_password) {
            return redirect()->route('profile.edit')->with(
                'status',
                'يجب تغيير كلمة المرور المؤقتة قبل استخدام الحساب.',
            );
        }

        return redirect()->intended(Fortify::redirects('login'));
    }
}
