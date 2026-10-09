<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShortLinkRequest;
use App\Http\Requests\UpdateShortLinkRequest;
use App\Models\ShortLink;
use App\Services\ShortLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ShortLinkController extends Controller
{
    public function store(StoreShortLinkRequest $request, ShortLinkService $service): RedirectResponse
    {
        $shortLink = $service->create($request->user(), $request->validated());

        return redirect()
            ->route('tools.short-url', ['short_link' => $shortLink])
            ->with('status', 'تم إنشاء الرابط المختصر بنجاح.');
    }

    public function update(
        UpdateShortLinkRequest $request,
        ShortLink $shortLink,
        ShortLinkService $service,
    ): RedirectResponse {
        $updatedShortLink = $service->update($shortLink, $request->validated());

        if (! $updatedShortLink) {
            return back()
                ->withInput()
                ->withErrors(['version' => 'تم تعديل الرابط من جلسة أخرى. حدّث الصفحة ثم أعد المحاولة.']);
        }

        return redirect()
            ->route('user.short-links.show', $updatedShortLink)
            ->with('status', 'تم تحديث الرابط المختصر.');
    }

    public function destroy(ShortLink $shortLink): RedirectResponse
    {
        Gate::authorize('delete', $shortLink);
        $shortLink->delete();

        return redirect()
            ->route('dashboard', ['section' => 'links'])
            ->with('status', 'تم حذف الرابط مع إبقاء مقطعه محجوزًا.');
    }
}
