@extends('layouts.public', ['activeNavigation' => 'tools'])

@section('content')
    <x-public.tool-page-header
        title="مدرب الطباعة العربي والإنجليزي"
        description="اختر اللغة، اكتب الكلمات المظللة، واكتشف سرعتك ودقتك في نهاية كل تدريب."
        current="مدرب الطباعة"
        icon="fa-keyboard" />

    @if($campaigns['top'])
        <section class="mx-auto w-[min(calc(100%_-_2rem),1040px)] py-6">
            <x-public.advertisement :campaign="$campaigns['top']" />
        </section>
    @endif

    <section class="mx-auto w-[min(calc(100%_-_2rem),1180px)] py-8 sm:py-12">
        <div @class(['grid items-start gap-6', 'lg:grid-cols-[minmax(0,1fr)_260px]' => $campaigns['side']])>
            <div
                class="overflow-hidden rounded-[2rem] border border-violet-100 bg-white shadow-[0_24px_70px_rgba(76,29,149,0.1)]"
                data-typing-trainer>
                <script type="application/json" data-typing-trainer-config>@json($typingTrainerConfig)</script>

                <div class="border-b border-slate-100 bg-linear-to-l from-violet-50 via-white to-indigo-50 px-5 py-7 sm:px-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-black text-violet-700">تدريب سريع وواضح</p>
                            <h2 class="mt-1 text-2xl font-black text-[#111a35]">طوّر سرعتك كلمة بعد كلمة</h2>
                        </div>
                        <div class="flex items-center gap-2 text-sm font-bold text-slate-500">
                            <i class="fa-regular fa-circle-question text-violet-600" aria-hidden="true"></i>
                            يبدأ الوقت مع أول حرف
                        </div>
                    </div>
                </div>

                <div class="p-5 sm:p-8 lg:p-10">
                    <section class="mx-auto max-w-3xl text-center" data-typing-language-selection>
                        <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-violet-100 text-3xl text-violet-700">
                            <i class="fa-solid fa-language" aria-hidden="true"></i>
                        </span>
                        <h3 class="mt-5 text-2xl font-black text-slate-900">اختر لغة التدريب</h3>
                        <p class="mx-auto mt-2 max-w-xl font-medium leading-7 text-slate-500">ستظهر فقرة تعليمية قصيرة وعشوائية، ويمكنك تغيير اللغة بعد كل محاولة.</p>
                        <div class="mt-7 grid gap-4 sm:grid-cols-2">
                            <button class="group min-h-36 rounded-3xl border-2 border-violet-100 bg-violet-50/50 p-5 text-right transition hover:-translate-y-1 hover:border-violet-400 hover:bg-violet-50 focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-violet-600" type="button" data-typing-language="ar">
                                <span class="grid size-11 place-items-center rounded-xl bg-violet-700 text-lg font-black text-white">ض</span>
                                <strong class="mt-4 block text-xl text-slate-900">اللغة العربية</strong>
                                <span class="mt-1 block text-sm font-medium text-slate-500">فقرة عربية فصحى دون تشكيل</span>
                            </button>
                            <button class="group min-h-36 rounded-3xl border-2 border-indigo-100 bg-indigo-50/50 p-5 text-left transition hover:-translate-y-1 hover:border-indigo-400 hover:bg-indigo-50 focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-indigo-600" type="button" dir="ltr" data-typing-language="en">
                                <span class="grid size-11 place-items-center rounded-xl bg-indigo-700 text-lg font-black text-white">A</span>
                                <strong class="mt-4 block text-xl text-slate-900">English</strong>
                                <span class="mt-1 block text-sm font-medium text-slate-500">A short educational paragraph</span>
                            </button>
                        </div>
                    </section>

                    <div class="hidden min-h-64 place-items-center text-center" data-typing-loading aria-live="polite">
                        <div>
                            <i class="fa-solid fa-spinner fa-spin text-4xl text-violet-600" aria-hidden="true"></i>
                            <p class="mt-4 font-bold text-slate-600">جار تحضير فقرة التدريب...</p>
                        </div>
                    </div>

                    <section class="hidden" data-typing-test>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl bg-violet-50 p-4 text-center">
                                <span class="block text-xs font-bold text-violet-600">الوقت المتبقي</span>
                                <strong class="mt-1 block text-3xl font-black tabular-nums text-violet-900" data-typing-timer aria-live="polite">00:00</strong>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 text-center">
                                <span class="block text-xs font-bold text-slate-500">التقدم</span>
                                <strong class="mt-1 block text-3xl font-black tabular-nums text-slate-900" data-typing-progress>0 / 0</strong>
                            </div>
                            <div class="rounded-2xl bg-emerald-50 p-4 text-center">
                                <span class="block text-xs font-bold text-emerald-600">الصحيح</span>
                                <strong class="mt-1 block text-3xl font-black tabular-nums text-emerald-800" data-typing-correct>0</strong>
                            </div>
                        </div>

                        <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50/70 p-5 sm:p-7">
                            <p class="flex flex-wrap justify-center gap-x-2 gap-y-3 text-[clamp(1.2rem,2.5vw,1.65rem)] leading-[2.2] font-bold text-slate-500" data-typing-passage aria-label="فقرة التدريب"></p>
                        </div>

                        <div class="mt-6">
                            <label class="mb-2 block text-sm font-black text-slate-700" for="typingTrainerInput">اكتب الكلمة المظللة ثم اضغط مسافة</label>
                            <input class="min-h-16 w-full rounded-2xl border-2 border-violet-200 bg-white px-5 text-2xl font-black text-slate-900 outline-none transition focus:border-violet-600 focus:ring-4 focus:ring-violet-100 disabled:bg-slate-100" id="typingTrainerInput" type="text" autocomplete="off" autocapitalize="off" spellcheck="false" data-typing-input aria-describedby="typingTrainerHint">
                            <p class="mt-2 text-sm font-medium text-slate-500" id="typingTrainerHint">يمكنك استخدام زر Enter لتثبيت الكلمة الأخيرة. اللصق غير مسموح أثناء التدريب.</p>
                        </div>
                    </section>

                    <section class="hidden" data-typing-result aria-live="polite">
                        <div class="text-center">
                            <span class="mx-auto grid size-16 place-items-center rounded-full bg-emerald-100 text-3xl text-emerald-700">
                                <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                            </span>
                            <h3 class="mt-4 text-2xl font-black text-slate-900" data-typing-result-title>نتيجة التدريب</h3>
                            <p class="mt-2 font-medium text-slate-500" data-typing-result-summary></p>
                        </div>
                        <div class="mt-7 grid grid-cols-2 gap-3 lg:grid-cols-3">
                            <div class="rounded-2xl bg-violet-50 p-4 text-center"><span class="text-xs font-bold text-violet-600">السرعة</span><strong class="mt-1 block text-2xl font-black text-violet-900" data-result-wpm>0</strong><small class="font-bold text-violet-600">كلمة/دقيقة</small></div>
                            <div class="rounded-2xl bg-blue-50 p-4 text-center"><span class="text-xs font-bold text-blue-600">الدقة</span><strong class="mt-1 block text-2xl font-black text-blue-900" data-result-accuracy>0%</strong></div>
                            <div class="rounded-2xl bg-emerald-50 p-4 text-center"><span class="text-xs font-bold text-emerald-600">الصحيحة</span><strong class="mt-1 block text-2xl font-black text-emerald-900" data-result-correct>0</strong></div>
                            <div class="rounded-2xl bg-red-50 p-4 text-center"><span class="text-xs font-bold text-red-600">الخاطئة</span><strong class="mt-1 block text-2xl font-black text-red-900" data-result-incorrect>0</strong></div>
                            <div class="rounded-2xl bg-amber-50 p-4 text-center"><span class="text-xs font-bold text-amber-600">الإكمال</span><strong class="mt-1 block text-2xl font-black text-amber-900" data-result-completion>0%</strong></div>
                            <div class="rounded-2xl bg-slate-100 p-4 text-center"><span class="text-xs font-bold text-slate-500">الزمن</span><strong class="mt-1 block text-2xl font-black text-slate-900" data-result-time>00:00</strong></div>
                        </div>
                        <div class="mt-7 grid gap-3 sm:grid-cols-3">
                            <button class="min-h-12 rounded-xl bg-violet-700 px-6 font-black text-white transition hover:bg-violet-800" type="button" data-typing-restart><i class="fa-solid fa-arrow-rotate-left ms-2" aria-hidden="true"></i>إعادة المحاولة</button>
                            <button class="min-h-12 rounded-xl bg-violet-700 px-6 font-black text-white transition hover:bg-violet-800" type="button" data-typing-retry><i class="fa-solid fa-rotate ms-2" aria-hidden="true"></i>فقرة أخرى</button>
                            <button class="min-h-12 rounded-xl border border-violet-200 bg-white px-6 font-black text-violet-800 transition hover:bg-violet-50" type="button" data-typing-change-language><i class="fa-solid fa-language ms-2" aria-hidden="true"></i>تغيير اللغة</button>
                        </div>
                    </section>

                    <div class="mt-5 hidden rounded-2xl border border-red-200 bg-red-50 p-4 text-center font-bold text-red-800" data-typing-error role="alert">
                        تعذر تحميل فقرة التدريب. حاول مرة أخرى.
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
