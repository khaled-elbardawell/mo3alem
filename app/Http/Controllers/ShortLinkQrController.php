<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Services\QrCodeRenderer;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ShortLinkQrController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ShortLink $shortLink, QrCodeRenderer $renderer): Response
    {
        Gate::authorize('view', $shortLink);

        $svg = $renderer->render(
            route('short-links.redirect', $shortLink->slug),
            $shortLink->resolvedQrDesign(),
        );

        return response($svg, 200, [
            'Cache-Control' => 'private, no-store',
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
