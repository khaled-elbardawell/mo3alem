<?php

namespace Database\Factories;

use App\Models\SmartBoard;
use App\Models\SmartBoardAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SmartBoardAsset>
 */
class SmartBoardAssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'smart_board_id' => SmartBoard::factory(),
            'disk' => 'local',
            'path' => 'smart-boards/example.webp',
            'mime_type' => 'image/webp',
            'size' => 1024,
            'original_name' => 'example.webp',
        ];
    }
}
