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
            'site_name' => 'معلم',
            'title' => 'معلم | أدوات ذكية لكل معلم',
            'description' => 'منصة أدوات تعليمية عربية تساعد المعلم على إنجاز مهامه بسهولة.',
            'keywords' => 'أدوات المعلم, عجلة الأسماء, إنشاء QR, إنشاء شهادات, معلم',
            'canonical_url' => config('app.url'),
            'allow_indexing' => true,
            'twitter_card' => 'summary_large_image',
        ];
    }
}
