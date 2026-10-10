<?php

namespace Database\Factories;

use App\Models\SmartBoard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SmartBoard>
 */
class SmartBoardFactory extends Factory
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
            'title' => fake()->words(3, true),
            'state' => [
                'schemaVersion' => 1,
                'board' => ['background' => 'aurora', 'aspectRatio' => 'auto'],
                'widgets' => [],
            ],
            'version' => 1,
            'last_opened_at' => now(),
        ];
    }
}
