<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypingPassageResource extends JsonResource
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
            'language' => $this->language->value,
            'direction' => $this->language->direction(),
            'content' => $this->content,
            'word_count' => $this->word_count,
            'expected_seconds' => $this->expected_seconds,
        ];
    }
}
