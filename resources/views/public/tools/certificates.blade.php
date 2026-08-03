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
                <div class="mb-4 grid gap-3 rounded-3xl border border-violet-100 bg-white p-3 shadow-[0_14px_45px_rgba(49,46,129,0.08)] lg:grid-cols-[minmax(220px,1fr)_auto] lg:items-center sm:p-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-amber-100 text-xl text-amber-700"><i class="fa-solid fa-award" aria-hidden="true"></i></span>
                        <div class="min-w-0">
                            <p class="truncate font-black text-[#111a35]" id="certificateWorkingTitle">شهادة جديدة</p>
                            <p class="mt-0.5 text-xs font-bold text-slate-500" id="certificateSaveIndicator" role="status" aria-live="polite">مسودة محفوظة على هذا الجهاز</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                        <div class="flex rounded-xl border border-slate-200 bg-slate-50 p-1">
                            <button class="grid size-10 place-items-center rounded-lg text-slate-600 hover:bg-white disabled:opacity-35" id="certificateUndoBtn" type="button" aria-label="تراجع" title="تراجع"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i></button>
                            <button class="grid size-10 place-items-center rounded-lg text-slate-600 hover:bg-white disabled:opacity-35" id="certificateRedoBtn" type="button" aria-label="إعادة" title="إعادة"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i></button>
                        </div>
                        <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-violet-200 bg-white px-4 text-sm font-black text-violet-800 hover:bg-violet-50" id="certificatePreviewBtn" type="button"><i class="fa-regular fa-eye" aria-hidden="true"></i> معاينة</button>
                        <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-violet-200 bg-white px-4 text-sm font-black text-violet-800 hover:bg-violet-50" id="saveCertificateBtn" type="button"><i class="fa-regular fa-floppy-disk" aria-hidden="true"></i> حفظ</button>
                        <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-linear-to-l from-violet-700 to-indigo-700 px-5 text-sm font-black text-white shadow-lg shadow-violet-900/20 hover:-translate-y-0.5 disabled:opacity-50" id="printCertificateBtn" type="button"><i class="fa-solid fa-print" aria-hidden="true"></i> طباعة / تنزيل</button>
                    </div>
                </div>

                <div class="grid min-w-0 gap-4 xl:grid-cols-[250px_minmax(0,1fr)_280px]">
                    <aside class="order-2 min-w-0 rounded-3xl border border-violet-100 bg-white p-4 shadow-[0_14px_45px_rgba(49,46,129,0.07)] xl:order-1 xl:sticky xl:top-24 xl:max-h-[calc(100vh-7rem)] xl:overflow-y-auto" aria-labelledby="certificateToolsTitle">
                        @auth
                            <nav class="mb-4 grid grid-cols-2 gap-2" aria-label="إجراءات الشهادات">
                                <a class="rounded-xl bg-violet-700 px-3 py-2.5 text-center text-sm font-black text-white hover:bg-violet-800" href="{{ route('dashboard', ['section' => 'certificates']) }}"><i class="fa-solid fa-folder-open ml-1" aria-hidden="true"></i> شهاداتي</a>
                                <a class="rounded-xl border border-violet-200 px-3 py-2.5 text-center text-sm font-black text-violet-800 hover:bg-violet-50" id="createNewCertificateLink" href="{{ route('tools.certificates') }}"><i class="fa-solid fa-plus ml-1" aria-hidden="true"></i> جديد</a>
                            </nav>
                        @endauth

                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-black text-violet-700">ابدأ من هنا</p>
                                <h2 class="mt-1 text-xl font-black text-[#111a35]" id="certificateToolsTitle">القوالب والأدوات</h2>
                            </div>
                            <span class="grid size-10 place-items-center rounded-xl bg-violet-100 text-violet-700"><i class="fa-solid fa-shapes" aria-hidden="true"></i></span>
                        </div>

                        <fieldset class="mt-5">
                            <legend class="text-sm font-black text-slate-700">القوالب الجاهزة</legend>
                            <div class="mt-3 grid grid-cols-2 gap-2" id="certificateTemplates">
                                @foreach ($certificateConfig['templates'] as $template)
                                    <button class="group rounded-xl border border-slate-200 bg-white p-1.5 text-xs font-black text-slate-600 transition hover:border-violet-300 hover:bg-violet-50 data-selected:border-violet-500 data-selected:bg-violet-50 data-selected:text-violet-800 data-selected:ring-3 data-selected:ring-violet-100" type="button" data-certificate-template="{{ $template['key'] }}">
                                        <img class="aspect-[1.414/1] w-full rounded-lg bg-slate-50 object-cover" src="{{ $template['url'] }}" alt="{{ $template['label'] }}" loading="lazy">
                                        <span class="mt-1.5 block">{{ $template['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </fieldset>

                        <div class="mt-5 border-t border-slate-100 pt-5">
                            <label class="flex min-h-12 items-center justify-center gap-2 rounded-xl border border-dashed border-violet-300 bg-violet-50 px-3 text-center text-sm font-black text-violet-800 hover:bg-violet-100" for="certificateBackgroundInput">
                                <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                                رفع قالب خاص
                            </label>
                            <input class="sr-only" id="certificateBackgroundInput" type="file" accept="image/png,image/jpeg,image/webp">
                            <p class="mt-2 text-xs font-bold leading-5 text-slate-500" id="certificateBackgroundHint">PNG أو JPG أو WebP، حتى 4 ميجابايت.</p>
                        </div>

                        <button class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 font-black text-white hover:bg-slate-800" id="addCertificateTextBtn" type="button"><i class="fa-solid fa-plus" aria-hidden="true"></i> إضافة مربع نص</button>

                        <section class="mt-5 border-t border-slate-100 pt-5" aria-labelledby="certificateLayersTitle">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="font-black text-[#111a35]" id="certificateLayersTitle">الطبقات</h3>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-500" id="certificateLayersCount">0</span>
                            </div>
                            <div class="mt-3 grid gap-2" id="certificateLayersList"></div>
                        </section>
                    </aside>

                    <section class="order-1 min-w-0 rounded-3xl border border-violet-100 bg-white p-3 shadow-[0_18px_60px_rgba(49,46,129,0.09)] sm:p-5 xl:order-2" aria-labelledby="certificateCanvasTitle">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-black text-[#111a35]" id="certificateCanvasTitle">مساحة العمل</h2>
                                <p class="mt-1 text-xs font-bold text-slate-500">اسحب العنصر، واستخدم مقابض الزوايا لتغيير حجمه.</p>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700"><i class="fa-solid fa-circle text-[7px]" aria-hidden="true"></i> جاهز للتحرير</span>
                        </div>

                        <div class="mt-4 min-h-[360px] overflow-auto rounded-2xl border border-violet-100 bg-[radial-gradient(circle_at_50%_45%,rgba(124,58,237,0.11),transparent_42%),#f3f1fb] p-4 sm:min-h-[560px] sm:p-7" id="certificateViewport">
                            <div class="mx-auto" id="certificateCanvasSizer">
                                <div class="certificate-canvas" id="certificateCanvas" tabindex="0" aria-label="محرر الشهادة">
                                    <img class="certificate-background" id="certificateBackground" alt="قالب الشهادة">
                                    <div class="certificate-guide certificate-guide-x" id="certificateGuideX" aria-hidden="true"></div>
                                    <div class="certificate-guide certificate-guide-y" id="certificateGuideY" aria-hidden="true"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3">
                            <div class="flex items-center gap-2">
                                <button class="grid size-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:text-violet-700" id="certificateZoomOutBtn" type="button" aria-label="تصغير"><i class="fa-solid fa-minus" aria-hidden="true"></i></button>
                                <input class="w-28 accent-violet-700 sm:w-40" id="certificateZoomRange" type="range" min="25" max="125" value="70" aria-label="مستوى التكبير">
                                <button class="grid size-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:text-violet-700" id="certificateZoomInBtn" type="button" aria-label="تكبير"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
                                <span class="min-w-11 text-center text-sm font-black text-slate-600" id="certificateZoomValue">70%</span>
                            </div>
                            <button class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 hover:bg-violet-50 hover:text-violet-800" id="certificateFitBtn" type="button"><i class="fa-solid fa-expand ml-1" aria-hidden="true"></i> احتواء</button>
                        </div>

                        @guest
                            <div class="mt-4 rounded-2xl border border-violet-200 bg-violet-50 p-4" id="guestCertificateSaveCard">
                                <div class="flex items-start gap-3">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white text-violet-700 shadow-sm"><i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i></span>
                                    <div class="min-w-0">
                                        <h3 class="font-black text-[#111a35]">تصميمك محفوظ على هذا الجهاز</h3>
                                        <p class="mt-1 text-sm font-bold leading-6 text-slate-600">أنشئ حساباً مجانياً لحفظ الشهادة وفتحها من أي جهاز.</p>
                                    </div>
                                </div>
                                <button class="mt-3 w-full rounded-xl bg-violet-700 px-4 py-3 font-black text-white hover:bg-violet-800" id="guestCertificateSavePromptBtn" type="button">حفظ هذه الشهادة مجاناً</button>
                            </div>
                        @endguest
                    </section>

                    <aside class="order-3 min-w-0 rounded-3xl border border-violet-100 bg-white p-4 shadow-[0_14px_45px_rgba(49,46,129,0.07)] xl:sticky xl:top-24 xl:max-h-[calc(100vh-7rem)] xl:overflow-y-auto" aria-labelledby="certificatePropertiesTitle">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-black text-violet-700">العنصر المحدد</p>
                                <h2 class="mt-1 text-xl font-black text-[#111a35]" id="certificatePropertiesTitle">خصائص النص</h2>
                            </div>
                            <span class="grid size-10 place-items-center rounded-xl bg-violet-100 text-violet-700"><i class="fa-solid fa-sliders" aria-hidden="true"></i></span>
                        </div>

                        <div class="mt-6 grid place-items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-10 text-center text-slate-500" id="certificatePropertiesEmpty">
                            <i class="fa-solid fa-arrow-pointer text-2xl text-violet-300" aria-hidden="true"></i>
                            <p class="text-sm font-bold leading-6">اختر نصاً من الشهادة أو أضف مربعاً جديداً لتعديل خصائصه.</p>
                        </div>

                        <form class="mt-5 hidden gap-4" id="certificatePropertiesForm">
                            <label class="grid gap-2 text-sm font-black text-slate-700" for="certificateTextContent">النص
                                <textarea class="min-h-24 resize-y rounded-xl border border-slate-200 px-3 py-2 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" id="certificateTextContent" maxlength="1000"></textarea>
                            </label>

                            <div class="grid grid-cols-2 gap-3">
                                <label class="grid gap-2 text-xs font-black text-slate-600" for="certificateFontFamily">الخط
                                    <select class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-violet-400" id="certificateFontFamily">
                                        <option value="Tajawal">Tajawal</option>
                                        <option value="Cairo">Cairo</option>
                                        <option value="Amiri">Amiri</option>
                                        <option value="Noto Kufi Arabic">Noto Kufi</option>
                                    </select>
                                </label>
                                <label class="grid gap-2 text-xs font-black text-slate-600" for="certificateFontSize">الحجم
                                    <input class="min-h-11 rounded-xl border border-slate-200 px-3 outline-none focus:border-violet-400" id="certificateFontSize" type="number" min="8" max="240">
                                </label>
                            </div>

                            <div class="grid grid-cols-[1fr_auto] gap-3">
                                <label class="grid gap-2 text-xs font-black text-slate-600" for="certificateTextColor">اللون
                                    <span class="flex min-h-11 items-center gap-2 rounded-xl border border-slate-200 p-2">
                                        <input class="size-8 border-0 bg-transparent p-0" id="certificateTextColor" type="color">
                                        <span class="font-mono text-xs" id="certificateTextColorValue" dir="ltr">#172b52</span>
                                    </span>
                                </label>
                                <label class="grid gap-2 text-xs font-black text-slate-600" for="certificateFontWeight">السماكة
                                    <select class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 outline-none" id="certificateFontWeight">
                                        <option value="400">عادي</option>
                                        <option value="500">متوسط</option>
                                        <option value="700">عريض</option>
                                        <option value="800">عريض جداً</option>
                                        <option value="900">أسود</option>
                                    </select>
                                </label>
                            </div>

                            <fieldset>
                                <legend class="text-xs font-black text-slate-600">المحاذاة</legend>
                                <div class="mt-2 grid grid-cols-3 gap-2" role="radiogroup">
                                    @foreach ([['right', 'fa-align-right'], ['center', 'fa-align-center'], ['left', 'fa-align-left']] as [$alignment, $icon])
                                        <label>
                                            <input class="peer sr-only" type="radio" name="certificate_text_align" value="{{ $alignment }}">
                                            <span class="grid min-h-11 place-items-center rounded-xl border border-slate-200 text-slate-500 peer-checked:border-violet-500 peer-checked:bg-violet-50 peer-checked:text-violet-800"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>

                            <div class="grid grid-cols-2 gap-3">
                                <label class="grid gap-2 text-xs font-black text-slate-600" for="certificateRotation">الدوران <span id="certificateRotationValue">0°</span>
                                    <input class="accent-violet-700" id="certificateRotation" type="range" min="-180" max="180" value="0">
                                </label>
                                <label class="grid gap-2 text-xs font-black text-slate-600" for="certificateOpacity">الشفافية <span id="certificateOpacityValue">100%</span>
                                    <input class="accent-violet-700" id="certificateOpacity" type="range" min="10" max="100" value="100">
                                </label>
                            </div>

                            <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm font-black text-slate-700">
                                قفل العنصر
                                <input class="size-5 accent-violet-700" id="certificateElementLocked" type="checkbox">
                            </label>

                            <div class="grid grid-cols-4 gap-2 border-t border-slate-100 pt-4">
                                <button class="grid min-h-11 place-items-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50" id="certificateLayerUpBtn" type="button" aria-label="إرسال للأمام" title="إرسال للأمام"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i></button>
                                <button class="grid min-h-11 place-items-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50" id="certificateLayerDownBtn" type="button" aria-label="إرسال للخلف" title="إرسال للخلف"><i class="fa-solid fa-arrow-down" aria-hidden="true"></i></button>
                                <button class="grid min-h-11 place-items-center rounded-xl border border-slate-200 text-violet-700 hover:bg-violet-50" id="duplicateCertificateElementBtn" type="button" aria-label="نسخ العنصر" title="نسخ"><i class="fa-regular fa-copy" aria-hidden="true"></i></button>
                                <button class="grid min-h-11 place-items-center rounded-xl border border-red-200 text-red-700 hover:bg-red-50" id="deleteCertificateElementBtn" type="button" aria-label="حذف العنصر" title="حذف"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                            </div>
                        </form>

                        <div class="mt-5 rounded-2xl border border-amber-100 bg-amber-50 p-3 text-xs font-bold leading-6 text-amber-900">
                            <i class="fa-regular fa-lightbulb ml-1" aria-hidden="true"></i>
                            انقر مرتين على النص لتحريره داخل الشهادة، واستخدم الأسهم لتحريكه بدقة.
                        </div>
                    </aside>
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
