@extends('layouts.public', ['activeNavigation' => 'home'])

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
                    <a class="inline-flex min-h-13 items-center justify-center gap-2.5 rounded-xl bg-linear-to-l from-violet-700 to-indigo-700 px-7 font-black text-white shadow-xl shadow-violet-900/20 transition-transform hover:-translate-y-0.5" href="#tools" data-primary-tools-action>
                        استكشف الأدوات
                        <i class="fa-solid fa-border-all" aria-hidden="true"></i>
                    </a>
                    @guest
                        <a class="inline-flex min-h-13 items-center justify-center gap-2.5 rounded-xl border border-violet-200 bg-white px-7 font-black text-violet-800 shadow-sm transition-colors hover:bg-violet-50" href="{{ route('login') }}" data-guest-start>
                            ابدأ مجانًا
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                        </a>
                    @endguest
                </div>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-x-7 gap-y-3 text-sm font-bold text-slate-600 lg:justify-start">
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500" aria-hidden="true"></i> مجاني للبدء</span>
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-bolt text-amber-500" aria-hidden="true"></i> سريع وسهل</span>
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-shield-halved text-violet-600" aria-hidden="true"></i> يحترم خصوصيتك</span>
                </div>
            </div>

            <div class="relative mx-auto h-[400px] w-full max-w-[590px] max-sm:h-[350px]" data-reveal="scale">
                <a class="group absolute top-3 right-1/2 z-20 w-[215px] translate-x-1/2 rounded-[2rem] focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-violet-600 sm:w-[245px] lg:w-[265px]" href="{{ route('tools.wheel') }}" aria-label="فتح أداة عجلة الأسماء العشوائية" data-hero-tool="wheel">
                    <span class="block motion-safe:animate-hero-tool-pulse">
                        <img class="block h-auto w-full drop-shadow-[0_30px_40px_rgba(76,29,149,0.22)] transition-transform duration-300 group-hover:scale-[1.04]" src="{{ asset('assets/wheel-card.png') }}" alt="معاينة أداة عجلة الأسماء العشوائية" fetchpriority="high">
                    </span>
                </a>
                <a class="group absolute top-16 right-0 z-10 w-[165px] rounded-[2rem] focus-visible:z-30 focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-violet-600 sm:right-3 sm:w-[215px]" href="{{ route('tools.qr') }}" aria-label="فتح أداة إنشاء رمز QR" data-hero-tool="qr">
                    <span class="block motion-safe:animate-hero-tool-pulse motion-safe:[animation-delay:-1.2s]">
                        <img class="block h-auto w-full rotate-6 drop-shadow-[0_22px_30px_rgba(30,41,59,0.14)] transition-transform duration-300 group-hover:scale-[1.04] group-hover:rotate-3" src="{{ asset('assets/qr-card.png') }}" alt="معاينة أداة إنشاء رمز QR" decoding="async">
                    </span>
                </a>
                <a class="group absolute top-16 left-0 z-10 w-[170px] rounded-[2rem] focus-visible:z-30 focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-violet-600 sm:left-3 sm:w-[220px]" href="{{ route('tools.certificates') }}" aria-label="فتح أداة إنشاء الشهادات" data-hero-tool="certificates">
                    <span class="block motion-safe:animate-hero-tool-pulse motion-safe:[animation-delay:-2.4s]">
                        <img class="block h-auto w-full -rotate-6 drop-shadow-[0_22px_30px_rgba(30,41,59,0.14)] transition-transform duration-300 group-hover:scale-[1.04] group-hover:-rotate-3" src="{{ asset('assets/certificate-card.png') }}" alt="معاينة أداة إنشاء الشهادات" decoding="async">
                    </span>
                </a>
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
                <div class="grid items-start gap-5 sm:grid-cols-2 xl:grid-cols-3" data-reveal-group>
                    <a class="group flex h-fit flex-col rounded-2xl border border-violet-200 bg-white p-4 shadow-[0_16px_50px_rgba(49,46,129,0.08)] transition-[transform,box-shadow,border-color] hover:-translate-y-1 hover:border-violet-300 hover:shadow-[0_24px_65px_rgba(76,29,149,0.14)] focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-violet-600" href="{{ route('tools.wheel') }}" data-tool-card="wheel" data-reveal data-lift-card>
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-violet-100 text-xl text-violet-700"><i class="fa-solid fa-dharmachakra" aria-hidden="true"></i></span>
                            <h3 class="text-lg font-black text-[#111a35]">عجلة الأسماء العشوائية</h3>
                        </div>
                        <p class="mt-2.5 text-sm font-medium leading-7 text-slate-500">أدخل أسماء طلابك واختر اسمًا عشوائيًا بطريقة ممتعة وشفافة داخل الصف.</p>
                        <div class="mt-3 mb-3 grid h-32 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-50/70 p-2 sm:h-36">
                            <img class="block h-auto max-h-28 w-auto max-w-full object-contain transition-transform duration-300 group-hover:scale-[1.03] sm:max-h-32" src="{{ asset('assets/wheel-small-card.png') }}" alt="واجهة عجلة الأسماء العشوائية" loading="lazy">
                        </div>
                        <span class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white transition-colors group-hover:bg-violet-800">
                            استخدم الأداة
                            <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                        </span>
                    </a>

                    <a class="group flex h-fit flex-col rounded-2xl border border-violet-100 bg-white p-4 shadow-[0_16px_50px_rgba(49,46,129,0.06)] transition-[transform,box-shadow,border-color] hover:-translate-y-1 hover:border-violet-300 hover:shadow-[0_24px_65px_rgba(76,29,149,0.14)] focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-violet-600" href="{{ route('tools.qr') }}" data-tool-card="qr" data-reveal data-lift-card>
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-indigo-100 text-xl text-indigo-700"><i class="fa-solid fa-qrcode" aria-hidden="true"></i></span>
                            <h3 class="text-lg font-black text-[#111a35]">إنشاء QR احترافي</h3>
                        </div>
                        <p class="mt-2.5 text-sm font-medium leading-7 text-slate-500">حوّل الروابط والنصوص إلى رمز QR مخصص بالألوان والشعار، جاهز للطباعة والمشاركة.</p>
                        <div class="mt-3 mb-3 grid h-32 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-50/70 p-2 sm:h-36">
                            <img class="block h-auto max-h-28 w-auto max-w-full object-contain transition-transform duration-300 group-hover:scale-[1.03] sm:max-h-32" src="{{ asset('assets/qr-small-card.png') }}" alt="واجهة أداة إنشاء رمز QR" loading="lazy">
                        </div>
                        <span class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white transition-colors group-hover:bg-violet-800">
                            استخدم الأداة
                            <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                        </span>
                    </a>

                    <a class="group flex h-fit flex-col rounded-2xl border border-violet-100 bg-white p-4 shadow-[0_16px_50px_rgba(49,46,129,0.06)] transition-[transform,box-shadow,border-color] hover:-translate-y-1 hover:border-violet-300 hover:shadow-[0_24px_65px_rgba(76,29,149,0.14)] focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-violet-600" href="{{ route('tools.certificates') }}" data-tool-card="certificates" data-reveal data-lift-card>
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-100 text-xl text-amber-600"><i class="fa-solid fa-award" aria-hidden="true"></i></span>
                            <h3 class="text-lg font-black text-[#111a35]">إنشاء الشهادات</h3>
                        </div>
                        <p class="mt-2.5 text-sm font-medium leading-7 text-slate-500">صمّم شهادات أنيقة من قوالب عربية جاهزة، وأنشئ شهادات طلابك دفعة واحدة.</p>
                        <div class="mt-3 mb-3 grid h-32 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-50/70 p-2 sm:h-36">
                            <img class="block h-auto max-h-28 w-auto max-w-full object-contain transition-transform duration-300 group-hover:scale-[1.03] sm:max-h-32" src="{{ asset('assets/certificate-small-card.png') }}" alt="واجهة أداة إنشاء الشهادات" loading="lazy">
                        </div>
                        <span class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white transition-colors group-hover:bg-violet-800">
                            استخدم الأداة
                            <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                        </span>
                    </a>
                </div>

                @if($campaigns['side'])
                    <x-public.advertisement class="min-h-72 lg:h-full" :campaign="$campaigns['side']" placement="side" />
                @endif
            </div>
        </div>
    </section>

    <section class="scroll-mt-24 border-y border-slate-100 bg-white py-16 sm:py-20" id="features" data-section-surface="plain">
        <div class="relative mx-auto w-[min(calc(100%_-_2rem),1280px)]">
            <div class="text-center" data-reveal>
                <h2 class="text-4xl font-black tracking-tight text-[#111a35] sm:text-5xl">مميزاتنا</h2>
                <p class="mt-3 text-xl font-black text-violet-700">تجربة صُممت لتسهّل يومك</p>
            </div>
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group>
                @foreach([
                    ['number' => '01', 'icon' => 'fa-rocket', 'iconStyle' => 'from-white via-violet-50/70 to-slate-100 text-violet-600/80 shadow-[0_10px_24px_rgba(15,23,42,0.08)]', 'sparkle' => 'text-violet-300/70', 'badge' => 'bg-violet-100 text-violet-700', 'dots' => 'text-violet-200', 'title' => 'سرعة وكفاءة', 'text' => 'أنجز مهامك التعليمية في دقائق بدلًا من ساعات.'],
                    ['number' => '02', 'icon' => 'fa-folder-open', 'iconStyle' => 'from-white via-amber-50/70 to-slate-100 text-amber-600/80 shadow-[0_10px_24px_rgba(15,23,42,0.08)]', 'sparkle' => 'text-amber-300/70', 'badge' => 'bg-amber-100 text-amber-600', 'dots' => 'text-amber-200', 'title' => 'قوالب جاهزة', 'text' => 'ابدأ من نماذج مدروسة ثم خصّصها كما تريد.'],
                    ['number' => '03', 'icon' => 'fa-crown', 'iconStyle' => 'from-white via-indigo-50/70 to-slate-100 text-indigo-600/80 shadow-[0_10px_24px_rgba(15,23,42,0.08)]', 'sparkle' => 'text-indigo-300/70', 'badge' => 'bg-indigo-100 text-indigo-700', 'dots' => 'text-indigo-200', 'title' => 'نتائج احترافية', 'text' => 'مخرجات أنيقة وجاهزة للطباعة أو العرض والمشاركة.'],
                    ['number' => '04', 'icon' => 'fa-share-nodes', 'iconStyle' => 'from-white via-rose-50/70 to-slate-100 text-rose-600/80 shadow-[0_10px_24px_rgba(15,23,42,0.08)]', 'sparkle' => 'text-rose-300/70', 'badge' => 'bg-rose-100 text-rose-600', 'dots' => 'text-rose-200', 'title' => 'مشاركة سهلة', 'text' => 'نزّل أعمالك وشاركها مع الطلاب والزملاء بسرعة.'],
                ] as $feature)
                    <article class="group relative flex min-h-[330px] flex-col items-center overflow-hidden rounded-[28px] border border-white/90 bg-white/85 px-6 py-6 text-center shadow-[0_20px_55px_rgba(76,29,149,0.10)] backdrop-blur-sm transition-[transform,box-shadow,border-color] duration-300 hover:border-violet-200 hover:shadow-[0_28px_70px_rgba(76,29,149,0.16)]" data-feature-card="{{ $feature['number'] }}" data-reveal data-lift-card>
                        <span class="relative grid h-24 w-20 shrink-0 place-items-center rounded-[27px] border border-slate-200/70 bg-linear-to-b text-[34px] transition-transform duration-300 group-hover:-translate-y-1 group-hover:scale-105 {{ $feature['iconStyle'] }}" data-icon-tone="soft">
                            <i class="fa-solid {{ $feature['icon'] }}" aria-hidden="true"></i>
                            <i class="fa-solid fa-star absolute top-3 right-3 text-[10px] {{ $feature['sparkle'] }}" aria-hidden="true"></i>
                        </span>
                        <h3 class="mt-6 text-[22px] font-black tracking-tight text-[#111a35]">{{ $feature['title'] }}</h3>
                        <p class="mt-3 max-w-[230px] text-[15px] font-medium leading-7 text-slate-500">{{ $feature['text'] }}</p>
                        <div class="mt-auto flex w-full items-center gap-3 pt-6 {{ $feature['dots'] }}" aria-hidden="true">
                            <span class="h-1 flex-1 bg-[radial-gradient(circle,currentColor_1.5px,transparent_1.6px)] bg-[size:12px_4px] bg-repeat-x"></span>
                            <strong class="grid h-11 w-11 shrink-0 place-items-center rounded-full text-sm font-black {{ $feature['badge'] }}" data-feature-number>{{ $feature['number'] }}</strong>
                            <span class="h-1 flex-1 bg-[radial-gradient(circle,currentColor_1.5px,transparent_1.6px)] bg-[size:12px_4px] bg-repeat-x"></span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="scroll-mt-24 border-b border-slate-100 bg-white py-16 sm:py-20" id="activity" data-section-surface="plain">
        <div class="relative mx-auto w-[min(calc(100%_-_2rem),1280px)]">
            <div class="text-center" data-reveal>
                <h2 class="text-4xl font-black tracking-tight text-[#111a35] sm:text-5xl">نشاط المنصة</h2>
                <p class="mt-3 text-xl font-black text-violet-700">أرقام تنمو معكم</p>
                <p class="mx-auto mt-2 max-w-2xl font-medium leading-7 text-slate-500">نظرة سريعة على ما ينجزه مجتمع معلم باستخدام أدوات المنصة.</p>
            </div>
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group>
                @foreach([
                    ['key' => 'spins', 'value' => $platformActivity['spins'], 'label' => 'مرة تم تدوير العجلة', 'icon' => 'fa-arrows-rotate', 'iconStyle' => 'from-white via-violet-50/70 to-slate-100 text-violet-600/80 shadow-[0_10px_24px_rgba(15,23,42,0.08)]', 'sparkle' => 'text-violet-300/70', 'dots' => 'text-violet-200', 'accent' => 'bg-violet-500'],
                    ['key' => 'names', 'value' => $platformActivity['names'], 'label' => 'اسمًا تمت إضافته', 'icon' => 'fa-signature', 'iconStyle' => 'from-white via-rose-50/70 to-slate-100 text-rose-600/80 shadow-[0_10px_24px_rgba(15,23,42,0.08)]', 'sparkle' => 'text-rose-300/70', 'dots' => 'text-rose-200', 'accent' => 'bg-rose-400'],
                    ['key' => 'qr', 'value' => $platformActivity['qrOperations'], 'label' => 'عملية على رموز QR', 'icon' => 'fa-qrcode', 'iconStyle' => 'from-white via-indigo-50/70 to-slate-100 text-indigo-600/80 shadow-[0_10px_24px_rgba(15,23,42,0.08)]', 'sparkle' => 'text-indigo-300/70', 'dots' => 'text-indigo-200', 'accent' => 'bg-indigo-400'],
                    ['key' => 'certificates', 'value' => $platformActivity['certificateOperations'], 'label' => 'عملية على الشهادات', 'icon' => 'fa-award', 'iconStyle' => 'from-white via-amber-50/70 to-slate-100 text-amber-600/80 shadow-[0_10px_24px_rgba(15,23,42,0.08)]', 'sparkle' => 'text-amber-300/70', 'dots' => 'text-amber-200', 'accent' => 'bg-amber-400'],
                ] as $stat)
                    <article class="group relative flex min-h-[250px] flex-col items-center overflow-hidden rounded-[28px] border border-white/90 bg-white/85 px-5 pt-6 pb-7 text-center shadow-[0_20px_55px_rgba(76,29,149,0.10)] backdrop-blur-sm transition-[transform,box-shadow,border-color] duration-300 hover:border-violet-200 hover:shadow-[0_28px_70px_rgba(76,29,149,0.16)]" data-activity-card="{{ $stat['key'] }}" data-reveal data-lift-card>
                        <span class="relative grid h-20 w-20 shrink-0 place-items-center rounded-[25px] border border-slate-200/70 bg-linear-to-b text-[28px] transition-transform duration-300 group-hover:-translate-y-1 group-hover:scale-105 {{ $stat['iconStyle'] }}" data-icon-tone="soft">
                            <i class="fa-solid {{ $stat['icon'] }}" aria-hidden="true"></i>
                            <i class="fa-solid fa-star absolute top-3 right-3 text-[9px] {{ $stat['sparkle'] }}" aria-hidden="true"></i>
                        </span>
                        <strong class="mt-4 block text-[42px] leading-none font-black tracking-tight text-violet-700" data-count-up data-count-value="{{ $stat['value'] }}" data-count-prefix="{{ $stat['value'] >= 1000 ? '+' : '' }}">{{ $stat['value'] >= 1000 ? '+' : '' }}{{ number_format($stat['value']) }}</strong>
                        <span class="mt-3 text-[17px] font-black leading-7 text-[#111a35]">{{ $stat['label'] }}</span>
                        <span class="absolute right-5 bottom-7 h-5 w-16 bg-[radial-gradient(circle,currentColor_1.5px,transparent_1.6px)] bg-[size:12px_8px] {{ $stat['dots'] }}" aria-hidden="true"></span>
                        <span class="absolute bottom-0 left-1/2 h-1 w-20 -translate-x-1/2 rounded-t-full {{ $stat['accent'] }}" aria-hidden="true"></span>
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
                <details class="group overflow-hidden rounded-2xl border border-violet-100 bg-[#fbfbff] p-5 transition-[transform,border-color,background-color,box-shadow] duration-300 open:-translate-y-0.5 open:border-violet-200 open:bg-white open:shadow-[0_16px_38px_rgba(76,29,149,0.09)]" data-reveal open>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-black text-[#111a35] select-none [&::-webkit-details-marker]:hidden">
                        ما الأدوات المتوفرة في منصة معلم، ومتى أستخدم كل أداة؟
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-violet-100 text-sm text-violet-700 transition-[transform,background-color,color] duration-300 group-open:rotate-45 group-open:bg-violet-700 group-open:text-white [.is-closing_&]:rotate-0"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
                    </summary>
                    <p class="mt-4 overflow-hidden border-t border-violet-100 pt-4 font-medium leading-8 text-slate-600">استخدم عجلة الأسماء للاختيارات والمسابقات الصفية العشوائية، وأداة QR لتحويل الروابط والنصوص إلى رموز قابلة للمسح، وأداة الشهادات لإنشاء شهادات عربية جاهزة للطباعة أو المشاركة.</p>
                </details>
                <details class="group overflow-hidden rounded-2xl border border-violet-100 bg-[#fbfbff] p-5 transition-[transform,border-color,background-color,box-shadow] duration-300 open:-translate-y-0.5 open:border-violet-200 open:bg-white open:shadow-[0_16px_38px_rgba(76,29,149,0.09)]" data-reveal>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-black text-[#111a35] select-none [&::-webkit-details-marker]:hidden">
                        ما فائدة إنشاء حساب في المنصة؟
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-violet-100 text-sm text-violet-700 transition-[transform,background-color,color] duration-300 group-open:rotate-45 group-open:bg-violet-700 group-open:text-white [.is-closing_&]:rotate-0"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
                    </summary>
                    <p class="mt-4 overflow-hidden border-t border-violet-100 pt-4 font-medium leading-8 text-slate-600">يتيح لك الحساب حفظ قوائم العجلة ونتائجها ومسابقاتك ورموز QR والشهادات، ثم الرجوع إليها وتعديلها من لوحة التحكم بدل البدء من جديد في كل مرة.</p>
                </details>
                <details class="group overflow-hidden rounded-2xl border border-violet-100 bg-[#fbfbff] p-5 transition-[transform,border-color,background-color,box-shadow] duration-300 open:-translate-y-0.5 open:border-violet-200 open:bg-white open:shadow-[0_16px_38px_rgba(76,29,149,0.09)]" data-reveal>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-black text-[#111a35] select-none [&::-webkit-details-marker]:hidden">
                        هل أستطيع استخدام الأدوات الثلاث بدون حساب؟
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-violet-100 text-sm text-violet-700 transition-[transform,background-color,color] duration-300 group-open:rotate-45 group-open:bg-violet-700 group-open:text-white [.is-closing_&]:rotate-0"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
                    </summary>
                    <p class="mt-4 overflow-hidden border-t border-violet-100 pt-4 font-medium leading-8 text-slate-600">نعم، يمكنك فتح عجلة الأسماء وأداة QR وأداة الشهادات وتجربتها مباشرة. تحتاج إلى تسجيل الدخول عندما تريد حفظ عملك في حسابك والعودة إليه لاحقًا.</p>
                </details>
                <details class="group overflow-hidden rounded-2xl border border-violet-100 bg-[#fbfbff] p-5 transition-[transform,border-color,background-color,box-shadow] duration-300 open:-translate-y-0.5 open:border-violet-200 open:bg-white open:shadow-[0_16px_38px_rgba(76,29,149,0.09)]" data-reveal>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-black text-[#111a35] select-none [&::-webkit-details-marker]:hidden">
                        كيف تُحفظ بياناتي ومن يستطيع رؤيتها؟
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-violet-100 text-sm text-violet-700 transition-[transform,background-color,color] duration-300 group-open:rotate-45 group-open:bg-violet-700 group-open:text-white [.is-closing_&]:rotate-0"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
                    </summary>
                    <p class="mt-4 overflow-hidden border-t border-violet-100 pt-4 font-medium leading-8 text-slate-600">الأعمال التي تحفظها ترتبط بحسابك ولا تظهر داخل حسابات المستخدمين الآخرين. أدخل فقط البيانات اللازمة لإنجاز المهمة، وتجنب إضافة معلومات شخصية حساسة لا تحتاجها الأداة.</p>
                </details>
                <details class="group overflow-hidden rounded-2xl border border-violet-100 bg-[#fbfbff] p-5 transition-[transform,border-color,background-color,box-shadow] duration-300 open:-translate-y-0.5 open:border-violet-200 open:bg-white open:shadow-[0_16px_38px_rgba(76,29,149,0.09)]" data-reveal>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-black text-[#111a35] select-none [&::-webkit-details-marker]:hidden">
                        هل يمكنني تعديل ما حفظته بدل إنشائه من جديد؟
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-violet-100 text-sm text-violet-700 transition-[transform,background-color,color] duration-300 group-open:rotate-45 group-open:bg-violet-700 group-open:text-white [.is-closing_&]:rotate-0"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
                    </summary>
                    <p class="mt-4 overflow-hidden border-t border-violet-100 pt-4 font-medium leading-8 text-slate-600">نعم. بعد تسجيل الدخول يمكنك فتح العناصر المحفوظة من لوحة التحكم، تحديث القوائم أو التصميم والإعدادات، ثم حفظ النسخة المعدلة ومتابعة العمل عليها لاحقًا.</p>
                </details>
            </div>
        </div>
    </section>

@endsection
