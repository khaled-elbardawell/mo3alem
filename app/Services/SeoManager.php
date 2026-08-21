<?php

namespace App\Services;

use App\Models\SeoSetting;
use App\SeoPage;
use Illuminate\Support\Facades\Cache;

class SeoManager
{
    public function forPage(SeoPage $page): SeoSetting
    {
        $values = Cache::remember($this->cacheKey($page), 300, function () use ($page): array {
            $setting = SeoSetting::query()
                ->where('page_key', $page->value)
                ->first();

            return $setting?->getAttributes() ?? $page->defaults();
        });

        return (new SeoSetting)->forceFill($values);
    }

    public function forget(SeoPage $page): void
    {
        Cache::forget($this->cacheKey($page));
    }

    private function cacheKey(SeoPage $page): string
    {
        return "seo:page:{$page->value}";
    }
}
