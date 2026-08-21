<?php

namespace App\Services;

use App\FooterLinkPlatform;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteSettingsManager
{
    private const FOOTER_LINKS_CACHE_KEY = 'site-settings:footer-links';

    /** @return array<int, array<string, mixed>> */
    public function footerLinks(): array
    {
        return Cache::remember(self::FOOTER_LINKS_CACHE_KEY, 300, function (): array {
            $storedLinks = SiteSetting::query()
                ->where('key', SiteSetting::SITE_KEY)
                ->first()?->footer_links;

            return is_array($storedLinks) ? $storedLinks : $this->defaultFooterLinks();
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function visibleFooterLinks(): array
    {
        return collect($this->footerLinks())
            ->filter(fn (array $link): bool => (bool) ($link['is_active'] ?? false))
            ->map(function (array $link): ?array {
                $platform = FooterLinkPlatform::tryFrom((string) ($link['platform'] ?? ''));

                if ($platform === null) {
                    return null;
                }

                return [
                    ...$link,
                    'icon' => $platform->iconClass(),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function forgetFooterLinks(): void
    {
        Cache::forget(self::FOOTER_LINKS_CACHE_KEY);
    }

    /** @return array<int, array<string, mixed>> */
    private function defaultFooterLinks(): array
    {
        return [
            [
                'platform' => FooterLinkPlatform::Website->value,
                'label' => 'موقع معلم الحاسب',
                'url' => 'https://cmp-tch.com',
                'open_in_new_tab' => true,
                'is_active' => true,
            ],
            [
                'platform' => FooterLinkPlatform::Tools->value,
                'label' => 'أدوات معلم',
                'url' => '/#tools',
                'open_in_new_tab' => false,
                'is_active' => true,
            ],
            [
                'platform' => FooterLinkPlatform::Help->value,
                'label' => 'مركز المساعدة',
                'url' => '/#faq',
                'open_in_new_tab' => false,
                'is_active' => true,
            ],
        ];
    }
}
