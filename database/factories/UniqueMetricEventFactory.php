<?php

namespace Database\Factories;

use App\Models\UniqueMetricEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UniqueMetricEvent>
 */
class UniqueMetricEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->date(),
            'event_hash' => hash('sha256', fake()->unique()->uuid()),
        ];
    }
}
