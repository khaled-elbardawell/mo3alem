<?php

namespace App\View\Composers;

use App\Services\SiteSettingsManager;
use Illuminate\View\View;

class FooterComposer
{
    public function __construct(private SiteSettingsManager $settings) {}

    public function compose(View $view): void
    {
        $view->with('footerLinks', $this->settings->visibleFooterLinks());
    }
}
