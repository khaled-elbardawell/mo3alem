@extends('layouts.public', ['activeNavigation' => 'certificates'])

@section('title', 'إنشاء شهادات احترافية')
@section('canonical', route('tools.certificates'))

@push('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@400;500;700;800;900&family=Noto+Kufi+Arabic:wght@400;500;700;800;900&display=swap" rel="stylesheet">
@endpush

@section('content')
    <div class="min-h-[calc(100vh-5rem)] overflow-hidden bg-[radial-gradient(circle_at_12%_8%,rgba(124,58,237,0.1),transparent_25%),radial-gradient(circle_at_88%_12%,rgba(245,158,11,0.1),transparent_24%),#fbfbff] pb-16">
        <div id="certificateAppConfig" hidden
            data-config="{{ json_encode($certificateConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}">
        </div>

        <x-public.tool-page-header
            title="أداة إنشاء الشهادات"
            description="اختر قالباً جاهزاً أو ارفع قالبك، ثم أضف النصوص وحرّكها بحرية قبل الحفظ والطباعة."
            current="إنشاء الشهادات"
            icon="fa-award"
        />

        @if ($campaigns['top'])
            <section class="mx-auto w-[min(calc(100%_-_2rem),1040px)] py-6" aria-label="إعلان علوي">
                <x-public.advertisement :campaign="$campaigns['top']" />
            </section>
        @endif

        <main @class([
            'mx-auto grid w-[min(calc(100%_-_1rem),1600px)] items-start gap-4 py-6 sm:w-[min(calc(100%_-_2rem),1600px)]',
            '2xl:grid-cols-[minmax(0,1fr)_260px]' => $campaigns['side'],
        ]) id="certificateBuilder">
            <div class="min-w-0">
                <section class="mb-3 rounded-2xl border border-slate-200 bg-white p-2 shadow-[0_12px_35px_rgba(15,23,42,0.08)] sm:p-3" id="certificateRibbon" aria-label="شريط أوامر الشهادة">
                    <div class="flex items-center gap-3">
                        <div class="hidden min-w-0 items-center gap-2 border-l border-slate-200 pl-3 sm:flex lg:min-w-52">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700"><i class="fa-solid fa-award" aria-hidden="true"></i></span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black text-[#111a35]" id="certificateWorkingTitle">شهادة جديدة</p>
                                <p class="truncate text-[11px] font-bold text-slate-500" id="certificateSaveIndicator" role="status" aria-live="polite">مسودة محفوظة على هذا الجهاز</p>
                            </div>
                        </div>
                        <div class="grid flex-1 grid-cols-5 gap-1 sm:flex sm:gap-1.5 sm:overflow-x-auto sm:overscroll-x-contain sm:[scrollbar-width:none]" id="certificateRibbonScroller">
                            <a class="inline-flex min-h-14 min-w-0 flex-col items-center justify-center gap-1 rounded-xl border border-slate-200 px-1 text-[9px] font-black text-slate-700 hover:border-violet-200 hover:bg-violet-50 hover:text-violet-800 sm:min-h-11 sm:shrink-0 sm:flex-row sm:gap-2 sm:px-3 sm:text-xs" id="createNewCertificateLink" href="{{ route('tools.certificates') }}"><i class="fa-regular fa-file" aria-hidden="true"></i> جديد</a>
                            <a class="inline-flex min-h-14 min-w-0 flex-col items-center justify-center gap-1 rounded-xl border border-slate-200 px-1 text-[9px] font-black text-slate-700 hover:border-violet-200 hover:bg-violet-50 hover:text-violet-800 sm:min-h-11 sm:shrink-0 sm:flex-row sm:gap-2 sm:px-3 sm:text-xs" href="{{ auth()->check() ? route('dashboard', ['section' => 'certificates']) : route('tools.certificates.auth', 'login') }}"><i class="fa-regular fa-folder-open" aria-hidden="true"></i> شهاداتي</a>
                            <button class="inline-flex min-h-14 min-w-0 flex-col items-center justify-center gap-1 rounded-xl border border-slate-200 px-1 text-[9px] font-black text-slate-700 hover:bg-slate-50 sm:min-h-11 sm:shrink-0 sm:flex-row sm:gap-2 sm:px-3 sm:text-xs" id="certificatePreviewBtn" type="button"><i class="fa-regular fa-eye" aria-hidden="true"></i> معاينة</button>
                            <button class="inline-flex min-h-14 min-w-0 flex-col items-center justify-center gap-1 rounded-xl border border-violet-200 bg-violet-50 px-1 text-[9px] font-black text-violet-800 hover:bg-violet-100 sm:min-h-11 sm:shrink-0 sm:flex-row sm:gap-2 sm:px-3 sm:text-xs" id="saveCertificateBtn" type="button"><i class="fa-regular fa-floppy-disk" aria-hidden="true"></i> حفظ</button>
                            <button class="inline-flex min-h-14 min-w-0 flex-col items-center justify-center gap-1 rounded-xl bg-violet-700 px-1 text-[9px] font-black text-white shadow-md shadow-violet-900/15 hover:bg-violet-800 sm:min-h-11 sm:shrink-0 sm:flex-row sm:gap-2 sm:px-4 sm:text-xs" id="printCertificateBtn" type="button"><i class="fa-solid fa-print" aria-hidden="true"></i> طباعة</button>
                        </div>
                    </div>
                </section>

                <div class="grid min-w-0 grid-cols-1 gap-3 fullscreen:h-screen fullscreen:overflow-auto fullscreen:bg-[#f7f7fb] fullscreen:p-3 lg:grid-cols-[300px_minmax(0,1fr)_68px] lg:rounded-3xl lg:border lg:border-slate-200 lg:bg-[#f7f7fb] lg:p-3 lg:shadow-[0_18px_60px_rgba(15,23,42,0.08)]" id="certificateEditorShell">
                    <aside class="order-2 hidden scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-3 lg:order-none lg:col-start-1 lg:row-start-1 lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto" id="certificateTextControls" data-certificate-sidebar-panel="properties" aria-labelledby="certificatePropertiesTitle">
                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="grid size-9 place-items-center rounded-xl bg-violet-100 text-violet-700"><i class="fa-solid fa-font" aria-hidden="true"></i></span>
                                <div>
                                    <p class="text-[11px] font-black text-violet-700">العنصر المحدد</p>
                                    <h2 class="text-base font-black text-[#111a35]" id="certificatePropertiesTitle">خصائص النص</h2>
                                </div>
                            </div>
                            <span class="rounded-lg bg-violet-50 px-2 py-1 text-xs font-black text-violet-700">نص</span>
                        </div>

                        <div class="mt-3 min-h-32 place-items-center rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 text-center text-sm font-bold leading-6 text-slate-500" id="certificatePropertiesEmpty">
                            حدد نصاً من الشهادة لتظهر أدواته هنا
                        </div>

                        <form class="mt-3 hidden grid-cols-2 gap-3" id="certificatePropertiesForm">
                            <label class="col-span-2 grid gap-1.5 text-xs font-black text-slate-600" for="certificateTextContent">النص
                                <textarea class="min-h-20 resize-y rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-violet-400 focus:ring-3 focus:ring-violet-100" id="certificateTextContent" maxlength="1000"></textarea>
                            </label>
                            <label class="grid gap-1.5 text-xs font-black text-slate-600" for="certificateFontFamily">الخط
                                <select class="h-11 rounded-xl border border-slate-200 bg-white px-2 text-sm outline-none focus:border-violet-400" id="certificateFontFamily">
                                    <option value="Tajawal">Tajawal</option>
                                    <option value="Cairo">Cairo</option>
                                    <option value="Amiri">Amiri</option>
                                    <option value="Noto Kufi Arabic">Noto Kufi</option>
                                </select>
                            </label>
                            <label class="grid gap-1.5 text-xs font-black text-slate-600" for="certificateFontWeight">السماكة
                                <select class="h-11 rounded-xl border border-slate-200 bg-white px-2 text-sm outline-none" id="certificateFontWeight">
                                    <option value="400">عادي</option>
                                    <option value="500">متوسط</option>
                                    <option value="700">عريض</option>
                                    <option value="800">عريض جداً</option>
                                    <option value="900">أسود</option>
                                </select>
                            </label>
                            <label class="grid gap-1.5 text-xs font-black text-slate-600" for="certificateFontSize">الحجم
                                <input class="h-11 rounded-xl border border-slate-200 bg-white px-3 outline-none focus:border-violet-400" id="certificateFontSize" type="number" min="8" max="240">
                            </label>
                            <label class="grid gap-1.5 text-xs font-black text-slate-600" for="certificateTextColor">اللون
                                <span class="flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-2">
                                    <input class="size-7 border-0 bg-transparent p-0" id="certificateTextColor" type="color">
                                    <span class="font-mono text-[10px]" id="certificateTextColorValue" dir="ltr">#172b52</span>
                                </span>
                            </label>
                            <fieldset class="col-span-2">
                                <legend class="text-xs font-black text-slate-600">محاذاة النص</legend>
                                <div class="mt-1.5 grid grid-cols-3 gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1" role="radiogroup">
                                    @foreach ([['right', 'fa-align-right'], ['center', 'fa-align-center'], ['left', 'fa-align-left']] as [$alignment, $icon])
                                        <label>
                                            <input class="peer sr-only" type="radio" name="certificate_text_align" value="{{ $alignment }}">
                                            <span class="grid min-h-10 place-items-center rounded-lg text-slate-500 peer-checked:bg-white peer-checked:text-violet-800 peer-checked:shadow-sm"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                            <label class="grid gap-1 text-xs font-black text-slate-600" for="certificateRotation">الدوران <span id="certificateRotationValue">0°</span>
                                <input class="h-8 w-full accent-violet-700" id="certificateRotation" type="range" min="-180" max="180" value="0">
                            </label>
                            <label class="grid gap-1 text-xs font-black text-slate-600" for="certificateOpacity">الشفافية <span id="certificateOpacityValue">100%</span>
                                <input class="h-8 w-full accent-violet-700" id="certificateOpacity" type="range" min="10" max="100" value="100">
                            </label>
                            <label class="col-span-2 flex min-h-11 items-center justify-between rounded-xl border border-slate-200 px-3 text-xs font-black text-slate-600">
                                قفل العنصر
                                <input class="size-5 accent-violet-700" id="certificateElementLocked" type="checkbox">
                            </label>
                            <div class="col-span-2 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3">
                                <button class="grid min-h-10 place-items-center rounded-xl border border-violet-200 text-violet-700 hover:bg-violet-50" id="duplicateCertificateElementBtn" type="button" aria-label="نسخ العنصر" title="نسخ"><i class="fa-regular fa-copy" aria-hidden="true"></i></button>
                                <button class="grid min-h-10 place-items-center rounded-xl border border-red-200 text-red-700 hover:bg-red-50" id="deleteCertificateElementBtn" type="button" aria-label="حذف العنصر" title="حذف"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                            </div>
                        </form>
                    </aside>

                    <section class="order-1 w-full max-w-full min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 lg:order-none lg:col-start-2 lg:row-start-1 lg:p-3" aria-labelledby="certificateCanvasTitle">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2" id="certificateCanvasToolbar">
                            <div class="flex items-center gap-1.5">
                                <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1">
                                    <button class="grid size-9 place-items-center rounded-lg bg-white text-slate-600 shadow-sm hover:text-violet-700" id="certificateZoomOutBtn" type="button" aria-label="تصغير"><i class="fa-solid fa-minus" aria-hidden="true"></i></button>
                                    <span class="min-w-12 text-center text-xs font-black text-slate-600" id="certificateZoomValue">70%</span>
                                    <input class="sr-only" id="certificateZoomRange" type="range" min="25" max="125" value="70" aria-label="مستوى التكبير">
                                    <button class="grid size-9 place-items-center rounded-lg bg-white text-slate-600 shadow-sm hover:text-violet-700" id="certificateZoomInBtn" type="button" aria-label="تكبير"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
                                </div>
                                <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1" data-certificate-history-controls>
                                    <button class="grid size-9 place-items-center rounded-lg bg-white text-slate-600 shadow-sm hover:text-violet-700 disabled:opacity-35" id="certificateUndoBtn" type="button" aria-label="تراجع" title="تراجع"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i></button>
                                    <button class="grid size-9 place-items-center rounded-lg bg-white text-slate-600 shadow-sm hover:text-violet-700 disabled:opacity-35" id="certificateRedoBtn" type="button" aria-label="إعادة" title="إعادة"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i></button>
                                </div>
                            </div>
                            <h2 class="sr-only" id="certificateCanvasTitle">مساحة العمل</h2>
                            <div class="flex items-center gap-1.5">
                                <button class="grid size-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-violet-50 hover:text-violet-700" id="certificateFitBtn" type="button" aria-label="احتواء الشهادة" title="احتواء"><i class="fa-solid fa-up-right-and-down-left-from-center" aria-hidden="true"></i></button>
                                <button class="grid size-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-violet-50 hover:text-violet-700" id="certificateFullscreenBtn" type="button" aria-label="ملء الشاشة" title="ملء الشاشة"><i class="fa-solid fa-expand" aria-hidden="true"></i></button>
                            </div>
                        </div>

                        <div class="mt-2 min-h-[300px] w-full max-w-full overflow-auto rounded-xl border border-violet-100 bg-[#f5f5fa] p-2 sm:min-h-[520px] lg:min-h-[610px] lg:p-4" id="certificateViewport">
                            <div class="mx-auto" id="certificateCanvasSizer">
                                <div class="certificate-canvas" id="certificateCanvas" tabindex="0" aria-label="محرر الشهادة">
                                    <img class="certificate-background" id="certificateBackground" alt="قالب الشهادة">
                                    <div class="certificate-guide certificate-guide-x" id="certificateGuideX" aria-hidden="true"></div>
                                    <div class="certificate-guide certificate-guide-y" id="certificateGuideY" aria-hidden="true"></div>
                                </div>
                            </div>
                        </div>

                        <p class="mt-2 rounded-xl bg-slate-50 px-3 py-2 text-center text-xs font-bold text-slate-500"><i class="fa-regular fa-hand-pointer ml-1 text-violet-600" aria-hidden="true"></i> اسحب النص لتحريكه، وانقر عليه مرتين للكتابة.</p>

                        @guest
                            <div class="mt-3 hidden rounded-xl border border-violet-200 bg-violet-50 p-3 lg:block" id="guestCertificateSaveCard">
                                <div class="flex items-start gap-3">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-white text-violet-700 shadow-sm"><i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i></span>
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-black text-[#111a35]">تصميمك محفوظ على هذا الجهاز</h3>
                                        <p class="mt-0.5 text-xs font-bold leading-5 text-slate-600">أنشئ حساباً مجانياً لحفظ الشهادة وفتحها من أي جهاز.</p>
                                    </div>
                                </div>
                                <button class="mt-2 w-full rounded-xl bg-violet-700 px-4 py-2.5 text-sm font-black text-white hover:bg-violet-800" id="guestCertificateSavePromptBtn" type="button">حفظ هذه الشهادة مجاناً</button>
                            </div>
                        @endguest
                    </section>

                    <aside class="order-2 scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-3 lg:order-none lg:col-start-1 lg:row-start-1 lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:overflow-hidden" id="certificateTemplatesPanel" data-certificate-sidebar-panel="templates" aria-labelledby="certificateTemplatesTitle">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3">
                            <h2 class="font-black text-[#111a35]" id="certificateTemplatesTitle">القوالب الجاهزة</h2>
                            <span class="rounded-lg bg-violet-50 px-2 py-1 text-[10px] font-black text-violet-700">{{ count($certificateConfig['templates']) }} قالب</span>
                        </div>
                        <div class="mt-3 flex snap-x snap-mandatory gap-2 overflow-x-auto pb-2 [scrollbar-width:thin] lg:grid lg:max-h-[470px] lg:grid-cols-1 lg:overflow-y-auto lg:pl-1" id="certificateTemplates">
                            @foreach ($certificateConfig['templates'] as $template)
                                <button class="group/template w-28 shrink-0 snap-start rounded-xl border border-slate-200 bg-white p-1.5 text-xs font-black text-slate-600 transition hover:border-violet-300 hover:bg-violet-50 data-selected:border-violet-500 data-selected:bg-violet-50 data-selected:text-violet-800 data-selected:ring-3 data-selected:ring-violet-100 lg:w-full" type="button" data-certificate-template="{{ $template['key'] }}">
                                    <img class="aspect-[1.414/1] w-full rounded-lg bg-slate-50 object-cover" src="{{ $template['url'] }}" alt="{{ $template['label'] }}" loading="lazy">
                                    <span class="mt-1.5 block truncate">{{ $template['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                        <label class="mt-2 flex min-h-12 items-center justify-center gap-2 rounded-xl border border-dashed border-violet-300 bg-violet-50 px-3 text-center text-xs font-black text-violet-800 hover:bg-violet-100" for="certificateBackgroundInput"><i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i> رفع قالب خاص</label>
                        <input class="sr-only" id="certificateBackgroundInput" type="file" accept="image/png,image/jpeg,image/webp">
                        <p class="mt-1.5 text-[10px] font-bold leading-4 text-slate-500" id="certificateBackgroundHint">PNG أو JPG أو WebP حتى 4 ميجابايت.</p>

                    </aside>

                    <aside class="order-2 hidden scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-3 lg:order-none lg:col-start-1 lg:row-start-1 lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:overflow-hidden" id="certificateLayersPanel" data-certificate-sidebar-panel="layers" aria-labelledby="certificateLayersTitle">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="grid size-9 place-items-center rounded-xl bg-violet-100 text-violet-700"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
                                <h2 class="font-black text-[#111a35]" id="certificateLayersTitle">طبقات النص</h2>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-500" id="certificateLayersCount">0</span>
                        </div>
                        <p class="mt-3 text-xs font-bold leading-5 text-slate-500">اختر عنصراً لتعديله أو اسحبه داخل الشهادة لتغيير مكانه.</p>
                        <div class="mt-3 grid max-h-[520px] gap-2 overflow-y-auto" id="certificateLayersList"></div>
                    </aside>

                    <nav class="fixed inset-x-2 bottom-2 z-70 grid grid-cols-3 gap-2 rounded-2xl border border-slate-200 bg-white/95 p-2 shadow-[0_12px_35px_rgba(15,23,42,0.15)] backdrop-blur-xl lg:order-none lg:sticky lg:inset-auto lg:top-24 lg:col-start-3 lg:row-start-1 lg:z-auto lg:flex lg:h-[560px] lg:flex-col lg:gap-2 lg:border-0 lg:bg-slate-950 lg:p-2 lg:text-white lg:shadow-none" id="certificateEditorRail" aria-label="أدوات المحرر">
                        <button class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl px-1 text-[10px] font-black text-slate-600 hover:bg-violet-50 hover:text-violet-800 data-selected:bg-violet-700 data-selected:text-white lg:text-slate-200 lg:hover:bg-white/10 lg:hover:text-white" type="button" data-certificate-sidebar-tab="templates" data-selected><i class="fa-solid fa-grip" aria-hidden="true"></i> القوالب</button>
                        <button class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl px-1 text-[10px] font-black text-slate-600 hover:bg-violet-50 hover:text-violet-800 data-selected:bg-violet-700 data-selected:text-white lg:text-slate-200 lg:hover:bg-white/10 lg:hover:text-white" id="addCertificateTextBtn" type="button" data-certificate-sidebar-tab="properties"><i class="fa-regular fa-square-plus" aria-hidden="true"></i> إضافة نص</button>
                        <button class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl px-1 text-[10px] font-black text-slate-600 hover:bg-violet-50 hover:text-violet-800 data-selected:bg-violet-700 data-selected:text-white lg:text-slate-200 lg:hover:bg-white/10 lg:hover:text-white" type="button" data-certificate-sidebar-tab="layers"><i class="fa-solid fa-layer-group" aria-hidden="true"></i> الطبقات</button>
                    </nav>
                </div>
            </div>

            @if ($campaigns['side'])
                <x-public.advertisement class="min-h-72 2xl:sticky 2xl:top-25 2xl:h-[620px]" :campaign="$campaigns['side']" placement="side" />
            @endif
        </main>

        @if ($campaigns['bottom'])
            <section class="mx-auto w-[min(calc(100%_-_2rem),1040px)] py-6" aria-label="إعلان سفلي">
                <x-public.advertisement :campaign="$campaigns['bottom']" placement="bottom" />
            </section>
        @endif

        <dialog class="fixed inset-0 m-auto rounded-3xl border-0 p-0 text-right shadow-[0_30px_100px_rgba(17,24,39,0.3)] backdrop:bg-slate-950/45 backdrop:backdrop-blur-[4px]" id="saveCertificateDialog" aria-labelledby="saveCertificateDialogTitle">
            <form class="w-[min(460px,calc(100vw_-_32px))] p-5 sm:p-6" id="saveCertificateForm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black text-[#111a35]" id="saveCertificateDialogTitle">حفظ الشهادة</h2>
                        <p class="mt-1 text-sm font-bold leading-6 text-slate-500">اختر اسماً واضحاً لتجد التصميم بسهولة داخل حسابك.</p>
                    </div>
                    <button class="grid size-9 shrink-0 place-items-center rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50" type="button" data-close-certificate-save aria-label="إغلاق"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <label class="mt-5 grid gap-2 text-sm font-black text-slate-700" for="certificateSaveTitle">اسم التصميم
                    <input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" id="certificateSaveTitle" maxlength="120" placeholder="مثال: شهادة تفوق الفصل الأول" autocomplete="off">
                </label>
                <p class="mt-2 min-h-5 text-xs font-bold text-slate-500" id="certificateSaveStatus" role="status" aria-live="polite"></p>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button class="min-h-11 rounded-xl border border-slate-200 font-black text-slate-600 hover:bg-slate-50" type="button" data-close-certificate-save>إلغاء</button>
                    <button class="min-h-11 rounded-xl bg-violet-700 font-black text-white hover:bg-violet-800 disabled:cursor-wait disabled:opacity-60" id="confirmSaveCertificateBtn" type="submit">حفظ في حسابي</button>
                </div>
            </form>
        </dialog>

        <dialog class="fixed inset-0 m-auto rounded-3xl border-0 p-0 text-right shadow-[0_30px_100px_rgba(17,24,39,0.3)] backdrop:bg-slate-950/45 backdrop:backdrop-blur-[4px]" id="guestCertificateDialog" aria-labelledby="guestCertificateDialogTitle">
            <div class="w-[min(480px,calc(100vw_-_32px))] p-5 sm:p-6">
                <span class="grid size-12 place-items-center rounded-2xl bg-violet-100 text-xl text-violet-700"><i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i></span>
                <h2 class="mt-4 text-2xl font-black text-[#111a35]" id="guestCertificateDialogTitle">احفظ شهادتك قبل أن تضيع</h2>
                <ul class="mt-4 grid gap-2 text-sm font-bold text-slate-600">
                    <li class="flex gap-2"><i class="fa-solid fa-check mt-1 text-emerald-500" aria-hidden="true"></i> افتحها وعدّلها من أي جهاز.</li>
                    <li class="flex gap-2"><i class="fa-solid fa-check mt-1 text-emerald-500" aria-hidden="true"></i> اطبعها أو نزّلها مجدداً في أي وقت.</li>
                    <li class="flex gap-2"><i class="fa-solid fa-check mt-1 text-emerald-500" aria-hidden="true"></i> ستعود إلى نفس المسودة بعد تسجيل الدخول.</li>
                </ul>
                <div class="mt-6 grid gap-2">
                    <a class="min-h-12 rounded-xl bg-violet-700 px-4 py-3 text-center font-black text-white hover:bg-violet-800" id="certificateRegisterLink" href="{{ route('tools.certificates.auth', 'register') }}">إنشاء حساب مجاني</a>
                    <a class="min-h-12 rounded-xl border border-violet-200 px-4 py-3 text-center font-black text-violet-800 hover:bg-violet-50" id="certificateLoginLink" href="{{ route('tools.certificates.auth', 'login') }}">لدي حساب — تسجيل الدخول</a>
                    <button class="min-h-10 text-sm font-bold text-slate-500 hover:text-slate-800" type="button" data-close-certificate-guest>المتابعة دون حفظ</button>
                </div>
            </div>
        </dialog>

        <dialog class="fixed inset-0 m-auto max-h-[calc(100vh_-_2rem)] max-w-[calc(100vw_-_2rem)] overflow-auto rounded-3xl border-0 bg-slate-950 p-0 text-right shadow-[0_30px_100px_rgba(17,24,39,0.45)] backdrop:bg-slate-950/60 backdrop:backdrop-blur-[4px]" id="certificatePreviewDialog" aria-labelledby="certificatePreviewDialogTitle">
            <div class="w-[min(1100px,calc(100vw_-_32px))] p-4 sm:p-6">
                <div class="flex items-center justify-between gap-4 text-white">
                    <div>
                        <h2 class="text-xl font-black" id="certificatePreviewDialogTitle">المعاينة النهائية</h2>
                        <p class="mt-1 text-sm font-bold text-slate-300">هذه هي النتيجة التي ستظهر عند التنزيل والطباعة.</p>
                    </div>
                    <button class="grid size-10 place-items-center rounded-full border border-white/20 text-white hover:bg-white/10" type="button" data-close-certificate-preview aria-label="إغلاق"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <div class="mt-5 grid min-h-64 place-items-center rounded-2xl bg-slate-900 p-2 sm:p-5">
                    <img class="max-h-[72vh] max-w-full object-contain shadow-2xl" id="certificatePreviewImage" alt="معاينة الشهادة النهائية">
                </div>
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    <button class="min-h-12 rounded-xl bg-violet-600 px-4 font-black text-white hover:bg-violet-500" id="downloadCertificatePngBtn" type="button"><i class="fa-solid fa-download ml-1" aria-hidden="true"></i> تنزيل PNG عالي الدقة</button>
                    <button class="min-h-12 rounded-xl border border-white/20 px-4 font-black text-white hover:bg-white/10" id="printCertificatePreviewBtn" type="button"><i class="fa-solid fa-print ml-1" aria-hidden="true"></i> طباعة / حفظ PDF</button>
                </div>
            </div>
        </dialog>
    </div>
@endsection
