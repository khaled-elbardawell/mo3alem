@props(['link' => [], 'index' => 0, 'platforms' => []])

@php
    $selectedPlatform = App\FooterLinkPlatform::tryFrom($link['platform'] ?? '') ?? App\FooterLinkPlatform::Website;
@endphp

<article class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-violet-200 hover:shadow-md"
    data-footer-link-row>
    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-4">
        <div class="flex min-w-0 items-center gap-3">
            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-violet-100 text-lg text-violet-700">
                <i class="{{ $selectedPlatform->iconClass() }}" data-footer-link-icon aria-hidden="true"></i>
            </span>
            <div class="min-w-0">
                <p class="text-xs font-bold text-slate-400">الرابط <span data-footer-link-number>{{ is_numeric($index) ? ((int) $index) + 1 : '' }}</span></p>
                <p class="truncate font-black text-slate-800" data-footer-link-title>{{ $link['label'] ?? 'رابط جديد' }}</p>
            </div>
        </div>
        <div class="flex shrink-0 items-center gap-1">
            <button class="grid size-9 cursor-pointer place-items-center rounded-lg text-slate-500 hover:bg-violet-50 hover:text-violet-700 disabled:cursor-not-allowed disabled:opacity-30"
                type="button" data-footer-link-action="up" aria-label="تحريك الرابط للأعلى">
                <i class="fa-solid fa-arrow-up"></i>
            </button>
            <button class="grid size-9 cursor-pointer place-items-center rounded-lg text-slate-500 hover:bg-violet-50 hover:text-violet-700 disabled:cursor-not-allowed disabled:opacity-30"
                type="button" data-footer-link-action="down" aria-label="تحريك الرابط للأسفل">
                <i class="fa-solid fa-arrow-down"></i>
            </button>
            <button class="grid size-9 cursor-pointer place-items-center rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700"
                type="button" data-footer-link-action="remove" aria-label="حذف الرابط">
                <i class="fa-regular fa-trash-can"></i>
            </button>
        </div>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <label class="grid gap-2 text-sm font-bold text-slate-700">
            المنصة والأيقونة
            <select class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                name="footer_links[{{ $index }}][platform]" data-footer-link-field="platform">
                @foreach ($platforms as $platform)
                    <option value="{{ $platform->value }}" data-icon="{{ $platform->iconClass() }}" @selected($selectedPlatform === $platform)>{{ $platform->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-2 text-sm font-bold text-slate-700">
            الاسم التعريفي
            <input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                name="footer_links[{{ $index }}][label]" value="{{ $link['label'] ?? '' }}" maxlength="80"
                placeholder="مثال: حسابنا على إنستغرام" required data-footer-link-field="label">
        </label>
        <label class="grid gap-2 text-sm font-bold text-slate-700 lg:col-span-2">
            الرابط
            <input class="min-h-12 rounded-xl border border-slate-200 px-4 text-left outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                dir="ltr" name="footer_links[{{ $index }}][url]" value="{{ $link['url'] ?? '' }}" maxlength="2048"
                placeholder="https://example.com/profile" required data-footer-link-field="url">
            <span class="text-xs font-medium text-slate-400">يُقبل رابط كامل آمن أو مسار داخلي يبدأ بـ / أو #.</span>
        </label>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2">
        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50/70 p-3 text-sm font-bold text-emerald-900">
            <input type="hidden" name="footer_links[{{ $index }}][is_active]" value="0" data-footer-link-field-name="is_active">
            <input class="size-5 accent-emerald-600" type="checkbox" name="footer_links[{{ $index }}][is_active]" value="1"
                @checked((bool) ($link['is_active'] ?? true)) data-footer-link-field="is_active">
            إظهار الرابط في الفوتر
        </label>
        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-sky-100 bg-sky-50/70 p-3 text-sm font-bold text-sky-900">
            <input type="hidden" name="footer_links[{{ $index }}][open_in_new_tab]" value="0" data-footer-link-field-name="open_in_new_tab">
            <input class="size-5 accent-sky-600" type="checkbox" name="footer_links[{{ $index }}][open_in_new_tab]" value="1"
                @checked((bool) ($link['open_in_new_tab'] ?? true)) data-footer-link-field="open_in_new_tab">
            فتح في نافذة جديدة
        </label>
    </div>
</article>
