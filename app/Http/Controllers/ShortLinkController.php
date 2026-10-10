<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShortLinkRequest;
use App\Http\Requests\UpdateShortLinkRequest;
use App\Http\Requests\UpdateShortLinkSlugRequest;
use App\Models\ShortLink;
use App\Services\ShortLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ShortLinkController extends Controller
{
    public function store(StoreShortLinkRequest $request, ShortLinkService $service): JsonResponse|RedirectResponse
    {
        $shortLink = $service->create($request->user(), $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم إنشاء الرابط المختصر بنجاح.',
                'data' => $this->resultData($shortLink),
            ], 201);
        }

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

    public function updateSlug(
        UpdateShortLinkSlugRequest $request,
        ShortLink $shortLink,
        ShortLinkService $service,
    ): JsonResponse|RedirectResponse {
        $updatedShortLink = $service->updateSlug($shortLink, $request->validated());

        if (! $updatedShortLink) {
            $message = 'تم تعديل الرابط من جلسة أخرى. حدّث الصفحة ثم أعد المحاولة.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message, 'errors' => ['version' => [$message]]], 409);
            }

            return back()->withErrors(['version' => $message]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم حفظ المقطع الجديد بنجاح.',
                'data' => $this->resultData($updatedShortLink),
            ]);
        }

        return redirect()
            ->route('tools.short-url', ['short_link' => $updatedShortLink])
            ->with('status', 'تم حفظ المقطع الجديد بنجاح.');
    }

    public function destroy(ShortLink $shortLink): RedirectResponse
    {
        Gate::authorize('delete', $shortLink);
        $shortLink->delete();

        return redirect()
            ->route('dashboard', ['section' => 'links'])
            ->with('status', 'تم حذف الرابط مع إبقاء مقطعه محجوزًا.');
    }

    /** @return array<string, int|string> */
    private function resultData(ShortLink $shortLink): array
    {
        return [
            'id' => $shortLink->getKey(),
            'slug' => $shortLink->slug,
            'short_url' => route('short-links.redirect', $shortLink->slug),
            'qr_url' => route('short-links.qr', $shortLink),
            'update_slug_url' => route('short-links.slug.update', $shortLink),
            'management_url' => route('user.short-links.show', $shortLink),
            'version' => $shortLink->version,
            'filename' => 'muallem-'.$shortLink->slug,
        ];
    }
}
