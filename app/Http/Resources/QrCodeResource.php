<?php

namespace App\Http\Resources;

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
            'content_type' => $this->content_type,
            'payload' => $this->payload,
            'design' => $this->design,
            'has_logo' => filled($this->logo_path),
            'logo_url' => filled($this->logo_path) ? route('qr-codes.logo', $this->resource) : null,
            'version' => $this->version,
            'last_opened_at' => $this->last_opened_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
