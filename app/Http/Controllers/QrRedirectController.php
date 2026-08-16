<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Services\MetricService;
use App\Services\VisitorIdentity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use function Illuminate\Support\defer;

class QrRedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        Request $request,
        QrCode $qrCode,
        MetricService $metrics,
        VisitorIdentity $visitors,
    ): RedirectResponse {
        abort_unless($qrCode->isAvailableForRedirect(), 410, 'رمز QR غير متاح حاليًا.');

        $visitorIdentifier = $visitors->for($request);
        defer(fn () => $metrics->recordQrScan($qrCode, $visitorIdentifier));

        return redirect()
            ->away($qrCode->payload['url'])
            ->setStatusCode(302)
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
