<?php

namespace Database\Factories;

use App\FooterLinkPlatform;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => SiteSetting::SITE_KEY,
            'footer_links' => [
                [
                    'platform' => FooterLinkPlatform::Website->value,
                    'label' => 'موقع معلم الحاسب',
                    'url' => 'https://cmp-tch.com',
                    'open_in_new_tab' => true,
                    'is_active' => true,
                ],
            ],
        ];
    }
}
