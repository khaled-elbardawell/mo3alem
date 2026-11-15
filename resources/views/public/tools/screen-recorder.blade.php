@extends('layouts.public', ['activeNavigation' => 'tools'])

@section('content')
    <x-public.tool-page-header
        title="أداة تسجيل الشاشة وتحرير الفيديو"
        description="سجّل الشاشة مع الميكروفون والكاميرا، عدّل التسجيل ثم احفظه على جهازك. الفيديو والصوت لا يُرفعان إلى السيرفر."
        current="تسجيل الشاشة"
        icon="fa-display" />

    <section class="relative overflow-hidden bg-[radial-gradient(circle_at_10%_10%,rgba(124,58,237,0.09),transparent_28%),radial-gradient(circle_at_90%_30%,rgba(192,38,211,0.06),transparent_25%)] py-8 sm:py-12"
        data-screen-recorder data-config='@json($screenRecorderConfig)'>
        <div class="mx-auto w-[min(calc(100%_-_2rem),1480px)]">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-900"
                data-privacy-banner>
                <span class="inline-flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-emerald-600" aria-hidden="true"></i>
                    خصوصيتك أولًا: التسجيل والمشروع يبقيان داخل متصفحك وعلى جهازك.
                </span>
                <span class="inline-flex items-center gap-2 text-emerald-700">
                    <i class="fa-solid fa-hard-drive" aria-hidden="true"></i>
                    حفظ محلي تلقائي
                </span>
            </div>

            <section class="hidden rounded-3xl border border-amber-200 bg-white p-6 text-center shadow-xl shadow-amber-900/5 sm:p-10"
                data-unsupported-panel role="alert">
                <span class="mx-auto grid size-16 place-items-center rounded-3xl bg-amber-100 text-2xl text-amber-700">
                    <i class="fa-solid fa-laptop" aria-hidden="true"></i>
                </span>
                <h2 class="mt-5 text-2xl font-black text-slate-900">افتح الأداة من كمبيوتر باستخدام Chrome أو Edge</h2>
                <p class="mx-auto mt-3 max-w-2xl font-medium leading-8 text-slate-600" data-unsupported-message>
                    تحتاج الأداة إلى متصفح Chromium حديث واتصال HTTPS لتسجيل الشاشة ومعالجة الفيديو محليًا.
                </p>
            </section>

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,.65fr)]" data-start-panel>
                <div class="overflow-hidden rounded-[2rem] border border-violet-100 bg-white shadow-[0_24px_80px_rgba(76,29,149,0.1)]">
                    <div class="border-b border-violet-100 bg-linear-to-l from-violet-800 via-violet-700 to-fuchsia-700 p-6 text-white sm:p-9">
                        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-center">
                            <div>
                                <span class="inline-flex items-center gap-2 rounded-full bg-white/12 px-3 py-1.5 text-sm font-black ring-1 ring-white/20">
                                    <i class="fa-solid fa-circle-record text-rose-300" aria-hidden="true"></i>
                                    استوديو تسجيل محلي
                                </span>
                                <h2 class="mt-4 text-3xl font-black sm:text-4xl">ابدأ تسجيلًا واضحًا خلال ثوانٍ</h2>
                                <p class="mt-2 max-w-2xl font-medium leading-8 text-violet-100">اختر مصادر الصوت والكاميرا، ثم حدّد الشاشة أو النافذة من نافذة المتصفح.</p>
                            </div>
                            <span class="grid size-20 shrink-0 place-items-center rounded-3xl bg-white/10 text-3xl ring-1 ring-white/20" aria-hidden="true">
                                <i class="fa-solid fa-video"></i>
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-6 p-5 sm:p-8 lg:grid-cols-2">
                        <div class="grid gap-3">
                            <h3 class="text-lg font-black text-slate-900">مصادر التسجيل</h3>
                            <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 p-4 transition-colors has-checked:border-violet-400 has-checked:bg-violet-50">
                                <span class="flex items-center gap-3">
                                    <span class="grid size-10 place-items-center rounded-xl bg-violet-100 text-violet-700"><i class="fa-solid fa-microphone" aria-hidden="true"></i></span>
                                    <span><strong class="block">الميكروفون</strong><small class="text-slate-500">تعليقك الصوتي أثناء الشرح</small></span>
                                </span>
                                <input class="size-5 accent-violet-700" type="checkbox" data-record-microphone checked>
                            </label>
                            <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 p-4 transition-colors has-checked:border-violet-400 has-checked:bg-violet-50">
                                <span class="flex items-center gap-3">
                                    <span class="grid size-10 place-items-center rounded-xl bg-fuchsia-100 text-fuchsia-700"><i class="fa-solid fa-camera" aria-hidden="true"></i></span>
                                    <span><strong class="block">الكاميرا العائمة</strong><small class="text-slate-500">تظهر فوق تسجيل الشاشة</small></span>
                                </span>
                                <input class="size-5 accent-violet-700" type="checkbox" data-record-camera checked>
                            </label>
                            <div class="rounded-2xl border border-violet-200 bg-violet-50 p-4 text-sm font-medium leading-7 text-violet-950">
                                <i class="fa-solid fa-volume-high ms-1 text-violet-600" aria-hidden="true"></i>
                                اختر «مشاركة صوت النظام» من نافذة مشاركة الشاشة إن ظهر الخيار. توفره يعتمد على الشاشة أو النافذة المختارة.
                            </div>
                        </div>

                        <div class="grid content-start gap-4">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="font-black text-slate-800">المساحة المحلية المتاحة</span>
                                    <strong class="text-violet-700" data-storage-available>جارٍ الفحص…</strong>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200" aria-hidden="true">
                                    <span class="block h-full w-0 rounded-full bg-linear-to-l from-violet-700 to-fuchsia-500 transition-[width]" data-storage-bar></span>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-3 text-center text-sm">
                                    <div class="rounded-xl bg-white p-3"><span class="block text-slate-500">الحد الأقصى</span><strong data-safe-duration>60:00</strong></div>
                                    <div class="rounded-xl bg-white p-3"><span class="block text-slate-500">الحجم المتوقع</span><strong data-estimated-size>—</strong></div>
                                </div>
                            </div>

                            <button class="inline-flex min-h-14 items-center justify-center gap-3 rounded-2xl bg-linear-to-l from-rose-600 to-violet-700 px-7 text-lg font-black text-white shadow-xl shadow-violet-900/20 transition-transform hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-50 motion-reduce:transition-none"
                                type="button" data-start-recording>
                                <span class="size-3 rounded-full bg-white shadow-[0_0_0_5px_rgba(255,255,255,.2)]" aria-hidden="true"></span>
                                بدء تسجيل الشاشة
                            </button>
                            <p class="text-center text-xs font-bold leading-6 text-slate-500">سيطلب المتصفح إذن الشاشة في كل تسجيل. المدة القصوى 60 دقيقة.</p>
                        </div>
                    </div>
                </div>

                <aside class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] sm:p-6" aria-labelledby="localProjectsTitle">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-black text-violet-600">على هذا الجهاز فقط</p>
                            <h2 class="mt-1 text-2xl font-black text-slate-900" id="localProjectsTitle">مشاريعي المحلية</h2>
                        </div>
                        <span class="grid size-12 place-items-center rounded-2xl bg-violet-100 text-violet-700"><i class="fa-solid fa-folder-open" aria-hidden="true"></i></span>
                    </div>
                    <p class="mt-3 text-sm font-medium leading-7 text-slate-500">استكمل مشروعًا سابقًا أو احذفه لتحرير المساحة. مسح بيانات الموقع يحذف هذه المشاريع.</p>
                    <div class="mt-5 grid max-h-[480px] gap-3 overflow-y-auto" data-project-list></div>
                    <div class="mt-5 rounded-2xl border border-dashed border-slate-300 p-5 text-center text-sm font-bold text-slate-500" data-projects-empty>
                        لا توجد مشاريع محلية بعد.
                    </div>
                </aside>
            </section>

            <section class="hidden overflow-hidden rounded-[2rem] border border-rose-100 bg-[#080b17] text-white shadow-[0_30px_100px_rgba(15,23,42,.28)]" data-recording-panel>
                <header class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 px-5 py-4 sm:px-7">
                    <div class="flex items-center gap-3">
                        <span class="size-3 animate-pulse rounded-full bg-rose-500 motion-reduce:animate-none" data-recording-dot></span>
                        <div><strong class="block text-lg" data-recording-state>جارٍ التسجيل</strong><span class="text-sm text-slate-400" data-recording-hint>يُحفظ التسجيل محليًا كل ثانية</span></div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-xl bg-white/8 px-4 py-2 font-mono text-xl font-black tracking-wider" dir="ltr" data-recording-time>00:00</span>
                        <span class="rounded-xl bg-white/8 px-3 py-2 text-sm font-bold text-slate-300" data-recording-remaining>متبقي 60:00</span>
                    </div>
                </header>

                <div class="relative aspect-video w-full overflow-hidden bg-black" data-live-stage>
                    <video class="h-full w-full object-contain" data-live-screen autoplay muted playsinline></video>
                    <video class="absolute right-[3%] bottom-[4%] aspect-video w-[24%] min-w-40 rounded-2xl border-3 border-white bg-slate-900 object-cover shadow-2xl -scale-x-100"
                        data-live-camera autoplay muted playsinline hidden></video>
                    <div class="pointer-events-none absolute inset-0 grid place-items-center bg-black/65" data-countdown-overlay hidden>
                        <span class="grid size-32 place-items-center rounded-full bg-white/10 text-7xl font-black ring-2 ring-white/30 backdrop-blur" data-countdown-value>3</span>
                    </div>
                </div>

                <footer class="grid gap-4 border-t border-white/10 p-5 sm:grid-cols-[1fr_auto_1fr] sm:items-center sm:px-7">
                    <div class="grid gap-2 text-xs font-bold text-slate-300 sm:max-w-xs">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-desktop w-4 text-violet-300" aria-hidden="true"></i><span data-system-audio-status>فحص صوت النظام…</span></span>
                        <span class="flex items-center gap-2"><i class="fa-solid fa-microphone w-4 text-fuchsia-300" aria-hidden="true"></i><span class="h-1.5 flex-1 overflow-hidden rounded-full bg-white/10"><i class="block h-full w-0 rounded-full bg-fuchsia-400 transition-[width]" data-microphone-meter></i></span></span>
                    </div>
                    <div class="flex justify-center gap-3">
                        <button class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-white/10 px-5 font-black hover:bg-white/15" type="button" data-pause-recording>
                            <i class="fa-solid fa-pause" aria-hidden="true"></i><span>إيقاف مؤقت</span>
                        </button>
                        <button class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-rose-600 px-6 font-black shadow-lg shadow-rose-950/30 hover:bg-rose-500" type="button" data-stop-recording>
                            <i class="fa-solid fa-stop" aria-hidden="true"></i>إنهاء التسجيل
                        </button>
                    </div>
                    <p class="text-center text-xs font-bold leading-6 text-slate-400 sm:text-left">يمكنك إيقاف مشاركة الشاشة من شريط المتصفح أيضًا.</p>
                </footer>
            </section>

            <section class="hidden" data-editor-panel>
                <header class="mb-4 flex flex-col justify-between gap-4 rounded-2xl border border-violet-100 bg-white p-4 shadow-sm lg:flex-row lg:items-center">
                    <div class="flex min-w-0 items-center gap-3">
                        <button class="grid size-11 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200" type="button" data-back-to-projects aria-label="العودة إلى المشاريع"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                        <div class="min-w-0">
                            <label class="sr-only" for="screenRecorderProjectTitle">اسم المشروع</label>
                            <input class="w-full min-w-0 border-0 bg-transparent text-xl font-black text-slate-900 outline-none sm:text-2xl" id="screenRecorderProjectTitle" maxlength="80" data-project-title value="تسجيل شاشة جديد">
                            <p class="mt-0.5 text-xs font-bold text-emerald-600" data-save-status>محفوظ محليًا</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 font-black text-slate-700 hover:bg-slate-50 disabled:opacity-40" type="button" data-undo><i class="fa-solid fa-rotate-left" aria-hidden="true"></i>تراجع</button>
                        <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 font-black text-slate-700 hover:bg-slate-50 disabled:opacity-40" type="button" data-redo><i class="fa-solid fa-rotate-right" aria-hidden="true"></i>إعادة</button>
                    </div>
                </header>

                <div class="grid items-start justify-center gap-4 xl:grid-cols-[300px_minmax(0,820px)]" dir="ltr" data-editor-workspace>
                    <main class="min-w-0 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-[0_20px_60px_rgba(15,23,42,.08)] xl:col-start-2 xl:row-start-1" dir="rtl" data-preview-workspace>
                        <div class="bg-[#080b17] p-3 sm:p-4">
                            <div class="relative mx-auto aspect-video w-full max-w-[720px] overflow-hidden rounded-xl bg-black shadow-2xl" data-preview-stage>
                                <canvas class="h-full w-full" width="1280" height="720" data-editor-canvas></canvas>
                                <video data-screen-source playsinline hidden></video>
                                <video data-camera-source playsinline hidden muted></video>
                                <audio data-microphone-source hidden></audio>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <button class="grid size-11 place-items-center rounded-full bg-white text-slate-900 shadow" type="button" data-play-preview aria-label="تشغيل المعاينة"><i class="fa-solid fa-play" aria-hidden="true"></i></button>
                                    <span class="min-w-28 font-mono text-sm font-black text-white" dir="ltr"><span data-playhead-time>00:00</span> / <span data-total-time>00:00</span></span>
                                </div>
                                <span class="rounded-lg bg-white/8 px-3 py-2 text-xs font-bold text-slate-300" data-preview-quality>معاينة 1080p / 30fps</span>
                            </div>
                        </div>

                        <div class="bg-slate-50/80 p-3 sm:p-4">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-2.5 shadow-sm">
                                <div class="flex items-center gap-1">
                                    <button class="grid size-9 place-items-center rounded-lg text-slate-500 transition hover:bg-violet-50 hover:text-violet-700 disabled:opacity-30" type="button" data-timeline-undo aria-label="تراجع"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i></button>
                                    <button class="grid size-9 place-items-center rounded-lg text-slate-500 transition hover:bg-violet-50 hover:text-violet-700 disabled:opacity-30" type="button" data-timeline-redo aria-label="إعادة"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i></button>
                                    <span class="mx-1 h-6 w-px bg-slate-200" aria-hidden="true"></span>
                                    <span class="text-xs font-black text-slate-600">تكبير</span>
                                    <button class="grid size-8 place-items-center rounded-full border border-slate-200 text-slate-600 hover:border-violet-300 hover:text-violet-700" type="button" data-timeline-zoom-out aria-label="تصغير الخط الزمني"><i class="fa-solid fa-minus" aria-hidden="true"></i></button>
                                    <input class="w-20 accent-violet-700 sm:w-28" type="range" min="1" max="3" step="0.25" value="1" data-timeline-zoom aria-label="تكبير الخط الزمني">
                                    <button class="grid size-8 place-items-center rounded-full border border-slate-200 text-slate-600 hover:border-violet-300 hover:text-violet-700" type="button" data-timeline-zoom-in aria-label="تكبير الخط الزمني"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full bg-violet-50 px-3 py-1.5 text-xs font-black text-violet-700" data-selection-label>التسجيل كامل محدد</span>
                                    <span class="rounded-lg border border-violet-100 bg-violet-50 px-3 py-1.5 font-mono text-xs font-black text-violet-700" dir="ltr"><span data-timeline-current-time>00:00</span> / <span data-timeline-total-time>00:00</span></span>
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-timeline-section>
                                <div class="min-w-0 overflow-x-auto overscroll-x-contain" data-timeline-viewport dir="ltr">
                                    <div class="relative min-w-[640px]" data-timeline-content>
                                        <div class="relative flex h-9 items-end border-b border-slate-200 bg-slate-50 px-1 text-[10px] font-bold text-slate-500" data-timeline-ruler></div>
                                        <div class="relative cursor-crosshair" data-timeline-shell title="انقر لاختيار موضع التشغيل">
                                            <div class="pointer-events-none absolute inset-0 z-0 flex justify-between" data-timeline-grid aria-hidden="true"></div>
                                            <div class="relative z-10 flex h-[76px] gap-1 overflow-hidden bg-violet-50/40 p-1.5" data-timeline-segments></div>
                                            <span class="pointer-events-none absolute inset-y-0 z-20 hidden bg-violet-500/12 ring-1 ring-inset ring-violet-400/40" data-timeline-selection></span>
                                            <span class="pointer-events-none absolute -top-2 bottom-0 z-30 w-0.5 bg-violet-600 shadow-[0_0_0_1px_white]" data-timeline-playhead><i class="absolute -top-1.5 -left-[5px] block size-3 rotate-45 rounded-[2px] bg-violet-600"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <details class="group mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-overlay-section open>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-4 font-black text-slate-900 marker:content-none">
                                    <span class="flex items-center gap-2"><i class="fa-solid fa-layer-group text-violet-600" aria-hidden="true"></i>الإضافات</span>
                                    <span class="flex items-center gap-2">
                                        <span class="rounded-full bg-violet-50 px-3 py-1 text-xs text-violet-700" data-overlay-count>لا توجد إضافات</span>
                                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition group-open:rotate-180" aria-hidden="true"></i>
                                    </span>
                                </summary>

                                <div class="border-t border-slate-200 p-3 sm:p-4">
                                    <div class="flex flex-wrap items-center gap-2" data-overlay-add-controls role="toolbar" aria-label="إضافة عنصر جديد">
                                        <span class="text-xs font-black text-slate-500">إضافة</span>
                                        @foreach([['text', 'fa-font', 'نص'], ['arrow', 'fa-arrow-left-long', 'سهم'], ['blur', 'fa-eye-slash', 'تمويه']] as [$type, $icon, $label])
                                            <button class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-4 text-sm font-black text-violet-800 transition hover:border-violet-300 hover:bg-violet-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600" type="button" data-add-overlay="{{ $type }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i>{{ $label }}</button>
                                        @endforeach
                                    </div>

                                    <div class="mt-3 grid gap-2" data-overlay-list></div>

                                    <template data-overlay-item-template>
                                        <article class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 transition hover:border-violet-200 sm:flex-row sm:items-center sm:justify-between" data-overlay-item>
                                            <div class="flex min-w-0 items-center gap-3">
                                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-violet-100 text-violet-700" data-overlay-icon><i class="fa-solid fa-shapes" aria-hidden="true"></i></span>
                                                <span class="min-w-0">
                                                    <strong class="block truncate text-sm font-black text-slate-900" data-overlay-name></strong>
                                                    <span class="mt-1 block font-mono text-xs font-bold text-slate-500" dir="ltr" data-overlay-time></span>
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 sm:flex sm:shrink-0">
                                                <button class="inline-flex min-h-9 items-center justify-center gap-2 rounded-lg border border-violet-200 bg-white px-3 text-xs font-black text-violet-700 hover:bg-violet-50" type="button" data-overlay-edit><i class="fa-solid fa-pen" aria-hidden="true"></i>تعديل</button>
                                                <button class="inline-flex min-h-9 items-center justify-center gap-2 rounded-lg border border-rose-200 bg-white px-3 text-xs font-black text-rose-700 hover:bg-rose-50" type="button" data-overlay-delete><i class="fa-solid fa-trash-can" aria-hidden="true"></i>حذف</button>
                                            </div>
                                        </article>
                                    </template>
                                </div>
                            </details>

                            <label class="sr-only" for="screenRecorderPlayhead">موضع التشغيل</label>
                            <input class="sr-only" id="screenRecorderPlayhead" type="range" min="0" max="1" step="0.01" value="0" data-playhead>

                            <div class="mt-5 grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2">
                                <label class="grid gap-2 text-sm font-black text-slate-700">بداية الجزء
                                    <span class="flex items-center gap-3"><input class="min-w-0 flex-1 accent-violet-700" type="range" min="0" max="1" step="0.01" value="0" data-selection-start><output class="min-w-14 text-left font-mono text-violet-700" dir="ltr" data-selection-start-output>00:00</output></span>
                                </label>
                                <label class="grid gap-2 text-sm font-black text-slate-700">نهاية الجزء
                                    <span class="flex items-center gap-3"><input class="min-w-0 flex-1 accent-violet-700" type="range" min="0" max="1" step="0.01" value="1" data-selection-end><output class="min-w-14 text-left font-mono text-violet-700" dir="ltr" data-selection-end-output>00:00</output></span>
                                </label>
                            </div>

                            <div class="mt-3 grid gap-2 sm:grid-cols-3" role="toolbar" aria-label="أدوات القص">
                                <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-violet-700 px-4 text-sm font-black text-white hover:bg-violet-800" type="button" data-edit-action="apply-trim"><i class="fa-solid fa-crop-simple" aria-hidden="true"></i>تطبيق القص</button>
                                <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 text-sm font-black text-rose-700 hover:bg-rose-100" type="button" data-edit-action="delete-range"><i class="fa-solid fa-trash-can" aria-hidden="true"></i>حذف الجزء المحدد</button>
                                <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 hover:border-violet-300 hover:bg-violet-50" type="button" data-edit-action="split"><i class="fa-solid fa-scissors" aria-hidden="true"></i>تقسيم عند المؤشر</button>
                            </div>

                            <p class="mt-3 text-xs font-bold leading-6 text-slate-500"><strong class="text-violet-700">تطبيق القص</strong> يحذف ما قبل البداية وما بعد النهاية، ويبقي الجزء الواقع بين المؤشرين فقط. يمكنك التراجع مباشرة.</p>
                        </div>
                    </main>

                    <aside class="grid min-w-0 content-start gap-3 xl:col-start-1 xl:row-start-1" dir="rtl" data-settings-panel>
                        <div class="rounded-2xl border border-violet-100 bg-white p-4 shadow-sm">
                            <h2 class="font-black text-slate-900"><i class="fa-solid fa-sliders ms-2 text-violet-600" aria-hidden="true"></i>إعدادات الفيديو</h2>
                            <p class="mt-1 text-xs font-bold leading-6 text-slate-500">تحكم في الإطار والكاميرا والصوت من مكان واحد.</p>
                        </div>
                        <details class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <summary class="cursor-pointer list-none font-black text-slate-900"><i class="fa-solid fa-crop-simple ms-2 text-violet-600" aria-hidden="true"></i>قص إطار الشاشة</summary>
                            <div class="mt-4 grid grid-cols-2 gap-3 text-sm font-bold">
                                @foreach([['x', 'من اليمين'], ['y', 'من الأعلى'], ['width', 'العرض'], ['height', 'الارتفاع']] as [$field, $label])
                                    <label class="grid gap-1.5 text-slate-600">{{ $label }}<input class="w-full accent-violet-700" type="range" min="0" max="100" value="{{ in_array($field, ['width', 'height']) ? 100 : 0 }}" data-crop-field="{{ $field }}"></label>
                                @endforeach
                            </div>
                            <button class="mt-4 w-full rounded-xl bg-slate-100 px-3 py-2 text-sm font-black text-slate-700 hover:bg-slate-200" type="button" data-reset-crop>إعادة الإطار كاملًا</button>
                        </details>

                        <details class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" data-camera-settings>
                            <summary class="cursor-pointer list-none font-black text-slate-900"><i class="fa-solid fa-camera ms-2 text-fuchsia-600" aria-hidden="true"></i>الكاميرا العائمة</summary>
                            <div class="mt-4 grid gap-3 text-sm font-bold">
                                <label class="flex items-center justify-between gap-3 text-slate-700">إظهار الكاميرا<input class="size-5 accent-violet-700" type="checkbox" data-camera-visible checked></label>
                                <label class="grid gap-1.5 text-slate-600">الحجم<input class="w-full accent-violet-700" type="range" min="12" max="45" value="24" data-camera-size></label>
                                <label class="grid gap-1.5 text-slate-600">الشكل<select class="min-h-10 rounded-xl border border-slate-200 bg-white px-3" data-camera-shape><option value="rounded">مستطيل مستدير</option><option value="circle">دائري</option><option value="square">مربع</option></select></label>
                                <label class="flex items-center justify-between gap-3 text-slate-700">عكس الكاميرا<input class="size-5 accent-violet-700" type="checkbox" data-camera-mirror checked></label>
                                <p class="text-xs font-medium leading-6 text-slate-500">اسحب الكاميرا مباشرة داخل المعاينة لتغيير مكانها.</p>
                            </div>
                        </details>

                        <details class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <summary class="cursor-pointer list-none font-black text-slate-900"><i class="fa-solid fa-sliders ms-2 text-emerald-600" aria-hidden="true"></i>مزج الصوت</summary>
                            <div class="mt-4 grid gap-4 text-sm font-bold">
                                <label class="grid gap-1.5 text-slate-600">صوت النظام <span class="flex items-center gap-2"><input class="min-w-0 flex-1 accent-violet-700" type="range" min="0" max="200" value="100" data-system-gain><output data-system-gain-output>100%</output></span></label>
                                <label class="flex items-center justify-between gap-3 text-slate-700">كتم صوت النظام<input class="size-5 accent-violet-700" type="checkbox" data-system-muted></label>
                                <label class="grid gap-1.5 text-slate-600">الميكروفون <span class="flex items-center gap-2"><input class="min-w-0 flex-1 accent-violet-700" type="range" min="0" max="200" value="100" data-microphone-gain><output data-microphone-gain-output>100%</output></span></label>
                                <label class="flex items-center justify-between gap-3 text-slate-700">كتم الميكروفون<input class="size-5 accent-violet-700" type="checkbox" data-microphone-muted></label>
                            </div>
                        </details>

                    </aside>
                </div>

                <footer class="sticky bottom-3 z-30 mt-4 flex flex-col justify-between gap-3 rounded-2xl border border-violet-100 bg-white/95 p-3 shadow-[0_18px_55px_rgba(76,29,149,.14)] backdrop-blur sm:flex-row sm:items-center" data-editor-actionbar>
                    <p class="text-xs font-bold leading-6 text-slate-500"><i class="fa-solid fa-shield-halved ms-2 text-emerald-600" aria-hidden="true"></i>التعديلات تُحفظ محليًا تلقائيًا ولا يُرفع الفيديو إلى السيرفر.</p>
                    <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-violet-700 px-7 font-black text-white shadow-lg shadow-violet-900/15 hover:bg-violet-800" type="button" data-export-video><i class="fa-solid fa-download" aria-hidden="true"></i>تصدير بجودة عالية</button>
                </footer>
            </section>
        </div>

        <dialog class="w-[min(calc(100%_-_2rem),540px)] rounded-3xl border-0 bg-white p-0 shadow-2xl backdrop:bg-slate-950/65" data-overlay-create-dialog>
            <form class="p-5 sm:p-7" method="dialog" data-overlay-create-form>
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="grid size-12 place-items-center rounded-2xl bg-violet-100 text-xl text-violet-700" data-overlay-create-icon><i class="fa-solid fa-shapes" aria-hidden="true"></i></span>
                        <div>
                            <p class="text-xs font-black text-violet-600" data-overlay-create-mode>إضافة جديدة</p>
                            <h2 class="mt-1 text-xl font-black text-slate-900" data-overlay-create-title>إضافة نص</h2>
                        </div>
                    </div>
                    <button class="grid size-10 place-items-center rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200" type="button" data-overlay-create-close aria-label="إغلاق"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>

                <div class="mt-6 grid gap-4">
                    <label class="grid gap-1.5 text-sm font-black text-slate-700" data-overlay-create-text-field>النص
                        <input class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 outline-none focus:border-violet-400 focus:ring-3 focus:ring-violet-100" maxlength="160" value="نص توضيحي" data-overlay-create-text>
                    </label>
                    <label class="hidden grid gap-1.5 text-sm font-black text-slate-700" data-overlay-create-direction-field>اتجاه السهم
                        <select class="min-h-11 rounded-xl border border-slate-200 bg-white px-3" data-overlay-create-direction><option value="left">يسار</option><option value="right">يمين</option><option value="up">أعلى</option><option value="down">أسفل</option></select>
                    </label>
                    <label class="hidden grid gap-1.5 text-sm font-black text-slate-700" data-overlay-create-blur-field>قوة التمويه
                        <span class="flex items-center gap-3"><input class="min-w-0 flex-1 accent-violet-700" type="range" min="4" max="40" value="16" data-overlay-create-blur><output class="min-w-10 font-mono text-violet-700" data-overlay-create-blur-output>16</output></span>
                    </label>
                    <label class="grid gap-1.5 text-sm font-black text-slate-700" data-overlay-create-color-field>اللون
                        <input class="h-11 w-full rounded-xl border border-slate-200 bg-white p-1" type="color" value="#6d28d9" data-overlay-create-color>
                    </label>

                    <fieldset class="grid gap-4 rounded-2xl border border-violet-100 bg-violet-50/60 p-4">
                        <legend class="px-2 text-sm font-black text-violet-950">فترة الظهور</legend>
                        <label class="grid gap-2 text-sm font-black text-slate-700">يبدأ عند
                            <span class="flex items-center gap-3"><input class="min-w-0 flex-1 accent-violet-700" type="range" min="0" max="1" step="0.01" value="0" data-overlay-create-start><output class="min-w-14 text-left font-mono text-violet-700" dir="ltr" data-overlay-create-start-output>00:00</output></span>
                        </label>
                        <label class="grid gap-2 text-sm font-black text-slate-700">ينتهي عند
                            <span class="flex items-center gap-3"><input class="min-w-0 flex-1 accent-violet-700" type="range" min="0" max="1" step="0.01" value="1" data-overlay-create-end><output class="min-w-14 text-left font-mono text-violet-700" dir="ltr" data-overlay-create-end-output>00:00</output></span>
                        </label>
                    </fieldset>
                    <p class="hidden rounded-xl bg-rose-50 px-3 py-2 text-sm font-bold text-rose-700" data-overlay-create-error></p>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button class="min-h-11 rounded-xl border border-slate-200 bg-white px-4 font-black text-slate-700 hover:bg-slate-50" type="button" data-overlay-create-cancel>إلغاء</button>
                    <button class="min-h-11 rounded-xl bg-violet-700 px-4 font-black text-white shadow-lg shadow-violet-200 hover:bg-violet-800" type="submit" data-overlay-create-submit>إضافة إلى الفيديو</button>
                </div>
            </form>
        </dialog>

        <dialog class="w-[min(calc(100%_-_2rem),560px)] rounded-3xl border-0 bg-white p-0 shadow-2xl backdrop:bg-slate-950/65" data-export-dialog>
            <div class="p-6 sm:p-8">
                <div data-export-ready>
                    <span class="grid size-14 place-items-center rounded-2xl bg-violet-100 text-2xl text-violet-700"><i class="fa-solid fa-download" aria-hidden="true"></i></span>
                    <h2 class="mt-5 text-2xl font-black text-slate-900">احفظ الفيديو على جهازك</h2>
                    <p class="mt-2 text-sm font-medium leading-7 text-slate-500">اختر مكان الحفظ، وسنجهّز الملف محليًا دون رفع التسجيل إلى الإنترنت.</p>
                    <div class="mt-5 grid grid-cols-2 gap-3 rounded-2xl bg-violet-50 p-4 text-sm">
                        <div><span class="block font-bold text-slate-500">نوع الملف</span><strong class="mt-1 block text-violet-800" data-export-format>جارٍ الفحص…</strong></div>
                        <div><span class="block font-bold text-slate-500">الحجم المتوقع</span><strong class="mt-1 block text-violet-800" data-export-size>—</strong></div>
                    </div>
                    <div class="mt-6 grid gap-2 sm:grid-cols-[1fr_auto]">
                        <button class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-violet-700 px-6 font-black text-white hover:bg-violet-800 disabled:cursor-not-allowed disabled:opacity-50" type="button" data-start-export><i class="fa-solid fa-folder-open" aria-hidden="true"></i>اختيار مكان الحفظ والبدء</button>
                        <button class="min-h-12 rounded-xl border border-slate-200 bg-white px-5 font-black text-slate-700 hover:bg-slate-50" type="button" data-close-export>رجوع</button>
                    </div>
                </div>

                <div class="hidden text-center" data-export-working>
                    <span class="mx-auto grid size-16 place-items-center rounded-3xl bg-violet-100 text-2xl text-violet-700"><i class="fa-solid fa-gears" aria-hidden="true"></i></span>
                    <h2 class="mt-5 text-2xl font-black text-slate-900">جارٍ تجهيز الفيديو على جهازك</h2>
                    <p class="mt-2 text-sm font-medium leading-7 text-slate-500" data-export-message>يتم تركيب أجزاء الفيديو والإضافات ثم ترميز الملف. يمكنك متابعة العمل في نافذة أخرى.</p>
                    <div class="mt-6 h-3 overflow-hidden rounded-full bg-slate-100"><span class="block h-full w-0 rounded-full bg-linear-to-l from-violet-700 to-fuchsia-500 transition-[width]" data-export-progress></span></div>
                    <div class="mt-3 flex items-center justify-between text-sm font-black"><span data-export-stage>تهيئة التصدير…</span><span class="text-violet-700" data-export-percent>0%</span></div>
                    <button class="mt-6 min-h-11 rounded-xl border border-slate-200 bg-white px-5 font-black text-slate-700 hover:bg-slate-50" type="button" data-cancel-export>إلغاء التصدير</button>
                </div>
            </div>
        </dialog>

        <div class="pointer-events-none fixed bottom-5 left-1/2 z-100 w-[min(calc(100%_-_2rem),520px)] -translate-x-1/2 translate-y-4 rounded-2xl bg-slate-950 px-5 py-4 text-center text-sm font-bold text-white opacity-0 shadow-2xl transition-[opacity,transform]"
            data-recorder-toast role="status" aria-live="polite"></div>
    </section>
@endsection
