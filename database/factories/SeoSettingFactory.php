<?php

namespace Database\Factories;

use App\Models\SeoSetting;
use App\SeoPage;
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
            'page_key' => SeoPage::Home->value,
            'site_name' => 'معلم',
            'title' => 'معلم | أدوات ذكية لكل معلم',
            'description' => 'منصة أدوات تعليمية عربية تساعد المعلم على إنجاز مهامه بسهولة.',
            'keywords' => 'أدوات المعلم, عجلة الأسماء, إنشاء QR, إنشاء شهادات, معلم',
            'canonical_url' => config('app.url'),
            'allow_indexing' => true,
            'allow_following' => true,
            'include_in_sitemap' => true,
            'sitemap_change_frequency' => 'weekly',
            'sitemap_priority' => 1.0,
            'twitter_card' => 'summary_large_image',
        ];
    }
}
