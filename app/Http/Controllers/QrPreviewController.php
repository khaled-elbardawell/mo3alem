<?php

namespace App\Http\Controllers;

use App\Http\Requests\RenderQrCodeRequest;
use App\Models\QrCode;
use App\QrCodeMode;
use App\QrContentType;
use App\Services\QrCodeRenderer;
use App\Services\QrContentResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class QrPreviewController extends Controller
{
    public function __invoke(
        RenderQrCodeRequest $request,
        QrContentResolver $contentResolver,
        QrCodeRenderer $renderer,
    ): JsonResponse {
        $data = $request->validated();
        $mode = QrCodeMode::from($data['mode']);
        $qrCode = null;

        if ($mode === QrCodeMode::Dynamic) {
            $qrCode = QrCode::query()->findOrFail($data['qr_code_id'] ?? null);
            Gate::authorize('view', $qrCode);
        }

        $content = $contentResolver->resolve(
            $mode,
            QrContentType::from($data['content_type']),
            $data['payload'],
            $qrCode,
        );

        return response()->json([
            'svg' => $renderer->render($content, $data['design']),
        ])->header('Cache-Control', 'no-store');
    }
}
