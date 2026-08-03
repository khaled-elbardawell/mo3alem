<?php

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
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
            'template_key' => 'b6',
            'design' => [
                'width' => 1120,
                'height' => 790,
                'elements' => [
                    [
                        'id' => (string) fake()->uuid(),
                        'type' => 'text',
                        'text' => 'شهادة تقدير',
                        'x' => 160,
                        'y' => 160,
                        'width' => 800,
                        'height' => 90,
                        'font_size' => 58,
                        'font_family' => 'Tajawal',
                        'font_weight' => 900,
                        'color' => '#172b52',
                        'text_align' => 'center',
                        'direction' => 'rtl',
                        'rotation' => 0,
                        'opacity' => 1,
                        'locked' => false,
                    ],
                ],
            ],
            'last_opened_at' => now(),
        ];
    }
}
