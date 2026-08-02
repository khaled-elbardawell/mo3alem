<?php

namespace App\Services;

use App\QrContentType;
use Illuminate\Support\Str;

class QrPayloadBuilder
{
    /** @param array<string, mixed> $payload */
    public function build(QrContentType $contentType, array $payload): string
    {
        return match ($contentType) {
            QrContentType::Url => $payload['url'],
            QrContentType::Text => $payload['text'],
            QrContentType::Wifi => $this->wifi($payload),
        };
    }

    /** @param array<string, mixed> $payload */
    private function wifi(array $payload): string
    {
        $encryption = $payload['encryption'];
        $password = $encryption === 'nopass' ? '' : $this->escape($payload['password'] ?? '');
        $hidden = ($payload['hidden'] ?? false) ? 'true' : 'false';

        return "WIFI:T:{$encryption};S:{$this->escape($payload['ssid'])};P:{$password};H:{$hidden};;";
    }

    private function escape(string $value): string
    {
        return Str::of($value)
            ->replace(['\\', ';', ',', ':', '"'], ['\\\\', '\\;', '\\,', '\\:', '\\"'])
            ->toString();
    }
}
