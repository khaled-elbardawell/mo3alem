<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificateResource extends JsonResource
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
            'template_key' => $this->template_key,
            'design' => $this->design,
            'has_custom_background' => filled($this->background_path),
            'background_url' => filled($this->background_path)
                ? route('certificates.background', $this->resource)
                : null,
            'version' => $this->version,
            'last_opened_at' => $this->last_opened_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
