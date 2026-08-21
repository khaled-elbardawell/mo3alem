<?php

namespace App\Http\Controllers\Admin;

use App\FooterLinkPlatform;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FooterLinksUpdateRequest;
use App\Models\SiteSetting;
use App\Services\AdminAuditService;
use App\Services\SiteSettingsManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(SiteSettingsManager $settings): View
    {
        return view('admin.settings.edit', [
            'footerLinks' => $settings->footerLinks(),
            'platforms' => FooterLinkPlatform::cases(),
        ]);
    }

    public function updateFooterLinks(
        FooterLinksUpdateRequest $request,
        SiteSettingsManager $settings,
        AdminAuditService $audit,
    ): RedirectResponse {
        $siteSetting = SiteSetting::query()->firstOrNew([
            'key' => SiteSetting::SITE_KEY,
        ]);
        $before = $siteSetting->exists ? $siteSetting->footer_links : null;
        $footerLinks = collect($request->validated('footer_links', []))
            ->map(fn (array $link): array => [
                'platform' => $link['platform'],
                'label' => $link['label'],
                'url' => $link['url'],
                'open_in_new_tab' => (bool) $link['open_in_new_tab'],
                'is_active' => (bool) $link['is_active'],
            ])
            ->values()
            ->all();

        $siteSetting->fill(['footer_links' => $footerLinks])->save();
        $settings->forgetFooterLinks();

        $audit->record(
            $request,
            'settings.footer-links.updated',
            $siteSetting,
            ['footer_links' => $before],
            ['footer_links' => $footerLinks],
        );

        return back()->with('status', 'تم حفظ روابط الفوتر بنجاح.');
    }
}
