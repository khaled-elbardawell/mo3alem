<?php

namespace Database\Factories;

use App\Models\ShortLink;
use App\Models\ShortLinkDailyStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShortLinkDailyStat>
 */
class ShortLinkDailyStatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'short_link_id' => ShortLink::factory(),
            'date' => fake()->unique()->dateTimeBetween('-30 days')->format('Y-m-d'),
            'visits' => fake()->numberBetween(1, 100),
            'unique_visits' => fake()->numberBetween(1, 50),
        ];
    }
}
