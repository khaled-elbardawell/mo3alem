<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmartBoardResource extends JsonResource
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
            'state' => $this->when(
                $request->routeIs('smart-boards.show', 'smart-boards.update'),
                $this->state,
            ),
            'version' => $this->version,
            'last_opened_at' => $this->last_opened_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
