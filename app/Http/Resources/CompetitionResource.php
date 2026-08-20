<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetitionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'saved_wheel_id' => $this->saved_wheel_id,
            'title' => $this->title,
            'names_count' => $this->names_count,
            'results_count' => $this->results_count,
            'version' => $this->version,
            'status' => $this->status,
            'sync_source_list' => $this->sync_source_list,
            'last_opened_at' => $this->last_opened_at,
            'updated_at' => $this->updated_at,
        ];

        if ($this->resource->relationLoaded('activeParticipants')) {
            $data['names'] = $this->names;
        }

        if ($this->resource->relationLoaded('resultEntries')) {
            $data['results'] = $this->results;
        }

        return $data;
    }
}
