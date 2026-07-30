<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class VisitorIdentity
{
    public const COOKIE_NAME = 'nard_visitor_id';

    public function for(Request $request): string
    {
        if ($request->user()) {
            return "user:{$request->user()->getAuthIdentifier()}";
        }

        $visitorId = $request->cookie(self::COOKIE_NAME);

        if (! is_string($visitorId) || ! Str::isUuid($visitorId)) {
            $visitorId = (string) Str::uuid();

            Cookie::queue(cookie(
                self::COOKIE_NAME,
                $visitorId,
                60 * 24 * 365,
                secure: $request->isSecure(),
                httpOnly: true,
                sameSite: 'lax',
            ));
        }

        return "visitor:{$visitorId}";
    }
}
