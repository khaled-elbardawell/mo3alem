<?php

namespace App\Services;

use App\Models\QrCode;
use App\QrCodeMode;
use App\QrContentType;
use Illuminate\Validation\ValidationException;

class QrContentResolver
{
    public function __construct(private QrPayloadBuilder $payloadBuilder) {}

    /** @param array<string, mixed> $payload */
    public function resolve(
        QrCodeMode $mode,
        QrContentType $contentType,
        array $payload,
        ?QrCode $qrCode = null,
    ): string {
        if ($mode === QrCodeMode::Static) {
            return $this->payloadBuilder->build($contentType, $payload);
        }

        if (! $qrCode || $qrCode->mode !== QrCodeMode::Dynamic || blank($qrCode->public_code)) {
            throw ValidationException::withMessages([
                'mode' => 'احفظ الرمز الديناميكي أولًا لإنشاء رابط التحويل الثابت.',
            ]);
        }

        return route('qr.redirect', $qrCode->public_code);
    }
}
