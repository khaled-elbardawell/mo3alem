@extends('layouts.public', ['activeNavigation' => 'tools'])

@section('content')
    <x-public.tool-page-header
        title="أداة اختصار الروابط"
        description="حوّل أي رابط طويل إلى رابط قصير سهل المشاركة، مع مقطع مخصص ورمز QR جاهز للتحميل."
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
        $managementUrl = $shortLink ? route('user.short-links.show', $shortLink) : '';
        $hasExpiry = old('expires_at') !== null && old('expires_at') !== '';
    @endphp

    <section class="mx-auto w-[min(calc(100%_-_2rem),1180px)] py-8 sm:py-12">
        <div @class(['grid items-start gap-6', 'lg:grid-cols-[minmax(0,1fr)_260px]' => $campaigns['side']])>
            <div>
                <div class="overflow-hidden rounded-[2rem] border border-violet-100 bg-white shadow-[0_24px_70px_rgba(76,29,149,0.1)]">
                    <div class="border-b border-slate-100 bg-linear-to-l from-violet-50 via-white to-indigo-50 px-5 py-8 text-center sm:px-10 sm:py-10">
                        <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-violet-700 text-2xl text-white shadow-lg shadow-violet-700/20">
                            <i class="fa-solid fa-link" aria-hidden="true"></i>
                        </span>
                        <h2 class="mt-4 text-2xl font-black text-[#111a35] sm:text-3xl">أنشئ رابطًا قصيرًا</h2>
                        <p class="mx-auto mt-2 max-w-xl text-sm font-medium leading-7 text-slate-500 sm:text-base">ألصق الرابط الطويل، وسنجهّز لك رابطًا قصيرًا وQR خلال لحظات.</p>
                    </div>

                    <div class="p-5 sm:p-8 lg:p-10">
                        @guest
                            <div class="mx-auto max-w-2xl rounded-2xl border border-violet-200 bg-violet-50 p-5 text-center sm:p-7">
                                <h3 class="text-lg font-black text-violet-950">سجّل الدخول لإنشاء رابط مختصر</h3>
                                <p class="mt-2 text-sm leading-6 text-violet-700">حسابك يحفظ روابطك ويمنحك التعديل والإحصائيات وتنزيل QR.</p>
                                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                    <a class="rounded-xl bg-violet-700 px-5 py-3 font-black text-white transition hover:bg-violet-800" href="{{ route('tools.short-url.auth', 'login') }}">تسجيل الدخول</a>
                                    <a class="rounded-xl border border-violet-200 bg-white px-5 py-3 font-black text-violet-800 transition hover:bg-violet-100" href="{{ route('tools.short-url.auth', 'register') }}">إنشاء حساب</a>
                                </div>
                            </div>
                        @else
                            @unless(auth()->user()->hasVerifiedEmail())
                                <div class="mx-auto max-w-2xl rounded-2xl border border-amber-200 bg-amber-50 p-5 text-center text-amber-950">
                                    <p class="font-bold">فعّل بريدك أولًا حتى تتمكن من إنشاء الروابط وإدارتها.</p>
                                    <a class="mt-4 inline-flex rounded-xl bg-amber-600 px-5 py-3 font-black text-white transition hover:bg-amber-700" href="{{ route('verification.notice') }}">تفعيل البريد</a>
                                </div>
                            @else
                                <form class="mx-auto max-w-4xl" method="POST" action="{{ route('short-links.store') }}" data-short-link-create>
                                    @csrf
                                    <input type="hidden" name="is_active" value="1">

                                    <label class="sr-only" for="short-link-destination">الرابط الطويل</label>
                                    <div class="rounded-2xl border border-slate-200 bg-white p-2 shadow-sm transition focus-within:border-violet-500 focus-within:ring-4 focus-within:ring-violet-100 sm:flex sm:items-center sm:gap-2">
                                        <span class="hidden size-12 shrink-0 place-items-center text-lg text-slate-400 sm:grid">
                                            <i class="fa-solid fa-link" aria-hidden="true"></i>
                                        </span>
                                        <input id="short-link-destination" class="min-h-14 w-full min-w-0 px-3 text-left text-base font-semibold text-slate-900 outline-none placeholder:font-medium placeholder:text-slate-400" type="url" name="destination_url" value="{{ old('destination_url') }}" maxlength="2048" dir="ltr" inputmode="url" autocomplete="url" placeholder="ألصق الرابط الطويل هنا https://..." required>
                                        <button class="inline-flex min-h-14 w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-linear-to-l from-violet-700 to-indigo-700 px-7 font-black text-white shadow-lg shadow-violet-900/15 transition hover:from-violet-800 hover:to-indigo-800 disabled:cursor-wait disabled:opacity-70 sm:w-auto" type="submit" data-create-short-link-button>
                                            <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>
                                            <span data-button-label>إنشاء الرابط</span>
                                        </button>
                                    </div>
                                    @error('destination_url')
                                        <p class="mt-2 text-sm font-bold text-red-700">{{ $message }}</p>
                                    @enderror

                                    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                                        <label class="flex cursor-pointer items-center justify-between gap-4">
                                            <span>
                                                <span class="block font-black text-slate-800">تاريخ انتهاء للرابط</span>
                                                <span class="mt-1 block text-sm font-medium text-slate-500">اختياري — اتركه مغلقًا ليبقى الرابط مفتوحًا.</span>
                                            </span>
                                            <input class="peer sr-only" type="checkbox" data-expiry-toggle @checked($hasExpiry)>
                                            <span class="relative h-7 w-12 shrink-0 rounded-full bg-slate-300 transition peer-checked:bg-violet-700 peer-focus-visible:ring-4 peer-focus-visible:ring-violet-200 after:absolute after:start-1 after:top-1 after:size-5 after:rounded-full after:bg-white after:shadow-sm after:transition-all peer-checked:after:translate-x-5 rtl:peer-checked:after:-translate-x-5"></span>
                                        </label>
                                        <div class="mt-4" data-expiry-fields @if(! $hasExpiry) hidden @endif>
                                            <label class="grid gap-2 font-bold text-slate-800" for="short-link-expiry">
                                                <span>ينتهي الرابط في</span>
                                                <input id="short-link-expiry" class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-500 focus:ring-3 focus:ring-violet-100" type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" @disabled(! $hasExpiry) data-expiry-input>
                                            </label>
                                            @error('expires_at')
                                                <p class="mt-2 text-sm font-bold text-red-700">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mt-4 hidden rounded-2xl border px-4 py-3 text-sm font-bold" data-short-link-create-status role="alert" aria-live="assertive"></div>
                                </form>

                                <section class="mx-auto mt-8 max-w-4xl border-t border-slate-200 pt-8" data-short-link-result @if(! $shortLink) hidden @endif>
                                    <div
                                        data-short-link-tools
                                        data-short-url="{{ $shortUrl }}"
                                        data-qr-url="{{ $qrUrl }}"
                                        data-filename="{{ $shortLink ? 'muallem-'.$shortLink->slug : 'muallem-short-link' }}">
                                        <div class="rounded-3xl border border-emerald-200 bg-emerald-50/60 p-5 sm:p-7">
                                            <div class="flex items-center gap-3">
                                                <span class="grid size-10 shrink-0 place-items-center rounded-full bg-emerald-600 text-white">
                                                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                                                </span>
                                                <div>
                                                    <h3 class="text-lg font-black text-emerald-950">رابطك المختصر جاهز</h3>
                                                    <p class="text-sm font-medium text-emerald-700">انسخه مباشرة أو خصّص المقطع كما تريد.</p>
                                                </div>
                                            </div>

                                            <div class="mt-5 flex flex-col gap-2 sm:flex-row">
                                                <input class="min-h-13 min-w-0 flex-1 rounded-xl border border-emerald-200 bg-white px-4 text-left font-bold text-violet-800 outline-none" value="{{ $shortUrl }}" dir="ltr" readonly data-short-url-output aria-label="الرابط المختصر">
                                                <button class="inline-flex min-h-13 items-center justify-center gap-2 rounded-xl bg-violet-700 px-6 font-black text-white transition hover:bg-violet-800" type="button" data-copy-short-link>
                                                    <i class="fa-regular fa-copy" aria-hidden="true"></i>
                                                    نسخ
                                                </button>
                                            </div>
                                        </div>

                                        <form class="mt-5 rounded-2xl border border-slate-200 p-5" method="POST" action="{{ $updateSlugUrl }}" data-short-link-slug-form novalidate>
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="version" value="{{ $shortLink?->version }}" data-short-link-version>
                                            <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                                                <label class="grid min-w-0 flex-1 gap-2 font-bold text-slate-800" for="short-link-slug">
                                                    <span>خصّص المقطع</span>
                                                    <span class="flex min-h-13 overflow-hidden rounded-xl border border-slate-200 bg-white focus-within:border-violet-500 focus-within:ring-3 focus-within:ring-violet-100" dir="ltr">
                                                        <span class="grid shrink-0 place-items-center border-r border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-500">/short/</span>
                                                        <input id="short-link-slug" class="min-w-0 flex-1 px-3 font-bold text-slate-900 outline-none" type="text" name="slug" value="{{ $shortLink?->slug }}" maxlength="50" autocomplete="off" autocapitalize="none" spellcheck="false" required data-short-link-slug>
                                                    </span>
                                                </label>
                                                <button class="inline-flex min-h-13 shrink-0 items-center justify-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-6 font-black text-violet-800 transition hover:bg-violet-100 disabled:cursor-wait disabled:opacity-60" type="submit" data-save-short-link-slug>
                                                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                                                    حفظ المقطع
                                                </button>
                                            </div>
                                            <p class="mt-2 text-xs font-medium text-slate-500">أحرف إنجليزية صغيرة وأرقام وشرطة فقط، من 4 إلى 50 حرفًا.</p>
                                        </form>

                                        <div class="mt-5 hidden rounded-2xl border px-4 py-3 text-sm font-bold" data-short-link-status role="status" aria-live="polite"></div>

                                        <div class="mt-7 grid gap-6 rounded-3xl bg-slate-50 p-5 sm:p-7 md:grid-cols-[220px_minmax(0,1fr)] md:items-center">
                                            <div class="grid place-items-center rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                                <img class="aspect-square w-full max-w-48" @if($shortLink) src="{{ $qrUrl }}" @endif alt="رمز QR للرابط المختصر" data-short-link-qr>
                                            </div>
                                            <div>
                                                <h3 class="text-xl font-black text-slate-900">رمز QR جاهز</h3>
                                                <p class="mt-2 text-sm font-medium leading-6 text-slate-500">نزّله وشاركه في أوراق العمل أو اعرضه للطلاب مباشرة.</p>
                                                <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                                                    <button class="min-h-11 rounded-xl border border-violet-200 bg-white px-5 font-bold text-violet-800 transition hover:bg-violet-50 disabled:opacity-50" type="button" data-download-qr="png">تنزيل PNG</button>
                                                    <button class="min-h-11 rounded-xl border border-violet-200 bg-white px-5 font-bold text-violet-800 transition hover:bg-violet-50 disabled:opacity-50" type="button" data-download-qr="svg">تنزيل SVG</button>
                                                </div>
                                                <a class="mt-3 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 font-bold text-white transition hover:bg-slate-800" href="{{ $managementUrl ?: '#' }}" @if(! $shortLink) hidden @endif data-short-link-management>
                                                    <i class="fa-solid fa-chart-column" aria-hidden="true"></i>
                                                    الإدارة والإحصائيات
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            @endunless
                        @endguest
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
