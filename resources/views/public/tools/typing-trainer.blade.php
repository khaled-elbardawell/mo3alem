@extends('layouts.public', ['activeNavigation' => 'typing-trainer'])

@section('content')
    <x-public.tool-page-header title="مدرب الطباعة بالعربية والإنجليزية"
        description="تعلم الكتابة باللمس عبر دروس متدرجة، لوحة مفاتيح تفاعلية، وقياس فوري للسرعة والدقة."
        current="مدرب الطباعة" icon="fa-keyboard" />

    <section class="relative min-h-[calc(100vh-5rem)] overflow-hidden bg-[#f7f7fc] py-7 sm:py-10" data-typing-trainer>
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_8%_4%,rgba(124,58,237,0.11),transparent_25%),radial-gradient(circle_at_92%_16%,rgba(14,165,233,0.09),transparent_24%)]" aria-hidden="true"></div>

        <div class="relative mx-auto w-[min(calc(100%_-_1.25rem),1380px)] sm:w-[min(calc(100%_-_2rem),1380px)]">
            <div class="grid items-start gap-4 lg:grid-cols-[290px_minmax(0,1fr)]">
                <aside class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_20px_55px_rgba(30,41,59,0.08)] lg:sticky lg:top-24">
                    <div class="border-b border-slate-100 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-black text-violet-700">مسار التعلم</p>
                                <h2 class="mt-1 text-lg font-black text-[#111a35]">اختر لغة التدريب</h2>
                            </div>
                            <span class="grid size-10 place-items-center rounded-xl bg-violet-50 text-violet-700" aria-hidden="true"><i class="fa-solid fa-route"></i></span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 rounded-xl bg-slate-100 p-1" role="group" aria-label="لغة التدريب">
                            <button class="typing-language-button" type="button" data-typing-language="ar" aria-pressed="true"><span>ع</span> العربية</button>
                            <button class="typing-language-button" type="button" data-typing-language="en" aria-pressed="false"><span>En</span> English</button>
                        </div>
                        <div class="mt-3 grid grid-cols-4 divide-x divide-x-reverse divide-slate-200 rounded-xl border border-slate-100 bg-slate-50 py-2 text-center" aria-label="ملخص التقدم">
                            <span><b class="block text-sm font-black text-slate-800" data-typing-completed>0</b><small class="text-[9px] font-bold text-slate-400">دروس</small></span>
                            <span><b class="block text-sm font-black text-amber-500" data-typing-stars>0</b><small class="text-[9px] font-bold text-slate-400">نجوم</small></span>
                            <span><b class="block text-sm font-black text-sky-700" data-typing-best>0</b><small class="text-[9px] font-bold text-slate-400">WPM</small></span>
                            <span><b class="block text-sm font-black text-orange-600" data-typing-streak>0</b><small class="text-[9px] font-bold text-slate-400">سلسلة</small></span>
                        </div>
                    </div>
                    <div class="max-h-[600px] overflow-y-auto p-3" data-typing-lessons aria-label="دروس الطباعة"></div>
                </aside>

                <main class="min-w-0 rounded-3xl border border-slate-200/80 bg-white shadow-[0_20px_55px_rgba(30,41,59,0.08)]">
                    <header class="flex flex-col gap-4 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-black">
                                <span class="rounded-full bg-violet-100 px-3 py-1 text-violet-700" data-typing-lesson-number>الدرس 1</span>
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-700">الهدف: <b data-typing-goal>8 WPM</b></span>
                            </div>
                            <h2 class="mt-2 text-2xl font-black tracking-tight text-[#111a35]" data-typing-lesson-title>نقطة البداية</h2>
                            <p class="mt-1 text-sm font-bold text-slate-500" data-typing-lesson-subtitle>الحرفان ت و ن</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-black text-slate-600 transition-colors hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700" type="button" data-typing-sound-toggle aria-pressed="false" aria-label="كتم صوت الكتابة">
                                <i class="fa-solid fa-volume-high" data-typing-sound-icon aria-hidden="true"></i><span data-typing-sound-label>كتم الصوت</span>
                            </button>
                            <button class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-black text-slate-600 transition-colors hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700" type="button" data-typing-focus>
                                <i class="fa-solid fa-expand" aria-hidden="true"></i><span>وضع التركيز</span>
                            </button>
                        </div>
                    </header>

                    <section class="p-4 sm:p-6" data-typing-intro>
                        <div class="grid items-center gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(300px,0.85fr)]">
                            <figure class="overflow-hidden rounded-2xl border border-slate-200 bg-linear-to-br from-slate-50 to-violet-50 p-3 sm:p-5">
                                <img class="mx-auto aspect-3/2 w-full max-w-3xl object-contain" src="{{ asset('images/typing-trainer/home-row-hands.png') }}" alt="الوضعية الصحيحة لليدين على صف الارتكاز في لوحة المفاتيح">
                                <figcaption class="mt-3 text-center text-xs font-bold text-slate-500">ضع السبابتين على مفتاحي <b class="text-violet-700">F</b> و <b class="text-violet-700">J</b>، واترك باقي الأصابع على صف الارتكاز.</figcaption>
                            </figure>

                            <div>
                                <span class="inline-flex items-center gap-2 rounded-full bg-violet-100 px-3 py-1.5 text-xs font-black text-violet-700"><i class="fa-solid fa-hand" aria-hidden="true"></i> استعد للدرس</span>
                                <h3 class="mt-3 text-2xl font-black tracking-tight text-[#111a35]" data-typing-guide-title>وضعية اليد الصحيحة</h3>
                                <p class="mt-2 font-bold leading-7 text-slate-500" data-typing-guide-description>قبل البدء، ثبّت أصابعك على صف الارتكاز وحافظ على راحة اليدين.</p>

                                <div class="mt-4 rounded-xl border border-violet-100 bg-violet-50 px-4 py-3">
                                    <span class="text-xs font-bold text-violet-600">محور هذا الدرس</span>
                                    <strong class="mt-1 block text-lg font-black text-violet-900" data-typing-guide-focus>الحرفان ت و ن</strong>
                                </div>

                                <ol class="mt-4 grid gap-2.5 text-sm font-bold text-slate-600">
                                    <li class="flex items-start gap-3 rounded-xl bg-slate-50 p-3"><span class="grid size-7 shrink-0 place-items-center rounded-lg bg-violet-700 text-xs text-white">1</span><span>أبقِ المعصمين مرتاحين والأصابع منحنية قليلًا فوق الأزرار.</span></li>
                                    <li class="flex items-start gap-3 rounded-xl bg-slate-50 p-3"><span class="grid size-7 shrink-0 place-items-center rounded-lg bg-sky-600 text-xs text-white">2</span><span>أعد كل إصبع إلى مكانه على صف الارتكاز بعد كتابة الحرف.</span></li>
                                    <li class="flex items-start gap-3 rounded-xl bg-slate-50 p-3"><span class="grid size-7 shrink-0 place-items-center rounded-lg bg-emerald-600 text-xs text-white">3</span><span>أبقِ عينيك على الشاشة، وابدأ بالدقة قبل محاولة زيادة السرعة.</span></li>
                                </ol>

                                <button class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-violet-700 px-6 font-black text-white shadow-lg shadow-violet-900/15 transition hover:-translate-y-0.5 hover:bg-violet-800" type="button" data-typing-start>
                                    ابدأ التدريب
                                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                                </button>
                                <p class="mt-2 text-center text-[11px] font-bold text-slate-400">يبدأ احتساب الوقت مع أول حرف تكتبه.</p>
                            </div>
                        </div>
                    </section>

                    <div class="p-4 sm:p-6" data-typing-practice hidden>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
                            <div class="typing-live-stat" data-typing-stat-card="wpm"><span>السرعة</span><strong><b data-typing-wpm>0</b> <small class="text-[10px]">WPM</small></strong></div>
                            <div class="typing-live-stat" data-typing-stat-card="accuracy"><span>الدقة</span><strong data-typing-accuracy>100%</strong></div>
                            <div class="typing-live-stat" data-typing-stat-card="time"><span>الوقت</span><strong data-typing-time>00:00</strong></div>
                            <div class="typing-live-stat" data-typing-stat-card="cpm"><span>الأحرف</span><strong><b data-typing-cpm>0</b> <small class="text-[10px]">CPM</small></strong></div>
                            <div class="typing-live-stat col-span-2 sm:col-span-1" data-typing-stat-card="errors"><span>الأخطاء</span><strong class="text-rose-600" data-typing-errors>0</strong></div>
                        </div>

                        <div class="mt-5 flex items-center gap-3">
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100"><span class="block h-full w-0 rounded-full bg-linear-to-l from-violet-600 to-sky-500 transition-[width] duration-200" data-typing-progress-bar></span></div>
                            <span class="w-10 text-left text-xs font-black text-slate-500" data-typing-progress-label>0%</span>
                        </div>

                        <section class="mt-5 rounded-2xl border-2 border-slate-100 bg-slate-50/70 px-4 py-7 text-center transition-colors focus-within:border-violet-300 sm:px-8 sm:py-10" aria-labelledby="typing-instruction">
                            <p class="text-xs font-black text-violet-700" id="typing-instruction"><i class="fa-solid fa-eye ml-1" aria-hidden="true"></i> انظر إلى النص ولا تنظر إلى لوحة المفاتيح</p>
                            <div class="typing-target-text mt-5 max-h-44 overflow-y-auto text-[clamp(1.35rem,3vw,2.15rem)] leading-[2.05] font-black tracking-wide text-slate-400 outline-none" data-typing-text tabindex="0" role="textbox" aria-label="نص التدريب"></div>
                            <input class="fixed bottom-0 left-0 size-px opacity-0" type="text" data-typing-capture autocomplete="off" autocapitalize="off" spellcheck="false" aria-label="حقل التقاط الكتابة">
                        </section>

                        <div class="mt-3 flex min-h-10 items-center justify-center rounded-xl bg-violet-50 px-4 text-center text-sm font-bold text-violet-800" data-typing-status role="status" aria-live="polite">ضع أصابعك على الصف الأوسط، ثم ابدأ عندما تكون جاهزًا.</div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                            <div class="flex items-center gap-3">
                                <span class="grid size-8 place-items-center rounded-full bg-violet-100 text-[10px] font-black text-violet-700" data-typing-finger-badge>س ي</span>
                                <span class="text-xs font-bold text-slate-500">الإصبع التالي: <strong class="text-slate-800" data-typing-finger-name>السبابة اليمنى</strong></span>
                            </div>
                            <button class="inline-flex min-h-9 items-center gap-2 rounded-lg bg-white px-3 text-xs font-black text-violet-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-violet-50" type="button" data-typing-keyboard-toggle aria-expanded="false" aria-controls="typingKeyboardPanel">
                                <i class="fa-solid fa-keyboard" aria-hidden="true"></i>
                                <span>إظهار لوحة المفاتيح</span>
                                <i class="fa-solid fa-chevron-down text-[9px] transition-transform" data-typing-keyboard-chevron aria-hidden="true"></i>
                            </button>
                        </div>

                        <section class="mt-3 overflow-x-auto rounded-2xl border border-slate-200 bg-[#f8fafc] p-3 sm:p-5" id="typingKeyboardPanel" data-typing-keyboard-panel aria-label="لوحة المفاتيح التفاعلية" hidden>
                            <div class="mx-auto min-w-[720px] max-w-[980px]" data-typing-keyboard></div>
                        </section>

                        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5">
                            <button class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-black text-slate-600 transition hover:bg-slate-50" type="button" data-typing-reset><i class="fa-solid fa-rotate-right" aria-hidden="true"></i> إعادة التمرين</button>
                            <button class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#111a35] px-5 text-sm font-black text-white transition hover:bg-violet-800 disabled:cursor-not-allowed disabled:opacity-35" type="button" data-typing-next>الدرس التالي <i class="fa-solid fa-arrow-left" aria-hidden="true"></i></button>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <dialog class="w-[min(calc(100%_-_2rem),520px)] rounded-3xl border-0 bg-white p-0 text-right shadow-[0_30px_100px_rgba(15,23,42,0.3)] backdrop:bg-slate-950/55 backdrop:backdrop-blur-sm" data-typing-result-dialog>
            <div class="relative overflow-hidden p-6 sm:p-8">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-32 bg-linear-to-b from-violet-100 to-transparent" aria-hidden="true"></div>
                <form class="absolute top-4 left-4" method="dialog"><button class="grid size-10 place-items-center rounded-full bg-white text-slate-500 shadow-sm hover:text-violet-700" aria-label="إغلاق النتيجة"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></form>
                <div class="relative text-center">
                    <span class="mx-auto grid size-18 place-items-center rounded-3xl bg-violet-700 text-3xl text-white shadow-xl shadow-violet-900/20"><i class="fa-solid fa-trophy" aria-hidden="true"></i></span>
                    <h2 class="mt-4 text-2xl font-black text-[#111a35]">أحسنت، أنهيت التمرين!</h2>
                    <div class="typing-result-stars mt-3 flex justify-center gap-2 text-2xl text-slate-200" data-result-stars aria-label="نجوم النتيجة"></div>
                    <p class="mt-3 font-bold leading-7 text-slate-500" data-result-message></p>
                </div>
                <div class="relative mt-6 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl bg-slate-50 p-3 text-center"><span class="block text-xs font-bold text-slate-400">السرعة</span><strong class="mt-1 block text-xl font-black text-violet-700"><b data-result-wpm>0</b> <small class="text-[10px]">WPM</small></strong></div>
                    <div class="rounded-2xl bg-slate-50 p-3 text-center"><span class="block text-xs font-bold text-slate-400">الدقة</span><strong class="mt-1 block text-xl font-black text-emerald-600" data-result-accuracy>100%</strong></div>
                    <div class="rounded-2xl bg-slate-50 p-3 text-center"><span class="block text-xs font-bold text-slate-400">الوقت</span><strong class="mt-1 block text-xl font-black text-slate-800" data-result-time>00:00</strong></div>
                </div>
                <div class="relative mt-6 grid grid-cols-2 gap-3">
                    <button class="min-h-12 rounded-xl border border-violet-200 font-black text-violet-700 hover:bg-violet-50" type="button" data-result-retry><i class="fa-solid fa-rotate-right ml-1" aria-hidden="true"></i> أعد المحاولة</button>
                    <button class="min-h-12 rounded-xl bg-violet-700 font-black text-white hover:bg-violet-800" type="button" data-result-next>الدرس التالي <i class="fa-solid fa-arrow-left mr-1" aria-hidden="true"></i></button>
                </div>
            </div>
        </dialog>
    </section>
@endsection
