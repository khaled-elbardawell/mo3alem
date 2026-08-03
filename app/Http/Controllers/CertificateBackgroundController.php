<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateBackgroundController extends Controller
{
    public function __invoke(Certificate $certificate): StreamedResponse
    {
        Gate::authorize('view', $certificate);
        abort_unless(
            $certificate->background_path && Storage::disk('local')->exists($certificate->background_path),
            404,
        );

        return Storage::disk('local')->response($certificate->background_path, headers: [
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
