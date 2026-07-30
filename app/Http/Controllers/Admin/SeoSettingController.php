<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SeoSettingUpdateRequest;
use App\Models\SeoSetting;
use App\Services\AdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SeoSettingController extends Controller
{
    public function edit(): View
    {
        $seo = SeoSetting::query()->first() ?? new SeoSetting;

        return view('admin.seo.edit', compact('seo'));
    }

    public function update(
        SeoSettingUpdateRequest $request,
        AdminAuditService $audit,
    ): RedirectResponse {
        $seo = SeoSetting::query()->first() ?? new SeoSetting;
        $before = $seo->exists ? $seo->toArray() : null;
        $data = $request->safe()->except('og_image');
        $oldImage = null;

        if ($request->hasFile('og_image')) {
            $oldImage = $seo->og_image_path;
            $data['og_image_path'] = $request->file('og_image')->store('seo', 'public');
        }

        $seo->fill($data)->save();

        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        Cache::forget('seo:home');
        Cache::forget('seo:allow-indexing');
        $audit->record($request, 'seo.updated', $seo, $before, $seo->fresh()->toArray());

        return back()->with('status', 'تم حفظ إعدادات SEO.');
    }
}
