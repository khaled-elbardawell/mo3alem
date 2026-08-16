<?php

namespace App\Http\Resources;

use App\QrCodeMode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QrCodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'mode' => $this->mode->value,
            'content_type' => $this->content_type,
            'payload' => $this->payload,
            'design' => $this->design,
            'has_logo' => filled($this->logo_path),
            'logo_url' => filled($this->logo_path) ? route('qr-codes.logo', $this->resource) : null,
            'public_url' => $this->mode === QrCodeMode::Dynamic && filled($this->public_code)
                ? route('qr.redirect', $this->public_code)
                : null,
            'is_active' => $this->is_active,
            'expires_at' => $this->expires_at?->format('Y-m-d\TH:i'),
            'scan_count' => $this->scan_count,
            'unique_scan_count' => $this->unique_scan_count,
            'version' => $this->version,
            'last_opened_at' => $this->last_opened_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
