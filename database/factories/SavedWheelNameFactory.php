<?php

namespace Database\Factories;

use App\Models\SavedWheel;
use App\Models\SavedWheelName;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedWheelName>
 */
class SavedWheelNameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'saved_wheel_id' => SavedWheel::factory(),
            'name' => fake()->name(),
            'position' => fake()->numberBetween(0, 1999),
        ];
    }
}
