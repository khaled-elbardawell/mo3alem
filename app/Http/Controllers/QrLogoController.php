<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QrLogoController extends Controller
{
    public function __invoke(QrCode $qrCode): StreamedResponse
    {
        Gate::authorize('view', $qrCode);
        abort_unless($qrCode->logo_path && Storage::disk('local')->exists($qrCode->logo_path), 404);

        return Storage::disk('local')->response($qrCode->logo_path, headers: [
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
