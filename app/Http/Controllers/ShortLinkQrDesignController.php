<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateShortLinkQrDesignRequest;
use App\Models\ShortLink;
use App\Services\ShortLinkService;
use Illuminate\Http\JsonResponse;

class ShortLinkQrDesignController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        UpdateShortLinkQrDesignRequest $request,
        ShortLink $shortLink,
        ShortLinkService $service,
    ): JsonResponse {
        $updatedShortLink = $service->updateQrDesign($shortLink, $request->validated());

        if (! $updatedShortLink) {
            $message = 'تم تعديل الرابط من جلسة أخرى. حدّث الصفحة ثم أعد المحاولة.';

            return response()->json([
                'message' => $message,
                'errors' => ['version' => [$message]],
            ], 409);
        }

        return response()->json([
            'message' => 'تم حفظ تصميم QR.',
            'data' => [
                'design' => $updatedShortLink->resolvedQrDesign(),
                'qr_url' => route('short-links.qr', $updatedShortLink),
                'version' => $updatedShortLink->version,
            ],
        ]);
    }
}
