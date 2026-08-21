<?php

namespace App\Http\Controllers;

use App\SeoPage;
use App\Services\SeoManager;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(SeoManager $seoManager): Response
    {
        $urls = collect(SeoPage::cases())
            ->map(function (SeoPage $page) use ($seoManager): ?string {
                $seo = $seoManager->forPage($page);

                if (! $seo->allow_indexing || ! $seo->include_in_sitemap) {
                    return null;
                }

                $location = e($seo->canonical_url ?: route($page->routeName()));
                $lastModified = $seo->updated_at?->toAtomString() ?? now()->toAtomString();
                $changeFrequency = e($seo->sitemap_change_frequency);
                $priority = number_format($seo->sitemap_priority, 1);

                return "    <url><loc>{$location}</loc><lastmod>{$lastModified}</lastmod><changefreq>{$changeFrequency}</changefreq><priority>{$priority}</priority></url>";
            })
            ->filter()
            ->implode("\n");

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{$urls}
</urlset>
XML;

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
