@extends('layouts.public', ['activeNavigation' => 'tools'])

@section('content')
    <x-public.tool-page-header
        title="أداة اختصار الروابط"
        description="اختصر رابطك خلال ثوانٍ، خصّص عنوانه عند الحاجة، وأنشئ له رمز QR جاهزًا للمشاركة."
        current="اختصار الروابط"
        icon="fa-link" />

    @if($campaigns['top'])
        <section class="mx-auto w-[min(calc(100%_-_2rem),1040px)] py-6">
            <x-public.advertisement :campaign="$campaigns['top']" />
        </section>
    @endif

    @php
        $shortUrl = $shortLink ? route('short-links.redirect', $shortLink->slug) : '';
        $qrUrl = $shortLink ? route('short-links.qr', $shortLink) : '';
        $updateSlugUrl = $shortLink ? route('short-links.slug.update', $shortLink) : '';
        $updateQrDesignUrl = $shortLink ? route('short-links.qr-design.update', $shortLink) : '';
        $managementUrl = $shortLink ? route('user.short-links.show', $shortLink) : '';
        $shortUrlPrefix = str_replace('__slug__', '', route('short-links.redirect', '__slug__'));
        $hasExpiry = filled(old('expires_at'));
        $creationMode = old('creation_mode', filled(old('custom_alias')) ? 'custom' : 'automatic');
        $canCreate = auth()->check() && auth()->user()->hasVerifiedEmail();
        $qrDesign = $shortLink?->resolvedQrDesign() ?? \App\Models\ShortLink::DEFAULT_QR_DESIGN;
    @endphp

    <section class="mx-auto w-[min(calc(100%_-_2rem),1180px)] py-8 sm:py-12">
        <div @class(['grid items-start gap-6', 'lg:grid-cols-[minmax(0,1fr)_260px]' => $campaigns['side']])>
            <div>
                <div class="overflow-hidden rounded-[2rem] border border-violet-100 bg-white shadow-[0_24px_70px_rgba(76,29,149,0.1)]">
                    <div class="border-b border-slate-100 bg-linear-to-l from-violet-50 via-white to-indigo-50 px-5 py-7 text-center sm:px-10 sm:py-9">
                        <span class="mx-auto grid size-13 place-items-center rounded-2xl bg-violet-700 text-xl text-white shadow-lg shadow-violet-700/20">
                            <i class="fa-solid fa-link" aria-hidden="true"></i>
                        </span>
                        <h2 class="mt-4 text-2xl font-black text-[#111a35] sm:text-3xl">رابط أقصر في خطوة واحدة</h2>
                        <p class="mx-auto mt-2 max-w-xl text-sm font-medium leading-7 text-slate-500 sm:text-base">ألصق الرابط، اختر الطريقة التي تناسبك، وسننشئه وننسخه مباشرة.</p>
                    </div>

                    <div class="p-5 sm:p-8 lg:p-10" data-short-link-workspace>
                        @auth
                            @unless(auth()->user()->hasVerifiedEmail())
                                <div class="mx-auto max-w-2xl rounded-2xl border border-amber-200 bg-amber-50 p-5 text-center text-amber-950">
                                    <span class="mx-auto grid size-11 place-items-center rounded-full bg-amber-100 text-amber-700"><i class="fa-solid fa-envelope-circle-check" aria-hidden="true"></i></span>
                                    <p class="mt-3 font-black">فعّل بريدك أولًا حتى تتمكن من إنشاء الروابط وإدارتها.</p>
                                    <a class="mt-4 inline-flex min-h-11 items-center rounded-xl bg-amber-600 px-5 font-black text-white transition hover:bg-amber-700" href="{{ route('verification.notice') }}">تفعيل البريد</a>
                                </div>
                            @endunless
                        @endauth

                        @if(! auth()->check() || $canCreate)
                            <form
                                class="mx-auto max-w-4xl"
                                method="POST"
                                action="{{ route('short-links.store') }}"
                                data-short-link-create
                                data-can-create="{{ $canCreate ? 'true' : 'false' }}"
                                data-login-url="{{ route('tools.short-url.auth', 'login') }}"
                                data-draft-key="muallem-short-link-draft-v1"
                                @if($shortLink) hidden @endif>
                                @csrf
                                <input type="hidden" name="is_active" value="1">

                                @guest
                                    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-violet-100 bg-violet-50/70 p-4 text-sm font-bold leading-6 text-violet-900">
                                        <i class="fa-solid fa-circle-info mt-1 text-violet-600" aria-hidden="true"></i>
                                        <p>أدخل الرابط أولًا، وسنحفظه لك أثناء تسجيل الدخول حتى لا تعيد كتابته.</p>
                                    </div>
                                @endguest

                                <fieldset>
                                    <legend class="mb-3 text-sm font-black text-slate-800">كيف تريد إنشاء الرابط؟</legend>
                                    <div class="grid grid-cols-2 gap-2 rounded-2xl bg-slate-100 p-1.5" role="radiogroup" aria-label="طريقة إنشاء الرابط">
                                        <label class="cursor-pointer">
                                            <input class="peer sr-only" type="radio" name="creation_mode" value="automatic" @checked($creationMode === 'automatic') data-creation-mode>
                                            <span class="flex min-h-12 items-center justify-center gap-2 rounded-xl px-3 text-sm font-black text-slate-600 transition peer-checked:bg-white peer-checked:text-violet-800 peer-checked:shadow-sm peer-focus-visible:ring-4 peer-focus-visible:ring-violet-200">
                                                <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                                                تلقائي وسريع
                                            </span>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input class="peer sr-only" type="radio" name="creation_mode" value="custom" @checked($creationMode === 'custom') data-creation-mode>
                                            <span class="flex min-h-12 items-center justify-center gap-2 rounded-xl px-3 text-sm font-black text-slate-600 transition peer-checked:bg-white peer-checked:text-violet-800 peer-checked:shadow-sm peer-focus-visible:ring-4 peer-focus-visible:ring-violet-200">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                                رابط مخصص
                                            </span>
                                        </label>
                                    </div>
                                </fieldset>

                                <div class="mt-5 grid gap-4">
                                    <label class="grid gap-2" for="short-link-destination">
                                        <span class="text-sm font-black text-slate-800">الرابط الطويل</span>
                                        <span class="flex min-h-14 items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 shadow-sm transition focus-within:border-violet-500 focus-within:ring-4 focus-within:ring-violet-100">
                                            <i class="fa-solid fa-link shrink-0 text-slate-400" aria-hidden="true"></i>
                                            <input id="short-link-destination" class="min-h-12 w-full min-w-0 text-left text-base font-semibold text-slate-900 outline-none placeholder:font-medium placeholder:text-slate-400" type="url" name="destination_url" value="{{ old('destination_url') }}" maxlength="2048" dir="ltr" inputmode="url" autocomplete="url" placeholder="https://example.com/very-long-link" required data-short-link-destination>
                                        </span>
                                    </label>
                                    @error('destination_url')
                                        <p class="text-sm font-bold text-red-700">{{ $message }}</p>
                                    @enderror

                                    <div class="grid gap-2" data-custom-alias-fields @if($creationMode !== 'custom') hidden @endif>
                                        <label class="text-sm font-black text-slate-800" for="short-link-custom-alias">المقطع المخصص</label>
                                        <div class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition focus-within:border-violet-500 focus-within:ring-4 focus-within:ring-violet-100 sm:flex-row" dir="ltr">
                                            <span class="flex min-h-13 shrink-0 items-center border-b border-slate-100 bg-slate-50 px-4 text-left text-sm font-bold text-slate-500 sm:border-r sm:border-b-0">{{ $shortUrlPrefix }}</span>
                                            <input id="short-link-custom-alias" class="min-h-13 min-w-0 flex-1 px-4 text-left font-black text-slate-900 outline-none placeholder:font-medium placeholder:text-slate-400" type="text" name="custom_alias" value="{{ old('custom_alias') }}" minlength="4" maxlength="50" autocomplete="off" autocapitalize="none" spellcheck="false" placeholder="science-class" data-custom-alias-input>
                                        </div>
                                        <p class="text-xs font-medium leading-5 text-slate-500">أحرف إنجليزية صغيرة وأرقام وشرطة فقط، مثل: <span class="font-bold" dir="ltr">science-class</span></p>
                                        @error('custom_alias')
                                            <p class="text-sm font-bold text-red-700">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <details class="group mt-5 rounded-2xl border border-slate-200 bg-slate-50/70" @if($hasExpiry) open @endif>
                                    <summary class="flex min-h-13 cursor-pointer list-none items-center justify-between gap-4 px-4 font-black text-slate-700 marker:hidden">
                                        <span class="flex items-center gap-2"><i class="fa-solid fa-sliders text-violet-600" aria-hidden="true"></i>خيارات إضافية</span>
                                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition group-open:rotate-180" aria-hidden="true"></i>
                                    </summary>
                                    <div class="border-t border-slate-200 p-4">
                                        <label class="flex cursor-pointer items-center justify-between gap-4">
                                            <span>
                                                <span class="block text-sm font-black text-slate-800">تاريخ انتهاء للرابط</span>
                                                <span class="mt-1 block text-xs font-medium text-slate-500">اتركه مغلقًا ليبقى الرابط متاحًا.</span>
                                            </span>
                                            <input class="peer sr-only" type="checkbox" data-expiry-toggle @checked($hasExpiry)>
                                            <span class="relative h-7 w-12 shrink-0 rounded-full bg-slate-300 transition peer-checked:bg-violet-700 peer-focus-visible:ring-4 peer-focus-visible:ring-violet-200 after:absolute after:start-1 after:top-1 after:size-5 after:rounded-full after:bg-white after:shadow-sm after:transition-all peer-checked:after:translate-x-5 rtl:peer-checked:after:-translate-x-5"></span>
                                        </label>
                                        <div class="mt-4" data-expiry-fields @if(! $hasExpiry) hidden @endif>
                                            <label class="grid gap-2 text-sm font-bold text-slate-800" for="short-link-expiry">
                                                ينتهي الرابط في
                                                <input id="short-link-expiry" class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-500 focus:ring-3 focus:ring-violet-100" type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" @disabled(! $hasExpiry) data-expiry-input>
                                            </label>
                                            @error('expires_at')
                                                <p class="mt-2 text-sm font-bold text-red-700">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </details>

                                <button class="mt-5 inline-flex min-h-14 w-full items-center justify-center gap-2 rounded-2xl bg-linear-to-l from-violet-700 to-indigo-700 px-7 font-black text-white shadow-lg shadow-violet-900/15 transition hover:from-violet-800 hover:to-indigo-800 disabled:cursor-wait disabled:opacity-70" type="submit" data-create-short-link-button>
                                    <i class="fa-solid {{ $canCreate ? 'fa-wand-magic-sparkles' : 'fa-right-to-bracket' }}" aria-hidden="true"></i>
                                    <span data-button-label>{{ $canCreate ? 'اختصر وانسخ' : 'تسجيل الدخول والمتابعة' }}</span>
                                </button>

                                @guest
                                    <p class="mt-3 text-center text-sm font-medium text-slate-500">مستخدم جديد؟ <a class="font-black text-violet-700 hover:text-violet-900" href="{{ route('tools.short-url.auth', 'register') }}" data-short-link-auth-link>أنشئ حسابًا مجانًا</a></p>
                                @endguest

                                <div class="mt-4 hidden rounded-2xl border px-4 py-3 text-sm font-bold" data-short-link-create-status role="alert" aria-live="assertive"></div>
                            </form>
                        @endif

                        @if($canCreate)
                            <section class="mx-auto max-w-4xl" data-short-link-result @if(! $shortLink) hidden @endif>
                                <div
                                    data-short-link-tools
                                    data-short-url="{{ $shortUrl }}"
                                    data-qr-url="{{ $qrUrl }}"
                                    data-update-qr-design-url="{{ $updateQrDesignUrl }}"
                                    data-qr-preview-url="{{ route('tools.qr.render') }}"
                                    data-filename="{{ $shortLink ? 'muallem-'.$shortLink->slug : 'muallem-short-link' }}">
                                    <div class="rounded-3xl border border-emerald-200 bg-emerald-50/60 p-5 sm:p-7">
                                        <div class="flex items-start gap-3">
                                            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-emerald-600 text-white"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                            <div>
                                                <h3 class="text-lg font-black text-emerald-950">رابطك جاهز</h3>
                                                <p class="text-sm font-medium text-emerald-700">تم إنشاؤه، ويمكنك نسخه أو تخصيصه من هنا.</p>
                                            </div>
                                        </div>

                                        <div class="mt-5 flex flex-col gap-2 sm:flex-row">
                                            <label class="sr-only" for="short-url-output">الرابط المختصر</label>
                                            <input id="short-url-output" class="min-h-13 min-w-0 flex-1 rounded-xl border border-emerald-200 bg-white px-4 text-left font-black text-violet-800 outline-none" value="{{ $shortUrl }}" dir="ltr" readonly data-short-url-output>
                                            <button class="inline-flex min-h-13 shrink-0 items-center justify-center gap-2 rounded-xl bg-violet-700 px-6 font-black text-white transition hover:bg-violet-800" type="button" data-copy-short-link><i class="fa-regular fa-copy" aria-hidden="true"></i>نسخ</button>
                                        </div>

                                        <div class="mt-4 grid gap-2 sm:grid-cols-3">
                                            <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 text-sm font-black text-slate-700 transition hover:border-violet-300 hover:text-violet-800" type="button" data-toggle-slug-editor aria-expanded="false"><i class="fa-solid fa-pen" aria-hidden="true"></i>تعديل الرابط</button>
                                            <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 text-sm font-black text-slate-700 transition hover:border-violet-300 hover:text-violet-800" type="button" data-toggle-qr-customizer aria-expanded="false"><i class="fa-solid fa-qrcode" aria-hidden="true"></i>تخصيص QR</button>
                                            <a class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 text-sm font-black text-slate-700 transition hover:border-violet-300 hover:text-violet-800" href="{{ $managementUrl ?: '#' }}" @if(! $shortLink) hidden @endif data-short-link-management><i class="fa-solid fa-chart-column" aria-hidden="true"></i>الإدارة والإحصائيات</a>
                                        </div>

                                        <button class="mt-4 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-dashed border-emerald-300 px-4 text-sm font-black text-emerald-800 transition hover:border-violet-400 hover:bg-white hover:text-violet-800" type="button" data-create-another-short-link>
                                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                            اختصار رابط جديد
                                        </button>

                                        <form class="fixed inset-0 z-100 grid items-end bg-slate-950/45 sm:justify-items-end" method="POST" action="{{ $updateSlugUrl }}" data-short-link-slug-form role="dialog" aria-modal="true" aria-labelledby="short-link-slug-title" hidden novalidate>
                                            @csrf
                                            @method('PATCH')
                                            <div class="max-h-[85vh] w-full overflow-y-auto rounded-t-3xl bg-white p-5 shadow-2xl sm:h-full sm:max-h-none sm:w-[430px] sm:rounded-none sm:p-7">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div>
                                                        <h3 class="text-xl font-black text-slate-900" id="short-link-slug-title">تعديل الرابط</h3>
                                                        <p class="mt-1 text-sm font-medium text-slate-500">اختر مقطعًا قصيرًا وسهل التذكّر.</p>
                                                    </div>
                                                    <button class="grid size-10 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-500 hover:text-violet-700" type="button" data-close-slug-editor aria-label="إغلاق تعديل الرابط"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                                                </div>
                                                <input type="hidden" name="version" value="{{ $shortLink?->version }}" data-short-link-version>
                                                <label class="mt-7 block text-sm font-black text-slate-800" for="short-link-slug">المقطع الجديد</label>
                                                <div class="mt-2 flex min-w-0 flex-col overflow-hidden rounded-xl border border-slate-200 focus-within:border-violet-500 focus-within:ring-3 focus-within:ring-violet-100" dir="ltr">
                                                    <span class="flex min-h-12 items-center border-b border-slate-100 bg-slate-50 px-3 text-sm font-bold text-slate-500">{{ $shortUrlPrefix }}</span>
                                                    <input id="short-link-slug" class="min-h-12 min-w-0 px-3 text-left font-black text-slate-900 outline-none" type="text" name="slug" value="{{ $shortLink?->slug }}" maxlength="50" autocomplete="off" autocapitalize="none" spellcheck="false" required data-short-link-slug>
                                                </div>
                                                <button class="mt-5 min-h-12 w-full rounded-xl bg-slate-900 px-5 font-black text-white transition hover:bg-slate-800 disabled:opacity-60" type="submit" data-save-short-link-slug>حفظ ونسخ</button>
                                            </div>
                                        </form>

                                        <div class="mt-4 hidden rounded-2xl border px-4 py-3 text-sm font-bold" data-short-link-status role="status" aria-live="polite"></div>
                                    </div>

                                    <section class="fixed inset-0 z-100 flex items-end bg-slate-950/45 sm:justify-end" data-qr-customizer role="dialog" aria-modal="true" aria-labelledby="short-link-qr-title" hidden>
                                        <div class="max-h-[92vh] w-full overflow-y-auto rounded-t-3xl bg-slate-50 p-5 shadow-2xl sm:h-full sm:max-h-none sm:w-[min(780px,92vw)] sm:rounded-none sm:p-7">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <h3 class="text-xl font-black text-slate-900" id="short-link-qr-title">صمّم رمز QR</h3>
                                                <p class="mt-1 text-sm font-medium text-slate-500">اختر الشكل والألوان، وشاهد النتيجة مباشرة.</p>
                                            </div>
                                            <button class="grid size-10 shrink-0 place-items-center rounded-full bg-white text-slate-500 shadow-sm hover:text-violet-700" type="button" data-close-qr-customizer aria-label="إغلاق تخصيص QR"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                                        </div>

                                        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_260px] lg:items-start">
                                            <div class="grid gap-6">
                                                <fieldset>
                                                    <legend class="mb-3 text-sm font-black text-slate-800">نمط الرمز</legend>
                                                    <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="نمط رمز QR">
                                                        @foreach ([['value' => 'classic', 'label' => 'كلاسيكي', 'image' => 'مربع كلاسيكي.png'], ['value' => 'dots', 'label' => 'نقاط', 'image' => 'نقاط ودوائر.png'], ['value' => 'rounded', 'label' => 'مستدير', 'image' => 'مستديرة ناعم.png']] as $style)
                                                            <label class="cursor-pointer">
                                                                <input class="peer sr-only" type="radio" name="short_link_qr_style" value="{{ $style['value'] }}" @checked($qrDesign['style'] === $style['value']) data-qr-style>
                                                                <span class="grid min-h-30 place-items-center gap-2 rounded-2xl border border-slate-200 bg-white p-2 text-xs font-black text-slate-600 transition hover:border-violet-300 peer-checked:border-violet-500 peer-checked:bg-violet-50 peer-checked:text-violet-800 peer-checked:ring-3 peer-checked:ring-violet-100 peer-focus-visible:ring-3 peer-focus-visible:ring-violet-100">
                                                                    <img class="aspect-square w-full max-w-16 rounded-lg object-contain" src="{{ asset('assets/qr-shapes/'.$style['image']) }}" alt="مثال {{ $style['label'] }}" loading="lazy">
                                                                    {{ $style['label'] }}
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </fieldset>

                                                <fieldset>
                                                    <legend class="mb-3 text-sm font-black text-slate-800">الألوان</legend>
                                                    <div class="grid gap-3 sm:grid-cols-3">
                                                        @foreach ([['field' => 'foreground_color', 'label' => 'لون الرمز'], ['field' => 'eye_color', 'label' => 'لون الزوايا'], ['field' => 'background_color', 'label' => 'الخلفية']] as $color)
                                                            <label class="grid gap-2 text-xs font-black text-slate-600">
                                                                {{ $color['label'] }}
                                                                <span class="flex min-h-12 items-center gap-2 rounded-xl border border-slate-200 bg-white p-2">
                                                                    <input class="h-8 w-10 shrink-0 cursor-pointer rounded-lg border-0 bg-transparent p-0" type="color" value="{{ $qrDesign[$color['field']] }}" data-qr-color="{{ $color['field'] }}">
                                                                    <span class="truncate text-left font-mono text-[11px]" dir="ltr" data-qr-color-value="{{ $color['field'] }}">{{ strtoupper($qrDesign[$color['field']]) }}</span>
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    <p class="mt-3 hidden rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs font-bold leading-6 text-amber-900" data-qr-contrast-warning role="status">التباين منخفض وقد يصعب مسح الرمز. اختر لونًا أغمق أو خلفية أفتح.</p>
                                                </fieldset>
                                            </div>

                                            <div class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                                <div class="grid aspect-square place-items-center overflow-hidden rounded-xl bg-slate-50 p-3">
                                                    <img class="h-full w-full object-contain" @if($shortLink) src="{{ $qrUrl }}?version={{ $shortLink->version }}" @endif alt="معاينة رمز QR للرابط المختصر" data-short-link-qr>
                                                </div>
                                                <button class="min-h-11 rounded-xl bg-violet-700 px-4 text-sm font-black text-white transition hover:bg-violet-800 disabled:cursor-wait disabled:opacity-60" type="button" data-save-qr-design>حفظ التصميم</button>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <button class="min-h-10 rounded-xl border border-violet-200 bg-white px-3 text-sm font-bold text-violet-800 hover:bg-violet-50 disabled:opacity-50" type="button" data-download-qr="png">PNG</button>
                                                    <button class="min-h-10 rounded-xl border border-violet-200 bg-white px-3 text-sm font-bold text-violet-800 hover:bg-violet-50 disabled:opacity-50" type="button" data-download-qr="svg">SVG</button>
                                                </div>
                                                <p class="hidden rounded-xl border px-3 py-2 text-xs font-bold" data-qr-design-status role="status" aria-live="polite"></p>
                                            </div>
                                        </div>
                                        </div>
                                    </section>
                                </div>
                            </section>
                        @endif
                    </div>
                </div>
            </div>

            @if($campaigns['side'])
                <x-public.advertisement class="min-h-72 lg:h-full" :campaign="$campaigns['side']" placement="side" />
            @endif
        </div>
    </section>

    @if($campaigns['bottom'])
        <section class="mx-auto w-[min(calc(100%_-_2rem),1040px)] pb-10">
            <x-public.advertisement :campaign="$campaigns['bottom']" />
        </section>
    @endif
@endsection
