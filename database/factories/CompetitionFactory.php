<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Competition>
 */
class CompetitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'saved_wheel_id' => null,
            'title' => fake()->words(3, true),
            'names' => ['أحمد', 'سارة', 'محمد'],
            'results' => [],
            'names_count' => 3,
            'results_count' => 0,
            'version' => 1,
            'status' => 'draft',
            'sync_source_list' => false,
            'last_opened_at' => now(),
        ];
    }
}
