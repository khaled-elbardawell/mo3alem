<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Services\MetricService;
use App\Services\VisitorIdentity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use function Illuminate\Support\defer;

class ShortLinkRedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        Request $request,
        ShortLink $shortLink,
        MetricService $metrics,
        VisitorIdentity $visitors,
    ): RedirectResponse|Response {
        if ($shortLink->trashed() || $shortLink->isExpired()) {
            return response()->view('public.short-link-unavailable', [
                'isPaused' => false,
            ], 410);
        }

        if (! $shortLink->is_active) {
            return response()->view('public.short-link-unavailable', [
                'isPaused' => true,
            ], 404);
        }

        $visitorIdentifier = $visitors->for($request);
        defer(fn () => $metrics->recordShortLinkVisit($shortLink, $visitorIdentifier));

        return redirect()
            ->away($shortLink->destination_url)
            ->setStatusCode(302)
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
