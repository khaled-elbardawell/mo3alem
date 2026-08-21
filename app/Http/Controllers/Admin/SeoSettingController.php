<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SeoSettingUpdateRequest;
use App\Models\SeoSetting;
use App\SeoPage;
use App\Services\AdminAuditService;
use App\Services\SeoManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SeoSettingController extends Controller
{
    public function edit(Request $request, SeoManager $seoManager): View
    {
        $selectedPage = SeoPage::tryFrom((string) $request->query('page')) ?? SeoPage::Home;
        $pages = collect(SeoPage::cases())->map(fn (SeoPage $page): array => [
            'page' => $page,
            'setting' => $seoManager->forPage($page),
        ]);
        $seo = $pages->firstWhere('page', $selectedPage)['setting'];

        return view('admin.seo.edit', compact('pages', 'selectedPage', 'seo'));
    }

    public function update(
        SeoSettingUpdateRequest $request,
        SeoPage $page,
        AdminAuditService $audit,
        SeoManager $seoManager,
    ): RedirectResponse {
        $seo = SeoSetting::query()->firstOrNew(['page_key' => $page->value]);
        $before = $seo->exists ? $seo->toArray() : null;
        $data = $request->safe()->except(['og_image', 'remove_og_image']);
        $oldImage = null;

        if ($request->hasFile('og_image')) {
            $oldImage = $seo->og_image_path;
            $data['og_image_path'] = $request->file('og_image')->store('seo', 'public');
        } elseif ($request->boolean('remove_og_image')) {
            $oldImage = $seo->og_image_path;
            $data['og_image_path'] = null;
        }

        $seo->fill(['page_key' => $page->value, ...$data])->save();

        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        $seoManager->forget($page);
        $audit->record($request, 'seo.updated', $seo, $before, $seo->fresh()->toArray());

        return redirect()
            ->route('admin.seo.edit', ['page' => $page->value])
            ->with('status', "تم حفظ إعدادات SEO لصفحة {$page->label()}.");
    }
}
