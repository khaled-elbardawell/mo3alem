@extends('layouts.public', ['activeNavigation' => 'qr'])

@section('title', 'إنشاء رمز QR احترافي')
@section('canonical', route('tools.qr'))

@section('content')
    <div
        class="min-h-[calc(100vh-5rem)] overflow-hidden bg-[radial-gradient(circle_at_12%_10%,rgba(124,58,237,0.1),transparent_26%),radial-gradient(circle_at_88%_18%,rgba(59,130,246,0.07),transparent_24%),#fbfbff] pb-16">
        <div id="qrAppConfig" hidden
            data-config="{{ json_encode($qrConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}">
        </div>

        <x-public.tool-page-header
            title="أداة إنشاء رمز QR"
            description="حوّل الروابط والنصوص وبيانات الشبكة إلى رمز QR مخصّص وجاهز للتنزيل والمشاركة."
            current="إنشاء رمز QR"
            icon="fa-qrcode"
        />

        @if ($campaigns['top'])
            <section class="mx-auto w-[min(calc(100%_-_2rem),1040px)] py-6" aria-label="إعلان علوي">
                <x-public.advertisement :campaign="$campaigns['top']" />
            </section>
        @endif

        <main @class([
            'mx-auto grid w-[min(calc(100%_-_2rem),1380px)] items-start gap-5 py-7',
            'lg:grid-cols-[minmax(0,1fr)_260px]' => $campaigns['side'],
        ]) id="qrBuilder">
            <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(340px,0.92fr)_minmax(420px,1.08fr)]">
                <section
                    class="order-2 min-w-0 rounded-3xl border border-violet-100 bg-white p-4 shadow-[0_18px_60px_rgba(49,46,129,0.09)] sm:p-6 xl:order-1"
                    aria-labelledby="qrSettingsTitle">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-black text-violet-700">خصّص الرمز</p>
                            <h2 class="mt-1 text-2xl font-black text-[#111a35]" id="qrSettingsTitle">إعدادات QR</h2>
                        </div>
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-violet-100 text-xl text-violet-700"><i
                                class="fa-solid fa-sliders" aria-hidden="true"></i></span>
                    </div>

                    <form class="mt-6 grid gap-6" id="qrForm" novalidate>
                        <fieldset class="grid gap-3">
                            <legend class="text-sm font-black text-slate-800">1. نوع المحتوى</legend>
                            <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="نوع محتوى الرمز">
                                @foreach ([['value' => 'url', 'label' => 'رابط', 'icon' => 'fa-link'], ['value' => 'text', 'label' => 'نص', 'icon' => 'fa-align-right'], ['value' => 'wifi', 'label' => 'Wi-Fi', 'icon' => 'fa-wifi']] as $type)
                                    <label class="group relative">
                                        <input class="peer sr-only" type="radio" name="content_type"
                                            value="{{ $type['value'] }}" @checked($type['value'] === 'url')>
                                        <span
                                            class="flex min-h-20 flex-col items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-2 text-sm font-black text-slate-600 transition peer-checked:border-violet-500 peer-checked:bg-violet-50 peer-checked:text-violet-800 peer-focus-visible:ring-4 peer-focus-visible:ring-violet-100">
                                            <i class="fa-solid {{ $type['icon'] }} text-lg" aria-hidden="true"></i>
                                            {{ $type['label'] }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="grid gap-2" data-content-panel="url">
                                <label class="text-sm font-black text-slate-700" for="qrUrl">الرابط</label>
                                <input
                                    class="min-h-12 rounded-xl border border-slate-200 px-4 text-left outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                                    id="qrUrl" type="url" dir="ltr" placeholder="https://example.com"
                                    autocomplete="url">
                            </div>
                            <div class="hidden gap-2" data-content-panel="text">
                                <label class="text-sm font-black text-slate-700" for="qrText">النص</label>
                                <textarea
                                    class="min-h-28 resize-y rounded-xl border border-slate-200 px-4 py-3 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                                    id="qrText" maxlength="2000" placeholder="اكتب النص الذي تريد مشاركته"></textarea>
                            </div>
                            <div class="hidden gap-3" data-content-panel="wifi">
                                <label class="grid gap-2 text-sm font-black text-slate-700">اسم الشبكة
                                    <input
                                        class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                                        id="qrWifiSsid" maxlength="100" autocomplete="off">
                                </label>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <label class="grid gap-2 text-sm font-black text-slate-700">نوع الحماية
                                        <select
                                            class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                                            id="qrWifiEncryption">
                                            <option value="WPA">WPA/WPA2</option>
                                            <option value="WEP">WEP</option>
                                            <option value="nopass">بدون كلمة مرور</option>
                                        </select>
                                    </label>
                                    <label class="grid gap-2 text-sm font-black text-slate-700">كلمة المرور
                                        <input
                                            class="min-h-12 rounded-xl border border-slate-200 px-4 text-left outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                                            id="qrWifiPassword" type="text" dir="ltr" maxlength="100"
                                            autocomplete="off">
                                    </label>
                                </div>
                                <label class="flex items-center gap-2 text-sm font-bold text-slate-600">
                                    <input class="size-4 accent-violet-700" id="qrWifiHidden" type="checkbox">
                                    شبكة مخفية
                                </label>
                            </div>
                        </fieldset>

                        <fieldset class="grid gap-3 border-t border-slate-100 pt-5">
                            <legend class="text-sm font-black text-slate-800">2. نمط الرمز</legend>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3" role="radiogroup" aria-label="نمط الرمز">
                                @foreach ([['value' => 'classic', 'label' => 'كلاسيكي', 'image' => 'مربع كلاسيكي.png'], ['value' => 'dots', 'label' => 'نقاط', 'image' => 'نقاط ودوائر.png'], ['value' => 'rounded', 'label' => 'مستديرة', 'image' => 'مستديرة ناعم.png']] as $style)
                                    <label class="cursor-pointer">
                                        <input class="peer sr-only" type="radio" name="qr_style"
                                            value="{{ $style['value'] }}" @checked($style['value'] === 'classic')>
                                        <span
                                            class="grid min-h-36 place-items-center gap-2 rounded-2xl border border-slate-200 bg-white p-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-violet-300 hover:bg-violet-50/40 peer-checked:border-violet-500 peer-checked:bg-violet-50 peer-checked:text-violet-800 peer-checked:ring-4 peer-checked:ring-violet-100 peer-focus-visible:ring-4 peer-focus-visible:ring-violet-100">
                                            <img class="aspect-square w-full max-w-20 rounded-lg object-contain"
                                                src="{{ asset('assets/qr-shapes/' . $style['image']) }}"
                                                alt="مثال نمط {{ $style['label'] }}" loading="lazy">
                                            <span>{{ $style['label'] }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset class="grid gap-3 border-t border-slate-100 pt-5">
                            <legend class="text-sm font-black text-slate-800">3. الألوان</legend>
                            <div class="grid gap-3 sm:grid-cols-3">
                                @foreach ([['id' => 'qrForegroundColor', 'label' => 'لون الرمز', 'value' => '#111827'], ['id' => 'qrEyeColor', 'label' => 'لون الزوايا', 'value' => '#6d28d9'], ['id' => 'qrBackgroundColor', 'label' => 'الخلفية', 'value' => '#ffffff']] as $color)
                                    <label class="grid gap-2 text-xs font-black text-slate-600" for="{{ $color['id'] }}">
                                        {{ $color['label'] }}
                                        <span
                                            class="flex min-h-12 items-center gap-2 rounded-xl border border-slate-200 bg-white p-2">
                                            <input class="h-8 w-10 shrink-0 rounded-lg border-0 bg-transparent p-0"
                                                id="{{ $color['id'] }}" type="color" value="{{ $color['value'] }}">
                                            <span class="truncate text-left font-mono text-[11px]" dir="ltr"
                                                data-color-value="{{ $color['id'] }}">{{ $color['value'] }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="hidden rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs font-bold leading-6 text-amber-900"
                                id="qrContrastWarning" role="status">
                                التباين منخفض وقد يصعب مسح الرمز. اختر لون رمز أغمق أو خلفية أفتح.
                            </p>
                        </fieldset>

                        <fieldset class="grid gap-3 border-t border-slate-100 pt-5">
                            <legend class="text-sm font-black text-slate-800">4. العنصر في المنتصف</legend>
                            <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="العنصر في منتصف الرمز">
                                @foreach ([['value' => 'none', 'label' => 'بدون', 'icon' => 'fa-ban'], ['value' => 'image', 'label' => 'صورة', 'icon' => 'fa-image'], ['value' => 'text', 'label' => 'نص', 'icon' => 'fa-font']] as $center)
                                    <label>
                                        <input class="peer sr-only" type="radio" name="center_type"
                                            value="{{ $center['value'] }}" @checked($center['value'] === 'none')>
                                        <span
                                            class="flex min-h-14 items-center justify-center gap-2 rounded-xl border border-slate-200 text-sm font-black text-slate-600 peer-checked:border-violet-500 peer-checked:bg-violet-50 peer-checked:text-violet-800 peer-focus-visible:ring-4 peer-focus-visible:ring-violet-100">
                                            <i class="fa-solid {{ $center['icon'] }}"
                                                aria-hidden="true"></i>{{ $center['label'] }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="hidden gap-2" data-center-panel="image">
                                <label
                                    class="flex min-h-12 items-center justify-center gap-2 rounded-xl border border-dashed border-violet-300 bg-violet-50 px-4 text-sm font-black text-violet-800"
                                    for="qrLogo">
                                    <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                                    اختر PNG أو JPG أو WebP
                                </label>
                                <input class="sr-only" id="qrLogo" type="file"
                                    accept="image/png,image/jpeg,image/webp">
                                <p class="text-xs font-bold text-slate-500" id="qrLogoName">حتى 1 ميجابايت، ونضع خلفه
                                    مساحة آمنة تلقائيًا.</p>
                            </div>
                            <div class="hidden gap-2" data-center-panel="text">
                                <label class="text-sm font-black text-slate-700" for="qrCenterText">نص قصير</label>
                                <input
                                    class="min-h-12 rounded-xl border border-slate-200 px-4 text-center text-lg font-black outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                                    id="qrCenterText" maxlength="10" placeholder="معلّم">
                            </div>
                        </fieldset>

                        <fieldset class="grid gap-3 border-t border-slate-100 pt-5">
                            <legend class="text-sm font-black text-slate-800">5. قالب الإطار</legend>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3" role="radiogroup"
                                aria-label="قالب الإطار">
                                <label class="cursor-pointer">
                                    <input class="peer sr-only" type="radio" name="qr_frame" value="none" checked>
                                    <span
                                        class="grid min-h-40 place-items-center gap-2 rounded-2xl border border-slate-200 bg-white p-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-violet-300 hover:bg-violet-50/40 peer-checked:border-violet-500 peer-checked:bg-violet-50 peer-checked:text-violet-800 peer-checked:ring-4 peer-checked:ring-violet-100 peer-focus-visible:ring-4 peer-focus-visible:ring-violet-100">
                                        <span
                                            class="grid aspect-square w-full max-w-24 place-items-center rounded-xl border border-dashed border-slate-200 bg-slate-50 text-2xl text-slate-400"
                                            aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
                                        <span>بدون قالب</span>
                                    </span>
                                </label>
                                @foreach (range(1, 11) as $templateNumber)
                                    <label class="cursor-pointer">
                                        <input class="peer sr-only" type="radio" name="qr_frame"
                                            value="template-{{ $templateNumber }}"
                                            data-template-url="{{ asset('assets/qr-templates/' . $templateNumber . '.png') }}">
                                        <span
                                            class="grid min-h-40 place-items-center gap-2 rounded-2xl border border-slate-200 bg-white p-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-violet-300 hover:bg-violet-50/40 peer-checked:border-violet-500 peer-checked:bg-violet-50 peer-checked:text-violet-800 peer-checked:ring-4 peer-checked:ring-violet-100 peer-focus-visible:ring-4 peer-focus-visible:ring-violet-100">
                                            <img class="aspect-square w-full max-w-24 rounded-xl object-contain"
                                                src="{{ asset('assets/qr-templates/' . $templateNumber . '.png') }}"
                                                alt="معاينة قالب {{ $templateNumber }}" loading="lazy">
                                            <span>قالب {{ $templateNumber }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div class="grid gap-3 border-t border-slate-100 pt-5 sm:grid-cols-[1fr_auto]">
                            <button
                                class="inline-flex min-h-13 items-center justify-center gap-2 rounded-xl bg-linear-to-l from-violet-700 to-indigo-700 px-6 font-black text-white shadow-lg shadow-violet-900/20 transition hover:-translate-y-0.5 disabled:cursor-wait disabled:opacity-60"
                                id="generateQrBtn" type="submit">
                                <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>
                                إنشاء رمز QR
                            </button>
                            <button
                                class="inline-flex min-h-13 items-center justify-center gap-2 rounded-xl border border-violet-200 bg-white px-5 font-black text-violet-800 hover:bg-violet-50 disabled:opacity-50"
                                id="saveQrBtn" type="button">
                                <i class="fa-regular fa-floppy-disk" aria-hidden="true"></i>
                                حفظ
                            </button>
                        </div>
                        <p class="min-h-5 text-sm font-bold text-slate-500" id="qrFormStatus" role="status"
                            aria-live="polite"></p>
                    </form>
                </section>

                <section class="order-1 min-w-0 xl:order-2" aria-labelledby="qrPreviewTitle">
                    <div
                        class="rounded-3xl border border-violet-100 bg-white p-4 shadow-[0_18px_60px_rgba(49,46,129,0.09)] sm:p-6 xl:sticky xl:top-25">
                        @auth
                            <nav class="mb-5 grid gap-2 sm:grid-cols-2" id="qrAuthenticatedActions"
                                aria-label="إجراءات رموز QR">
                                <a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white shadow-lg shadow-violet-900/20 transition hover:-translate-y-0.5 hover:bg-violet-800"
                                    id="qrMyCodesLink" href="{{ route('dashboard', ['section' => 'qr']) }}">
                                    <i class="fa-solid fa-qrcode" aria-hidden="true"></i>
                                    رموزي
                                </a>
                                <a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-violet-200 bg-white px-5 font-black text-violet-800 shadow-sm transition hover:-translate-y-0.5 hover:border-violet-300 hover:bg-violet-50"
                                    id="createNewQrLink" href="{{ route('tools.qr') }}">
                                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                    إنشاء رمز جديد
                                </a>
                            </nav>
                        @endauth
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="mt-1 text-2xl font-black text-[#111a35]" id="qrPreviewTitle">المعاينة</h2>
                            </div>
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600"
                                id="qrPreviewWaitingBadge"><i class="fa-regular fa-clock" aria-hidden="true"></i> بانتظار
                                المحتوى</span>
                            <span
                                class="hidden items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700"
                                id="qrPreviewReadyBadge"><i class="fa-solid fa-circle text-[7px]" aria-hidden="true"></i>
                                جاهز للمسح</span>
                        </div>

                        <div class="mt-5 grid min-h-[380px] place-items-center overflow-hidden rounded-3xl border border-violet-100 bg-[radial-gradient(circle_at_50%_45%,rgba(124,58,237,0.1),transparent_42%),#f8f7ff] p-4 sm:min-h-[500px] sm:p-8"
                            id="qrPreviewStage">
                            <div class="grid w-full max-w-[470px] place-items-center">
                                <div class="grid place-items-center gap-4 py-20 text-center text-slate-500"
                                    id="qrPreviewEmptyState">
                                    <span
                                        class="grid size-20 place-items-center rounded-3xl border border-dashed border-violet-200 bg-white/80 text-3xl text-violet-300"><i
                                            class="fa-solid fa-qrcode" aria-hidden="true"></i></span>
                                    <div class="grid gap-1">
                                        <span class="text-lg font-black text-slate-700">ابدأ بكتابة المحتوى</span>
                                        <span class="text-sm font-bold">لا يوجد شيء للمعاينة حتى الآن.</span>
                                    </div>
                                </div>
                                <img class="hidden h-auto w-full drop-shadow-[0_22px_35px_rgba(49,46,129,0.15)]"
                                    id="qrPreviewImage" alt="معاينة رمز QR المخصص">
                                <div class="hidden place-items-center gap-3 py-20 text-center text-slate-500"
                                    id="qrPreviewLoader">
                                    <i class="fa-solid fa-circle-notch animate-spin text-3xl text-violet-600"
                                        aria-hidden="true"></i>
                                    <span class="font-black">نجهّز المعاينة…</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 hidden grid-cols-2 gap-2 sm:grid-cols-3" id="qrDownloadActions">
                            <button
                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 text-sm font-black text-white hover:bg-slate-800"
                                id="downloadQrPng" type="button"><i class="fa-solid fa-image" aria-hidden="true"></i>
                                PNG</button>
                            <button
                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 hover:bg-slate-50"
                                id="downloadQrSvg" type="button"><i class="fa-solid fa-bezier-curve"
                                    aria-hidden="true"></i> SVG</button>
                            <button
                                class="col-span-2 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-violet-200 bg-white px-4 text-sm font-black text-violet-800 hover:bg-violet-50 sm:col-span-1"
                                id="copyQrImage" type="button"><i class="fa-regular fa-copy" aria-hidden="true"></i>
                                نسخ</button>
                        </div>

                        @guest
                            <div class="mt-5 rounded-2xl border border-violet-200 bg-violet-50 p-4" id="guestCloudSaveCard">
                                <div class="flex items-start gap-3">
                                    <span
                                        class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-violet-700 shadow-sm"><i
                                            class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i></span>
                                    <div>
                                        <h3 class="font-black text-[#111a35]">لا تفقد تصميمك</h3>
                                        <p class="mt-1 text-sm font-bold leading-6 text-slate-600">أنشئ حسابًا مجانيًا لتحفظ
                                            الرموز وتفتحها وتعدّلها من أي جهاز.</p>
                                    </div>
                                </div>
                                <button
                                    class="mt-3 w-full rounded-xl bg-violet-700 px-4 py-3 font-black text-white hover:bg-violet-800"
                                    id="guestSavePromptBtn" type="button">حفظ هذا التصميم مجانًا</button>
                            </div>
                        @else
                            @if (auth()->user()->hasVerifiedEmail())
                                <div
                                    class="mt-5 flex items-center justify-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-bold text-emerald-900">
                                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-cloud"
                                            aria-hidden="true"></i> حسابك جاهز للحفظ السحابي</span>
                                </div>
                            @else
                                <div
                                    class="mt-5 flex items-center justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-900">
                                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-envelope-circle-check"
                                            aria-hidden="true"></i> فعّل بريدك لتبدأ الحفظ السحابي</span>
                                    <a class="font-black underline" href="{{ route('verification.notice') }}">التفعيل</a>
                                </div>
                            @endif
                        @endguest
                    </div>
                </section>
            </div>

            @if ($campaigns['side'])
                <x-public.advertisement class="min-h-72 lg:sticky lg:top-25 lg:h-[620px]" :campaign="$campaigns['side']"
                    placement="side" />
            @endif
        </main>

        <section class="mx-auto grid w-[min(calc(100%_-_2rem),1100px)] gap-4 py-8 sm:grid-cols-3" data-reveal-group>
            @foreach ([['icon' => 'fa-bolt', 'title' => 'إنشاء فوري', 'text' => 'عدّل الإعدادات وشاهد النتيجة مباشرة دون خطوات معقدة.'], ['icon' => 'fa-mobile-screen', 'title' => 'قابل للمسح', 'text' => 'نستخدم تصحيح خطأ مرتفع ومساحة آمنة حول الرمز.'], ['icon' => 'fa-cloud-arrow-up', 'title' => 'حفظ اختياري', 'text' => 'أنشئ دون حساب، وسجّل فقط عندما تريد حفظ تصميمك.']] as $feature)
                <article class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm" data-reveal>
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-violet-100 text-violet-700"><i
                            class="fa-solid {{ $feature['icon'] }}" aria-hidden="true"></i></span>
                    <h2 class="mt-4 text-lg font-black text-[#111a35]">{{ $feature['title'] }}</h2>
                    <p class="mt-2 text-sm font-medium leading-7 text-slate-500">{{ $feature['text'] }}</p>
                </article>
            @endforeach
        </section>

        @if ($campaigns['bottom'])
            <section class="mx-auto w-[min(calc(100%_-_2rem),1040px)] py-6" aria-label="إعلان سفلي">
                <x-public.advertisement :campaign="$campaigns['bottom']" placement="bottom" />
            </section>
        @endif

        <dialog
            class="fixed inset-0 m-auto rounded-3xl border-0 p-0 text-right shadow-[0_30px_100px_rgba(17,24,39,0.3)] backdrop:bg-slate-950/45 backdrop:backdrop-blur-[4px]"
            id="saveQrDialog" aria-labelledby="saveQrDialogTitle">
            <form class="w-[min(460px,calc(100vw_-_32px))] p-5 sm:p-6" id="saveQrForm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black text-[#111a35]" id="saveQrDialogTitle">حفظ رمز QR</h2>
                        <p class="mt-1 text-sm font-bold leading-6 text-slate-500">أعطه اسمًا واضحًا لتجده بسهولة داخل
                            حسابك.</p>
                    </div>
                    <button
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50"
                        type="button" data-close-save-dialog aria-label="إغلاق"><i class="fa-solid fa-xmark"
                            aria-hidden="true"></i></button>
                </div>
                <label class="mt-5 grid gap-2 text-sm font-black text-slate-700" for="qrSaveTitle">اسم التصميم
                    <input
                        class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                        id="qrSaveTitle" maxlength="120" placeholder="مثال: رابط نشاط الصف الخامس" autocomplete="off">
                </label>
                <p class="mt-2 min-h-5 text-xs font-bold text-slate-500" id="qrSaveStatus" role="status"
                    aria-live="polite"></p>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button class="min-h-11 rounded-xl border border-slate-200 font-black text-slate-600 hover:bg-slate-50"
                        type="button" data-close-save-dialog>إلغاء</button>
                    <button
                        class="min-h-11 rounded-xl bg-violet-700 font-black text-white hover:bg-violet-800 disabled:cursor-wait disabled:opacity-60"
                        id="confirmSaveQrBtn" type="submit">حفظ في حسابي</button>
                </div>
            </form>
        </dialog>

        <dialog
            class="fixed inset-0 m-auto rounded-3xl border-0 p-0 text-right shadow-[0_30px_100px_rgba(17,24,39,0.3)] backdrop:bg-slate-950/45 backdrop:backdrop-blur-[4px]"
            id="guestQrDialog" aria-labelledby="guestQrDialogTitle">
            <div class="w-[min(480px,calc(100vw_-_32px))] p-5 sm:p-6">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-violet-100 text-xl text-violet-700"><i
                        class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i></span>
                <h2 class="mt-4 text-2xl font-black text-[#111a35]" id="guestQrDialogTitle">احفظ تصميمك قبل أن يضيع</h2>
                <ul class="mt-4 grid gap-2 text-sm font-bold text-slate-600">
                    <li class="flex gap-2"><i class="fa-solid fa-check mt-1 text-emerald-500" aria-hidden="true"></i>
                        افتحه وعدّله من أي جهاز.</li>
                    <li class="flex gap-2"><i class="fa-solid fa-check mt-1 text-emerald-500" aria-hidden="true"></i>
                        حمّله مجددًا دون إعادة التصميم.</li>
                    <li class="flex gap-2"><i class="fa-solid fa-check mt-1 text-emerald-500" aria-hidden="true"></i>
                        مسودتك الحالية ستبقى بانتظارك.</li>
                </ul>
                <div class="mt-6 grid gap-2">
                    <a class="min-h-12 rounded-xl bg-violet-700 px-4 py-3 text-center font-black text-white hover:bg-violet-800"
                        id="qrRegisterLink" href="{{ route('tools.qr.auth', 'register') }}">إنشاء حساب مجاني</a>
                    <a class="min-h-12 rounded-xl border border-violet-200 px-4 py-3 text-center font-black text-violet-800 hover:bg-violet-50"
                        id="qrLoginLink" href="{{ route('tools.qr.auth', 'login') }}">لدي حساب — تسجيل الدخول</a>
                    <button class="min-h-10 text-sm font-bold text-slate-500 hover:text-slate-800" type="button"
                        data-close-guest-dialog>المتابعة دون حفظ</button>
                </div>
            </div>
        </dialog>
    </div>
@endsection
