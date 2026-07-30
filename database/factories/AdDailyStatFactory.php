<?php

namespace Database\Factories;

use App\Models\AdCampaign;
use App\Models\AdDailyStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdDailyStat>
 */
class AdDailyStatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ad_campaign_id' => AdCampaign::factory(),
            'date' => today(),
            'impressions' => fake()->numberBetween(10, 1000),
            'clicks' => fake()->numberBetween(0, 10),
        ];
    }
}
