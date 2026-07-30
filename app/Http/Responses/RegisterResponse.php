<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Create an HTTP response for a successful registration.
     */
    public function toResponse($request): Response
    {
        return $request->wantsJson()
            ? new JsonResponse('', 201)
            : redirect()->route('verification.notice');
    }
}
