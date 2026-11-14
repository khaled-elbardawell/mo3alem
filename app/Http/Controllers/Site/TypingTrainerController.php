<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\SeoPage;
use App\Services\SeoManager;
use Illuminate\View\View;

class TypingTrainerController extends Controller
{
    public function __invoke(SeoManager $seoManager): View
    {
        $seo = $seoManager->forPage(SeoPage::TypingTrainer);

        return view('public.tools.typing-trainer', compact('seo'));
    }
}
