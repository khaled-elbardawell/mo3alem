<?php

namespace Database\Factories;

use App\Models\DailyMetric;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyMetric>
 */
class DailyMetricFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->unique()->date(),
            'site_visits' => fake()->numberBetween(0, 5000),
            'registrations' => fake()->numberBetween(0, 20),
            'active_users' => fake()->numberBetween(0, 500),
            'saved_wheels' => fake()->numberBetween(0, 100),
            'names_saved' => fake()->numberBetween(0, 5000),
            'spins' => fake()->numberBetween(0, 10000),
            'imports' => fake()->numberBetween(0, 100),
            'qr_generated' => fake()->numberBetween(0, 1000),
            'qr_saved' => fake()->numberBetween(0, 100),
            'certificate_generated' => fake()->numberBetween(0, 1000),
            'certificate_saved' => fake()->numberBetween(0, 100),
            'ad_impressions' => fake()->numberBetween(0, 10000),
            'ad_clicks' => fake()->numberBetween(0, 500),
        ];
    }
}
