<?php

namespace Database\Factories;

use App\Models\TypingPassage;
use App\Services\TypingPassageCalculator;
use App\TypingPassageLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TypingPassage>
 */
class TypingPassageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $content = fake()->unique()->sentence(18);
        $attributes = app(TypingPassageCalculator::class)->attributes($content);

        return [
            'seed_key' => null,
            'language' => TypingPassageLanguage::English,
            ...$attributes,
            'is_active' => true,
        ];
    }

    public function arabic(): static
    {
        return $this->state(function (): array {
            $content = 'يقرأ الطالب فقرة تعليمية قصيرة ثم يراجع أفكارها الأساسية ويكتب ملخصا واضحا يساعده على تذكر المعلومات المهمة بسهولة.';

            return [
                'language' => TypingPassageLanguage::Arabic,
                ...app(TypingPassageCalculator::class)->attributes($content),
            ];
        });
    }
}
