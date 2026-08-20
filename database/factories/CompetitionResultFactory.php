<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\CompetitionResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompetitionResult>
 */
class CompetitionResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'competition_id' => Competition::factory(),
            'competition_participant_id' => null,
            'round' => fake()->numberBetween(1, 10000),
            'sort_order' => fake()->numberBetween(0, 9999),
            'name_snapshot' => fake()->name(),
            'position' => fake()->numberBetween(1, 2000),
            'won_at' => now(),
        ];
    }
}
