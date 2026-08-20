<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavedWheelResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'names_count' => $this->names_count,
            'version' => $this->version,
            'last_opened_at' => $this->last_opened_at,
            'updated_at' => $this->updated_at,
        ];

        if ($this->resource->relationLoaded('nameEntries')) {
            $data['names'] = $this->names;
        }

        return $data;
    }
}
