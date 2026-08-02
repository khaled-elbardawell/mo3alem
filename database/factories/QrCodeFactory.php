<?php

namespace Database\Factories;

use App\Models\QrCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QrCode>
 */
class QrCodeFactory extends Factory
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
            'content_type' => 'url',
            'payload' => ['url' => fake()->url()],
            'design' => [
                'style' => 'classic',
                'foreground_color' => '#111827',
                'eye_color' => '#6d28d9',
                'background_color' => '#ffffff',
                'frame' => 'none',
                'center_type' => 'none',
                'center_text' => null,
            ],
            'last_opened_at' => now(),
        ];
    }
}
