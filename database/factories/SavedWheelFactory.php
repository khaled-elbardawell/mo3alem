<?php

namespace Database\Factories;

use App\Models\SavedWheel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedWheel>
 */
class SavedWheelFactory extends Factory
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
            'title' => fake()->unique()->words(3, true),
            'active_title' => fn (array $attributes): string => $attributes['title'],
            'names' => ['أحمد', 'سارة', 'محمد'],
            'results' => [],
            'names_count' => 3,
            'version' => 1,
            'last_opened_at' => now(),
        ];
    }
}
