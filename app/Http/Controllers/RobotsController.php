<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $content = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /dashboard\nSitemap: ".route('sitemap');

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
