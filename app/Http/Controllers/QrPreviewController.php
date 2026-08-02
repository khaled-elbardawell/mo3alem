<?php

namespace App\Http\Controllers;

use App\Http\Requests\RenderQrCodeRequest;
use App\QrContentType;
use App\Services\QrCodeRenderer;
use App\Services\QrPayloadBuilder;
use Illuminate\Http\JsonResponse;

class QrPreviewController extends Controller
{
    public function __invoke(
        RenderQrCodeRequest $request,
        QrPayloadBuilder $payloadBuilder,
        QrCodeRenderer $renderer,
    ): JsonResponse {
        $data = $request->validated();
        $content = $payloadBuilder->build(QrContentType::from($data['content_type']), $data['payload']);

        return response()->json([
            'svg' => $renderer->render($content, $data['design']),
        ])->header('Cache-Control', 'no-store');
    }
}
