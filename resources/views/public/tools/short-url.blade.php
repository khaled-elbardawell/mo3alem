@extends('layouts.public', ['activeNavigation' => 'tools'])

@section('content')
    <x-public.tool-page-header
        title="أداة اختصار الروابط"
        description="أنشئ رابطًا قصيرًا بمقطع مخصص، نزّل رمز QR، وتابع الزيارات من حسابك."
        current="اختصار الروابط"
        icon="fa-link" />

    @if($campaigns['top'])
        <section class="mx-auto w-[min(calc(100%_-_2rem),1040px)] py-6">
            <x-public.advertisement :campaign="$campaigns['top']" />
        </section>
    @endif

    <section class="mx-auto w-[min(calc(100%_-_2rem),1280px)] py-8 sm:py-12">
        <div @class(['grid items-start gap-6', 'lg:grid-cols-[minmax(0,1fr)_260px]' => $campaigns['side']])>
            <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,.85fr)]">
                <div class="rounded-3xl border border-violet-100 bg-white p-5 shadow-[0_20px_60px_rgba(76,29,149,0.08)] sm:p-7">
                    <div class="flex items-start gap-4">
                        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-violet-100 text-xl text-violet-700">
                            <i class="fa-solid fa-link" aria-hidden="true"></i>
                        </span>
                        <div>
                            <h2 class="text-2xl font-black text-[#111a35]">اختصر رابطك</h2>
                            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">الصق الرابط الطويل، واختر مقطعًا واضحًا أو اتركه فارغًا لتوليده تلقائيًا.</p>
                        </div>
                    </div>

                    @guest
                        <div class="mt-7 rounded-2xl border border-violet-200 bg-violet-50 p-5 text-center">
                            <h3 class="text-lg font-black text-violet-900">سجّل الدخول لإنشاء رابط مختصر</h3>
                            <p class="mt-2 text-sm leading-6 text-violet-700">حسابك يحفظ الروابط ويمنحك التعديل والإحصائيات وتنزيل QR.</p>
                            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                <a class="rounded-xl bg-violet-700 px-5 py-3 font-black text-white hover:bg-violet-800" href="{{ route('tools.short-url.auth', 'login') }}">تسجيل الدخول</a>
                                <a class="rounded-xl border border-violet-200 bg-white px-5 py-3 font-black text-violet-800 hover:bg-violet-100" href="{{ route('tools.short-url.auth', 'register') }}">إنشاء حساب</a>
                            </div>
                        </div>
                    @else
                        @unless(auth()->user()->hasVerifiedEmail())
                            <div class="mt-7 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
                                <p class="font-bold">فعّل بريدك أولًا حتى تتمكن من إنشاء الروابط وإدارتها.</p>
                                <a class="mt-3 inline-flex rounded-xl bg-amber-600 px-4 py-2 font-black text-white hover:bg-amber-700" href="{{ route('verification.notice') }}">تفعيل البريد</a>
                            </div>
                        @else
                            <form class="mt-7 grid gap-5" method="POST" action="{{ route('short-links.store') }}">
                                @csrf
                                <label class="grid gap-2 font-bold text-slate-800">
                                    <span>الرابط الطويل <span class="text-red-600">*</span></span>
                                    <input class="min-h-13 rounded-xl border border-slate-200 px-4 text-left focus:border-violet-500 focus:outline-none focus:ring-3 focus:ring-violet-100" type="url" name="destination_url" value="{{ old('destination_url') }}" maxlength="2048" dir="ltr" placeholder="https://example.com/long-link" required>
                                    @error('destination_url')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
                                </label>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <label class="grid gap-2 font-bold text-slate-800">
                                        <span>اسم داخلي <span class="font-medium text-slate-400">(اختياري)</span></span>
                                        <input class="min-h-13 rounded-xl border border-slate-200 px-4 focus:border-violet-500 focus:outline-none focus:ring-3 focus:ring-violet-100" type="text" name="title" value="{{ old('title') }}" maxlength="120" placeholder="مثال: واجب العلوم">
                                        @error('title')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
                                    </label>
                                    <label class="grid gap-2 font-bold text-slate-800">
                                        <span>المقطع المخصص <span class="font-medium text-slate-400">(اختياري)</span></span>
                                        <span class="flex min-h-13 overflow-hidden rounded-xl border border-slate-200 focus-within:border-violet-500 focus-within:ring-3 focus-within:ring-violet-100" dir="ltr">
                                            <span class="grid place-items-center border-r border-slate-200 bg-slate-50 px-3 text-sm text-slate-500">/short/</span>
                                            <input class="min-w-0 flex-1 px-3 outline-none" type="text" name="custom_alias" value="{{ old('custom_alias') }}" minlength="4" maxlength="50" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" placeholder="science-class">
                                        </span>
                                        @error('custom_alias')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
                                    </label>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-end">
                                    <label class="grid gap-2 font-bold text-slate-800">
                                        <span>تاريخ الانتهاء <span class="font-medium text-slate-400">(اختياري)</span></span>
                                        <input class="min-h-13 rounded-xl border border-slate-200 px-4 focus:border-violet-500 focus:outline-none focus:ring-3 focus:ring-violet-100" type="datetime-local" name="expires_at" value="{{ old('expires_at') }}">
                                        @error('expires_at')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
                                    </label>
                                    <label class="flex min-h-13 items-center gap-3 rounded-xl border border-slate-200 px-4 font-bold text-slate-700">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="size-5 accent-violet-700" type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                                        تفعيل الرابط فورًا
                                    </label>
                                </div>

                                <button class="inline-flex min-h-13 items-center justify-center gap-2 rounded-xl bg-linear-to-l from-violet-700 to-indigo-700 px-6 font-black text-white shadow-lg shadow-violet-900/15 hover:from-violet-800 hover:to-indigo-800" type="submit">
                                    <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>
                                    إنشاء الرابط المختصر
                                </button>
                            </form>
                        @endunless
                    @endguest
                </div>

                <aside class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                    @if($shortLink)
                        @php($shortUrl = route('short-links.redirect', $shortLink->slug))
                        <div data-short-link-tools data-short-url="{{ $shortUrl }}" data-qr-url="{{ route('short-links.qr', $shortLink) }}" data-filename="muallem-{{ $shortLink->slug }}">
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-black text-emerald-700">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                الرابط جاهز
                            </span>
                            <h2 class="mt-4 text-xl font-black text-[#111a35]">{{ $shortLink->title }}</h2>
                            <input class="mt-3 w-full rounded-xl border border-violet-200 bg-violet-50 px-4 py-3 text-left font-bold text-violet-800" value="{{ $shortUrl }}" dir="ltr" readonly>
                            <button class="mt-3 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-violet-700 px-4 font-black text-white hover:bg-violet-800" type="button" data-copy-short-link>
                                <i class="fa-regular fa-copy" aria-hidden="true"></i> نسخ الرابط
                            </button>

                            <div class="mt-6 grid place-items-center rounded-2xl bg-slate-50 p-5">
                                <img class="size-52 max-w-full" src="{{ route('short-links.qr', $shortLink) }}" alt="رمز QR للرابط المختصر {{ $shortLink->title }}">
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <button class="rounded-xl border border-violet-200 px-3 py-2.5 font-bold text-violet-800 hover:bg-violet-50 disabled:opacity-50" type="button" data-download-qr="png">تنزيل PNG</button>
                                <button class="rounded-xl border border-violet-200 px-3 py-2.5 font-bold text-violet-800 hover:bg-violet-50 disabled:opacity-50" type="button" data-download-qr="svg">تنزيل SVG</button>
                            </div>
                            <a class="mt-3 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 font-bold text-white hover:bg-slate-800" href="{{ route('user.short-links.show', $shortLink) }}">
                                <i class="fa-solid fa-chart-column" aria-hidden="true"></i> الإدارة والإحصائيات
                            </a>
                            <p class="mt-3 min-h-5 text-sm font-bold text-emerald-700" data-short-link-status aria-live="polite"></p>
                        </div>
                    @else
                        <div class="grid min-h-96 place-items-center text-center">
                            <div>
                                <span class="mx-auto grid size-20 place-items-center rounded-3xl bg-violet-50 text-3xl text-violet-300">
                                    <i class="fa-solid fa-qrcode" aria-hidden="true"></i>
                                </span>
                                <h2 class="mt-5 text-xl font-black text-slate-800">سيظهر الرابط وQR هنا</h2>
                                <p class="mx-auto mt-2 max-w-sm text-sm font-medium leading-6 text-slate-500">بعد الإنشاء يمكنك النسخ والتنزيل والانتقال مباشرة إلى الإحصائيات.</p>
                            </div>
                        </div>
                    @endif
                </aside>
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
