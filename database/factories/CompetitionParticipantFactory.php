<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\CompetitionParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompetitionParticipant>
 */
class CompetitionParticipantFactory extends Factory
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
            'name' => fake()->name(),
            'position' => fake()->numberBetween(0, 1999),
            'is_active' => true,
        ];
    }
}
