<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->must_change_password || $request->routeIs('profile.edit')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'يجب تغيير كلمة المرور المؤقتة قبل المتابعة.',
                'error' => 'password_change_required',
            ], 409);
        }

        return redirect()->route('profile.edit')->with(
            'status',
            'يجب تغيير كلمة المرور المؤقتة قبل استخدام الحساب.',
        );

    }
}
