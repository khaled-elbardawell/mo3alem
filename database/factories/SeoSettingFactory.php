<?php

namespace Database\Factories;

use App\Models\SeoSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeoSetting>
 */
class SeoSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_name' => 'نرد',
            'title' => 'نرد | عجلة الحظ',
            'description' => 'عجلة اختيار أسماء عربية مجانية وسهلة الاستخدام.',
            'keywords' => 'عجلة الحظ, اختيار أسماء, نرد',
            'canonical_url' => config('app.url'),
            'allow_indexing' => true,
            'twitter_card' => 'summary_large_image',
        ];
    }
}
