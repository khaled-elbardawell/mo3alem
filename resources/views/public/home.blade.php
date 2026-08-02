@extends('layouts.public', ['activeNavigation' => 'home'])

@section('title', $seo->title)
@section('canonical', $seo->canonical_url ?: route('home'))

@section('content')
    <section class="relative isolate overflow-hidden border-b border-violet-100/70 bg-white" id="home">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_12%_24%,rgba(124,58,237,0.13),transparent_25%),radial-gradient(circle_at_84%_18%,rgba(59,130,246,0.08),transparent_23%)]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-0 -z-10 opacity-45 [background-image:radial-gradient(rgba(109,40,217,0.16)_1px,transparent_1px)] [background-size:22px_22px] [mask-image:linear-gradient(to_bottom,black,transparent_85%)]" aria-hidden="true"></div>

        <div class="mx-auto grid min-h-[560px] w-[min(calc(100%_-_2rem),1280px)] min-w-0 items-center gap-8 py-8 lg:grid-cols-[1fr_1.05fr] lg:py-10">
            <div class="min-w-0 text-center lg:text-right" data-reveal>
                <span class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-black text-violet-800">
                    <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>
                    أدوات ذكية صُممت للمعلم العربي
                </span>
                <h1 class="mt-5 text-[clamp(2.15rem,4.4vw,3.8rem)] font-black leading-[1.16] tracking-tight text-[#111a35]">
                    كل ما يحتاجه المعلم
                    <span class="block bg-linear-to-l from-violet-700 via-indigo-700 to-blue-600 bg-clip-text text-transparent">في مكان واحد</span>
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg font-medium leading-8 text-slate-600 lg:mx-0">
                    وفّر وقتك وأنشئ أنشطة صفية، رموز QR وشهادات تعليمية بنتائج احترافية وسهلة المشاركة.
                </p>
                <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row lg:justify-start">
                    <a class="inline-flex min-h-13 items-center justify-center gap-2.5 rounded-xl bg-linear-to-l from-violet-700 to-indigo-700 px-7 font-black text-white shadow-xl shadow-violet-900/20 transition-transform hover:-translate-y-0.5" href="{{ route('tools.wheel') }}">
                        ابدأ مجانًا
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <a class="inline-flex min-h-13 items-center justify-center gap-2.5 rounded-xl border border-violet-200 bg-white px-7 font-black text-violet-800 shadow-sm transition-colors hover:bg-violet-50" href="#tools">
                        استكشف الأدوات
                        <i class="fa-solid fa-border-all" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-x-7 gap-y-3 text-sm font-bold text-slate-600 lg:justify-start">
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500" aria-hidden="true"></i> مجاني للبدء</span>
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-bolt text-amber-500" aria-hidden="true"></i> سريع وسهل</span>
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-shield-halved text-violet-600" aria-hidden="true"></i> يحترم خصوصيتك</span>
                </div>
            </div>

            <div class="relative mx-auto h-[400px] w-full max-w-[590px] max-sm:h-[350px]" data-reveal="scale">
                <img class="absolute top-3 right-1/2 z-20 h-auto w-[215px] translate-x-1/2 drop-shadow-[0_30px_40px_rgba(76,29,149,0.22)] sm:w-[245px] lg:w-[265px]" src="{{ asset('assets/wheel-card.png') }}" alt="معاينة أداة عجلة الأسماء العشوائية" fetchpriority="high">
                <img class="absolute top-16 right-0 z-10 h-auto w-[165px] rotate-6 drop-shadow-[0_22px_30px_rgba(30,41,59,0.14)] sm:right-3 sm:w-[215px]" src="{{ asset('assets/qr-card.png') }}" alt="معاينة أداة إنشاء رمز QR" decoding="async">
                <img class="absolute top-16 left-0 z-10 h-auto w-[170px] -rotate-6 drop-shadow-[0_22px_30px_rgba(30,41,59,0.14)] sm:left-3 sm:w-[220px]" src="{{ asset('assets/certificate-card.png') }}" alt="معاينة أداة إنشاء الشهادات" decoding="async">
            </div>
        </div>
    </section>

    @if($campaigns['top'])
        <section class="mx-auto w-[min(calc(100%_-_2rem),1040px)] py-6" aria-label="إعلان علوي">
            <x-public.advertisement :campaign="$campaigns['top']" />
        </section>
    @endif

    <section class="scroll-mt-24 py-16 sm:py-20" id="tools">
        <div class="mx-auto w-[min(calc(100%_-_2rem),1280px)]">
            <div class="text-center" data-reveal>
                <h2 class="text-4xl font-black tracking-tight text-[#111a35] sm:text-5xl">أدوات معلم</h2>
                <p class="mt-3 text-xl font-black text-violet-700">اختر الأداة وابدأ الإنجاز</p>
                <p class="mx-auto mt-2 max-w-2xl font-medium leading-7 text-slate-500">تجربة مباشرة وبسيطة، دون خطوات معقدة أو معرفة تقنية مسبقة.</p>
            </div>

            <div @class(['mt-10 grid gap-5', 'lg:grid-cols-[1fr_260px]' => $campaigns['side']])>
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3" data-reveal-group>
                    <article class="group flex flex-col rounded-3xl border border-violet-200 bg-white p-4 shadow-[0_16px_50px_rgba(49,46,129,0.08)] transition-[transform,box-shadow] hover:-translate-y-1 hover:shadow-[0_24px_65px_rgba(76,29,149,0.14)] sm:p-5" data-reveal data-lift-card>
                        <div class="flex min-h-12 items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-violet-100 text-xl text-violet-700"><i class="fa-solid fa-dharmachakra" aria-hidden="true"></i></span>
                            <h3 class="text-lg font-black text-[#111a35]">عجلة الأسماء العشوائية</h3>
                        </div>
                        <p class="mt-3 text-sm font-medium leading-7 text-slate-500 md:min-h-16">أدخل أسماء طلابك واختر اسمًا عشوائيًا بطريقة ممتعة وشفافة داخل الصف.</p>
                        <div class="my-4 grid h-40 min-h-0 shrink-0 place-items-center overflow-hidden rounded-2xl bg-slate-50/70 p-2 sm:h-44">
                            <img class="block h-auto max-h-36 w-auto max-w-full object-contain sm:max-h-40" src="{{ asset('assets/wheel-small-card.png') }}" alt="واجهة عجلة الأسماء العشوائية" loading="lazy">
                        </div>
                        <a class="mt-auto inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white transition-colors hover:bg-violet-800" href="{{ route('tools.wheel') }}">
                            استخدم الأداة
                            <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                        </a>
                    </article>

                    <article class="flex flex-col rounded-3xl border border-violet-100 bg-white p-4 shadow-[0_16px_50px_rgba(49,46,129,0.06)] sm:p-5" data-reveal data-lift-card>
                        <div class="flex min-h-12 items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-indigo-100 text-xl text-indigo-700"><i class="fa-solid fa-qrcode" aria-hidden="true"></i></span>
                            <h3 class="text-lg font-black text-[#111a35]">إنشاء QR احترافي</h3>
                        </div>
                        <p class="mt-3 text-sm font-medium leading-7 text-slate-500 md:min-h-16">حوّل الروابط والنصوص إلى رمز QR مخصص بالألوان والشعار، جاهز للطباعة والمشاركة.</p>
                        <div class="my-4 grid h-40 min-h-0 shrink-0 place-items-center overflow-hidden rounded-2xl bg-slate-50/70 p-2 sm:h-44">
                            <img class="block h-auto max-h-36 w-auto max-w-full object-contain sm:max-h-40" src="{{ asset('assets/qr-small-card.png') }}" alt="واجهة أداة إنشاء رمز QR" loading="lazy">
                        </div>
                        <a class="mt-auto inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white transition-colors hover:bg-violet-800" href="">
                            استخدم الأداة
                            <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                        </a>
                    </article>

                    <article class="flex flex-col rounded-3xl border border-violet-100 bg-white p-4 shadow-[0_16px_50px_rgba(49,46,129,0.06)] sm:p-5" data-reveal data-lift-card>
                        <div class="flex min-h-12 items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-100 text-xl text-amber-600"><i class="fa-solid fa-award" aria-hidden="true"></i></span>
                            <h3 class="text-lg font-black text-[#111a35]">إنشاء الشهادات</h3>
                        </div>
                        <p class="mt-3 text-sm font-medium leading-7 text-slate-500 md:min-h-16">صمّم شهادات أنيقة من قوالب عربية جاهزة، وأنشئ شهادات طلابك دفعة واحدة.</p>
                        <div class="my-4 grid h-40 min-h-0 shrink-0 place-items-center overflow-hidden rounded-2xl bg-slate-50/70 p-2 sm:h-44">
                            <img class="block h-auto max-h-36 w-auto max-w-full object-contain sm:max-h-40" src="{{ asset('assets/certificate-small-card.png') }}" alt="واجهة أداة إنشاء الشهادات" loading="lazy">
                        </div>
                        <a class="mt-auto inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white transition-colors hover:bg-violet-800" href="">
                            استخدم الأداة
                            <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                        </a>
                    </article>
                </div>

                @if($campaigns['side'])
                    <x-public.advertisement class="min-h-72 lg:h-full" :campaign="$campaigns['side']" placement="side" />
                @endif
            </div>
        </div>
    </section>

    <section class="scroll-mt-24 border-y border-violet-100 bg-white py-16 sm:py-20" id="features">
        <div class="mx-auto w-[min(calc(100%_-_2rem),1280px)]">
            <div class="text-center" data-reveal>
                <h2 class="text-4xl font-black tracking-tight text-[#111a35] sm:text-5xl">مميزاتنا</h2>
                <p class="mt-3 text-xl font-black text-violet-700">تجربة صُممت لتسهّل يومك</p>
            </div>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group>
                @foreach([
                    ['icon' => 'fa-rocket', 'color' => 'text-violet-700 bg-violet-100', 'title' => 'سرعة وكفاءة', 'text' => 'أنجز مهامك التعليمية في دقائق بدلًا من ساعات.'],
                    ['icon' => 'fa-folder-open', 'color' => 'text-amber-600 bg-amber-100', 'title' => 'قوالب جاهزة', 'text' => 'ابدأ من نماذج مدروسة ثم خصّصها كما تريد.'],
                    ['icon' => 'fa-crown', 'color' => 'text-indigo-700 bg-indigo-100', 'title' => 'نتائج احترافية', 'text' => 'مخرجات أنيقة وجاهزة للطباعة أو العرض والمشاركة.'],
                    ['icon' => 'fa-share-nodes', 'color' => 'text-rose-600 bg-rose-100', 'title' => 'مشاركة سهلة', 'text' => 'نزّل أعمالك وشاركها مع الطلاب والزملاء بسرعة.'],
                ] as $feature)
                    <article class="rounded-2xl border border-slate-100 bg-[#fbfbff] p-5" data-reveal data-lift-card>
                        <span class="grid h-11 w-11 place-items-center rounded-xl {{ $feature['color'] }}"><i class="fa-solid {{ $feature['icon'] }}" aria-hidden="true"></i></span>
                        <h3 class="mt-4 text-lg font-black text-[#111a35]">{{ $feature['title'] }}</h3>
                        <p class="mt-2 text-sm font-medium leading-7 text-slate-500">{{ $feature['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="scroll-mt-24 bg-violet-50/40 py-16 sm:py-20" id="activity">
        <div class="mx-auto w-[min(calc(100%_-_2rem),1120px)]">
            <div class="text-center" data-reveal>
                <h2 class="text-4xl font-black tracking-tight text-[#111a35] sm:text-5xl">نشاط المنصة</h2>
                <p class="mt-3 text-xl font-black text-violet-700">أرقام تنمو معكم</p>
                <p class="mx-auto mt-2 max-w-2xl font-medium leading-7 text-slate-500">نظرة سريعة على ما ينجزه مجتمع معلم باستخدام أدوات المنصة.</p>
            </div>
            <div class="mt-10 grid grid-cols-2 gap-4 lg:grid-cols-4" data-reveal-group>
                @foreach([
                    ['value' => $platformActivity['qrCodes'], 'label' => 'رموز QR تم إنشاؤها', 'icon' => 'fa-qrcode', 'color' => 'text-indigo-700 bg-indigo-100'],
                    ['value' => $platformActivity['certificates'], 'label' => 'شهادات تم تصميمها', 'icon' => 'fa-award', 'color' => 'text-amber-600 bg-amber-100'],
                    ['value' => $platformActivity['competitions'], 'label' => 'مسابقات', 'icon' => 'fa-trophy', 'color' => 'text-rose-600 bg-rose-100'],
                    ['value' => $platformActivity['activeUsers'], 'label' => 'مستخدمون نشطون', 'icon' => 'fa-users', 'color' => 'text-violet-700 bg-violet-100'],
                ] as $stat)
                    <article class="flex min-h-40 flex-col items-center justify-center rounded-3xl border border-violet-100 bg-white p-5 text-center shadow-[0_16px_45px_rgba(49,46,129,0.07)]" data-reveal data-lift-card>
                        <span class="grid h-12 w-12 place-items-center rounded-2xl text-xl {{ $stat['color'] }}"><i class="fa-solid {{ $stat['icon'] }}" aria-hidden="true"></i></span>
                        <strong class="mt-3 block text-3xl font-black text-violet-700 sm:text-4xl">{{ number_format($stat['value']) }}</strong>
                        <span class="mt-1 text-sm font-bold text-slate-600">{{ $stat['label'] }}</span>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="scroll-mt-24 py-16 sm:py-20" id="how">
        <div class="mx-auto w-[min(calc(100%_-_2rem),1120px)]">
            <div class="text-center" data-reveal>
                <h2 class="text-4xl font-black tracking-tight text-[#111a35] sm:text-5xl">كيف تعمل المنصة؟</h2>
                <p class="mt-3 text-xl font-black text-violet-700">خطوات بسيطة وواضحة</p>
                <p class="mx-auto mt-2 max-w-2xl font-medium leading-7 text-slate-500">أربع خطوات فقط تفصلك عن نتيجة جاهزة للاستخدام والمشاركة.</p>
            </div>
            <ol class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4" data-reveal-group>
                <li class="relative rounded-3xl border border-violet-100 bg-white p-6 shadow-sm" data-reveal data-lift-card>
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-violet-700 text-lg font-black text-white">١</span>
                    <h3 class="mt-5 text-xl font-black text-[#111a35]">اختر أداتك</h3>
                    <p class="mt-2 font-medium leading-7 text-slate-500">ابدأ بالأداة المناسبة للمهمة التي تريد إنجازها.</p>
                </li>
                <li class="relative rounded-3xl border border-violet-100 bg-white p-6 shadow-sm" data-reveal data-lift-card>
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-violet-700 text-lg font-black text-white">٢</span>
                    <h3 class="mt-5 text-xl font-black text-[#111a35]">أدخل بياناتك</h3>
                    <p class="mt-2 font-medium leading-7 text-slate-500">أضف الأسماء أو الروابط أو بيانات الشهادة التي تحتاجها.</p>
                </li>
                <li class="relative rounded-3xl border border-violet-100 bg-white p-6 shadow-sm" data-reveal data-lift-card>
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-violet-700 text-lg font-black text-white">٣</span>
                    <h3 class="mt-5 text-xl font-black text-[#111a35]">خصّص الإعدادات</h3>
                    <p class="mt-2 font-medium leading-7 text-slate-500">اضبط الألوان والتصميم وطريقة العرض بما يناسبك.</p>
                </li>
                <li class="relative rounded-3xl border border-violet-100 bg-white p-6 shadow-sm" data-reveal data-lift-card>
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-violet-700 text-lg font-black text-white">٤</span>
                    <h3 class="mt-5 text-xl font-black text-[#111a35]">أنشئ وشارك</h3>
                    <p class="mt-2 font-medium leading-7 text-slate-500">أنشئ النتيجة ثم نزّلها أو شاركها مباشرة بكل سهولة.</p>
                </li>
            </ol>
        </div>
    </section>

    @if($campaigns['bottom'])
        <section class="mx-auto w-[min(calc(100%_-_2rem),1040px)] pb-16" aria-label="إعلان سفلي">
            <x-public.advertisement :campaign="$campaigns['bottom']" placement="bottom" />
        </section>
    @endif

    <section class="scroll-mt-24 border-t border-violet-100 bg-white py-16 sm:py-20" id="faq">
        <div class="mx-auto w-[min(calc(100%_-_2rem),900px)]">
            <div class="text-center" data-reveal>
                <h2 class="text-4xl font-black tracking-tight text-[#111a35] sm:text-5xl">الأسئلة الشائعة</h2>
                <p class="mt-3 text-xl font-black text-violet-700">لديك سؤال؟</p>
                <p class="mx-auto mt-2 max-w-2xl font-medium leading-7 text-slate-500">إجابات واضحة عن الأدوات والحساب وطريقة استخدام المنصة.</p>
            </div>
            <div class="faq-list mt-9 grid gap-3" data-reveal-group>
                <details class="group rounded-2xl border border-violet-100 bg-[#fbfbff] p-5 open:bg-white open:shadow-sm" data-reveal open>
                    <summary class="flex list-none items-center justify-between gap-4 font-black text-[#111a35] [&::-webkit-details-marker]:hidden">
                        هل منصة معلم مجانية؟
                        <i class="fa-solid fa-plus text-sm text-violet-600 transition-transform group-open:rotate-45" aria-hidden="true"></i>
                    </summary>
                    <p class="mt-4 font-medium leading-8 text-slate-600">يمكنك استخدام الأدوات الأساسية مجانًا، وستتوفر لاحقًا مزايا وقوالب إضافية للحسابات المسجلة.</p>
                </details>
                <details class="group rounded-2xl border border-violet-100 bg-[#fbfbff] p-5 open:bg-white open:shadow-sm" data-reveal>
                    <summary class="flex list-none items-center justify-between gap-4 font-black text-[#111a35] [&::-webkit-details-marker]:hidden">
                        هل يجب إنشاء حساب لاستخدام العجلة؟
                        <i class="fa-solid fa-plus text-sm text-violet-600 transition-transform group-open:rotate-45" aria-hidden="true"></i>
                    </summary>
                    <p class="mt-4 font-medium leading-8 text-slate-600">لا. تستطيع تجربة عجلة الأسماء مباشرة، ويمنحك الحساب إمكانية حفظ قوائمك ومسابقاتك والوصول إليها لاحقًا.</p>
                </details>
                <details class="group rounded-2xl border border-violet-100 bg-[#fbfbff] p-5 open:bg-white open:shadow-sm" data-reveal>
                    <summary class="flex list-none items-center justify-between gap-4 font-black text-[#111a35] [&::-webkit-details-marker]:hidden">
                        ماذا يمكنني إنشاء بأداتي QR والشهادات؟
                        <i class="fa-solid fa-plus text-sm text-violet-600 transition-transform group-open:rotate-45" aria-hidden="true"></i>
                    </summary>
                    <p class="mt-4 font-medium leading-8 text-slate-600">يمكنك إنشاء رموز QR مخصصة وجاهزة للطباعة، وتصميم شهادات عربية احترافية وتنزيلها أو مشاركتها بسهولة.</p>
                </details>
                <details class="group rounded-2xl border border-violet-100 bg-[#fbfbff] p-5 open:bg-white open:shadow-sm" data-reveal>
                    <summary class="flex list-none items-center justify-between gap-4 font-black text-[#111a35] [&::-webkit-details-marker]:hidden">
                        هل تعمل المنصة على الهاتف؟
                        <i class="fa-solid fa-plus text-sm text-violet-600 transition-transform group-open:rotate-45" aria-hidden="true"></i>
                    </summary>
                    <p class="mt-4 font-medium leading-8 text-slate-600">نعم، صُممت واجهة معلم لتعمل على الهاتف والجهاز اللوحي والحاسوب مع تجربة عربية متجاوبة.</p>
                </details>
            </div>
        </div>
    </section>

@endsection
