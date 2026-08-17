<?php

namespace Database\Factories;

use App\Models\QrTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QrTemplate>
 */
class QrTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'qr-template-'.fake()->unique()->uuid(),
            'label' => fake()->words(2, true),
            'image_path' => 'qr-templates/example.webp',
            'is_builtin' => false,
            'width' => 1968,
            'height' => 1968,
            'qr_x' => 450,
            'qr_y' => 450,
            'qr_size' => 1000,
            'sort_order' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}
