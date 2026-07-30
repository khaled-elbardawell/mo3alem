<?php

namespace App\Http\Controllers;

use App\Models\SeoSetting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $allowIndexing = Cache::remember(
            'seo:allow-indexing',
            300,
            fn (): bool => SeoSetting::query()->value('allow_indexing') ?? true,
        );

        $content = $allowIndexing
            ? "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /dashboard\nSitemap: ".route('sitemap')
            : "User-agent: *\nDisallow: /";

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
