<?php

namespace Database\Factories;

use App\Models\CertificateTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CertificateTemplate>
 */
class CertificateTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'certificate-template-'.fake()->unique()->uuid(),
            'label' => fake()->words(2, true),
            'image_path' => 'certificate-templates/example.webp',
            'is_builtin' => false,
            'width' => 1123,
            'height' => 794,
            'sort_order' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}
