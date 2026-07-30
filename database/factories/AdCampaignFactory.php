<?php

namespace Database\Factories;

use App\AdCampaignStatus;
use App\AdPlacement;
use App\Models\AdCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdCampaign>
 */
class AdCampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'image_path' => 'ads/example.webp',
            'target_url' => fake()->url(),
            'alt_text' => fake()->sentence(5),
            'placement' => fake()->randomElement(AdPlacement::cases()),
            'status' => AdCampaignStatus::Active,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
            'weight' => fake()->numberBetween(1, 10),
        ];
    }
}
