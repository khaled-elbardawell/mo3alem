<!doctype html>
<html class="scroll-smooth overflow-x-hidden motion-reduce:scroll-auto motion-reduce:[&_*]:!duration-[0.001ms] motion-reduce:[&_*]:!animate-none [&.animate-ready_.reveal-on-scroll]:translate-y-6 [&.animate-ready_.reveal-on-scroll]:scale-[0.985] [&.animate-ready_.reveal-on-scroll]:opacity-0 [&.animate-ready_.reveal-on-scroll]:transition-[opacity,transform,box-shadow,border-color] [&.animate-ready_.reveal-on-scroll]:duration-700 [&.animate-ready_.reveal-on-scroll]:ease-[cubic-bezier(0.2,0.8,0.2,1)] [&.animate-ready_.reveal-on-scroll]:delay-[var(--reveal-delay,0ms)] [&.animate-ready_.reveal-on-scroll]:will-change-[opacity,transform] [&.animate-ready_.reveal-on-scroll.is-visible]:translate-y-0 [&.animate-ready_.reveal-on-scroll.is-visible]:scale-100 [&.animate-ready_.reveal-on-scroll.is-visible]:opacity-100 motion-reduce:[&.animate-ready_.reveal-on-scroll]:!translate-y-0 motion-reduce:[&.animate-ready_.reveal-on-scroll]:!scale-100 motion-reduce:[&.animate-ready_.reveal-on-scroll]:!opacity-100"
    lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="icon" href="{{ asset('assets/icon.png') }}" sizes="32x32" />
    <title>{{ $seo->title }}</title>
    @if($seo->description)<meta name="description" content="{{ $seo->description }}" />@endif
    @if($seo->keywords)<meta name="keywords" content="{{ $seo->keywords }}" />@endif
    <meta name="robots" content="{{ $seo->allow_indexing ? 'index,follow' : 'noindex,nofollow' }}" />
    <link rel="canonical" href="{{ $seo->canonical_url ?: route('home') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{ $seo->site_name }}" />
    <meta property="og:title" content="{{ $seo->title }}" />
    @if($seo->description)<meta property="og:description" content="{{ $seo->description }}" />@endif
    <meta property="og:url" content="{{ $seo->canonical_url ?: route('home') }}" />
    @if($seo->og_image_path)<meta property="og:image" content="{{ Storage::disk('public')->url($seo->og_image_path) }}" />@endif
    <meta name="twitter:card" content="{{ $seo->twitter_card }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap"
        rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    @vite(['resources/js/app.js', 'resources/css/app.css'])
</head>

<body
    class="m-0 max-w-full overflow-x-hidden bg-[radial-gradient(circle_at_18%_8%,rgba(124,58,237,0.08),transparent_32%),radial-gradient(circle_at_80%_16%,rgba(99,102,241,0.06),transparent_30%),#fff] pt-[76px] font-['Tajawal',system-ui,-apple-system,BlinkMacSystemFont,'Segoe_UI',sans-serif] text-[#111827] before:pointer-events-none before:fixed before:inset-0 before:content-[''] before:bg-[linear-gradient(rgba(109,40,217,0.035)_1px,transparent_1px),linear-gradient(90deg,rgba(109,40,217,0.035)_1px,transparent_1px)] before:bg-[size:44px_44px] before:[mask-image:linear-gradient(to_bottom,#000_0%,transparent_65%)] max-[620px]:pt-16 [&_a]:text-inherit [&_a]:no-underline [&_a]:[-webkit-tap-highlight-color:transparent] [&_button]:font-[inherit] [&_button]:[-webkit-tap-highlight-color:transparent] [&_input]:font-[inherit] [&_summary]:font-[inherit] [&_summary]:[-webkit-tap-highlight-color:transparent] [&_a:focus-visible]:outline-0 [&_a:focus-visible]:shadow-[0_0_0_4px_rgba(124,58,237,0.16)] [&_button:focus-visible]:outline-0 [&_button:focus-visible]:shadow-[0_0_0_4px_rgba(124,58,237,0.16)] [&_input:focus-visible]:outline-0 [&_input:focus-visible]:shadow-[0_0_0_4px_rgba(124,58,237,0.16)] [&_summary:focus-visible]:outline-0 [&_summary:focus-visible]:shadow-[0_0_0_4px_rgba(124,58,237,0.16)] [&_.confetti]:pointer-events-none [&_.confetti]:fixed [&_.confetti]:z-[109] [&_.confetti]:h-[18px] [&_.confetti]:w-2.5 [&_.confetti]:rounded-[3px] [&_.confetti]:opacity-0 [&_.confetti]:animate-confetti-burst">
    <div id="wheelAppConfig" hidden data-config="{{ json_encode($wheelConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"></div>
    <header
        class="site-header fixed inset-x-0 top-0 z-50 border-b border-[#e7e2f0] bg-white/[0.92] backdrop-blur-[16px]">
        <div
            class="nav mx-auto grid min-h-[76px] w-[min(calc(100%_-_44px),1320px)] grid-cols-[auto_1fr_auto_auto] items-center gap-[26px] max-[900px]:w-[min(calc(100%_-_28px),1320px)] max-[900px]:grid-cols-[auto_1fr_auto] max-[620px]:min-h-16">
            <a class="brand inline-flex items-center gap-2.5 text-[30px] font-black max-[620px]:text-2xl" href="#home"
                aria-label="نرد">
                <span class="brand__text">نرد</span>
                <span
                    class="brand__wheel relative inline-block h-[42px] w-[42px] overflow-hidden rounded-full border-[3px] border-white bg-[conic-gradient(#fb923c_0_60deg,#22c55e_60deg_120deg,#3b82f6_120deg_180deg,#8b5cf6_180deg_240deg,#ef4444_240deg_300deg,#eab308_300deg_360deg)] shadow-[0_0_0_1px_rgba(109,40,217,0.16),0_8px_20px_rgba(109,40,217,0.2)] after:absolute after:inset-3.5 after:rounded-full after:bg-white after:content-[''] max-[620px]:h-9 max-[620px]:w-9"
                    aria-hidden="true"></span>
            </a>

            <nav
                class="nav-links flex items-center justify-self-center gap-[clamp(18px,4vw,58px)] font-bold text-[#1f2937] max-[900px]:hidden"
                id="navLinks" aria-label="روابط الموقع">
                <a class="active relative pt-7 pb-[23px] [&.active]:text-[#6d28d9] [&.active]:after:absolute [&.active]:after:bottom-[15px] [&.active]:after:left-1/2 [&.active]:after:h-[3px] [&.active]:after:w-[34px] [&.active]:after:-translate-x-1/2 [&.active]:after:rounded-full [&.active]:after:bg-[#6d28d9] [&.active]:after:content-['']"
                    href="#home">الرئيسية</a>
                <a class="relative pt-7 pb-[23px] [&.active]:text-[#6d28d9] [&.active]:after:absolute [&.active]:after:bottom-[15px] [&.active]:after:left-1/2 [&.active]:after:h-[3px] [&.active]:after:w-[34px] [&.active]:after:-translate-x-1/2 [&.active]:after:rounded-full [&.active]:after:bg-[#6d28d9] [&.active]:after:content-['']"
                    href="#how">كيف تعمل العجلة؟</a>
                <!-- <a href="#uses">الاستخدامات</a> -->
                <a class="relative pt-7 pb-[23px] [&.active]:text-[#6d28d9] [&.active]:after:absolute [&.active]:after:bottom-[15px] [&.active]:after:left-1/2 [&.active]:after:h-[3px] [&.active]:after:w-[34px] [&.active]:after:-translate-x-1/2 [&.active]:after:rounded-full [&.active]:after:bg-[#6d28d9] [&.active]:after:content-['']"
                    href="#faq">الأسئلة الشائعة</a>
            </nav>

            <div class="nav-actions flex items-center gap-3 max-[900px]:hidden">
                @auth
                    <details class="group relative">
                        <summary
                            class="inline-flex min-h-11 cursor-pointer list-none items-center justify-center gap-2.5 rounded-xl border border-[#e7e2f0] bg-white px-4 font-extrabold text-[#1f2937] shadow-[0_8px_22px_rgba(76,29,149,0.08)] transition-[border-color,box-shadow] hover:border-violet-300 group-open:border-violet-300 group-open:shadow-[0_12px_30px_rgba(76,29,149,0.13)] [&::-webkit-details-marker]:hidden">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-violet-100 text-sm text-violet-700">
                                <i class="fa-regular fa-user"></i>
                            </span>
                            <span class="max-w-40 truncate">{{ auth()->user()->name }}</span>
                            <i class="fa-solid fa-chevron-down text-xs text-violet-600 transition-transform duration-200 group-open:rotate-180"></i>
                        </summary>
                        <div
                            class="absolute top-[calc(100%+10px)] left-0 z-[60] grid min-w-60 gap-1 rounded-2xl border border-[#e7e2f0] bg-white p-2 text-sm font-bold text-[#344054] shadow-[0_20px_55px_rgba(30,41,59,0.16)]">
                            <a class="flex items-center gap-3 rounded-xl px-3.5 py-3 hover:bg-[#f4efff]" href="{{ route('dashboard') }}">
                                <i class="fa-solid fa-bookmark w-5 text-center text-violet-600"></i>
                                قوائمي
                            </a>
                            <a class="flex items-center gap-3 rounded-xl px-3.5 py-3 hover:bg-[#f4efff]" href="{{ route('profile.edit') }}">
                                <i class="fa-regular fa-user w-5 text-center text-violet-600"></i>
                                الملف الشخصي
                            </a>
                            @if(auth()->user()->isAdmin())
                                <a class="flex items-center gap-3 rounded-xl px-3.5 py-3 hover:bg-[#f4efff]" href="{{ route('admin.dashboard') }}">
                                    <i class="fa-solid fa-shield-halved w-5 text-center text-violet-600"></i>
                                    لوحة الإدارة
                                </a>
                            @endif
                            <form class="mt-1 border-t border-slate-100 pt-1" method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="flex w-full cursor-pointer items-center gap-3 rounded-xl px-3.5 py-3 text-right font-bold text-red-700 hover:bg-red-50" type="submit">
                                    <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center"></i>
                                    تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </details>
                @else
                <a class="btn btn--primary inline-flex min-h-11 items-center justify-center gap-[9px] rounded-xl bg-linear-to-br from-[#7c3aed] to-[#5b21b6] px-5 font-extrabold text-white! shadow-[0_12px_28px_rgba(51,65,85,0.24)]" href="{{ route('login') }}"><i class="fa-solid fa-user"></i>تسجيل الدخول</a>
                <a class="btn btn--ghost inline-flex min-h-11 items-center justify-center gap-[9px] rounded-xl border border-[#e7e2f0] bg-white px-5 font-extrabold text-[#1f2937]" href="{{ route('register') }}"><i class="fa-regular fa-user"></i>إنشاء حساب</a>
                @endauth
            </div>

            <button
                class="mobile-menu hidden h-11 w-11 cursor-pointer rounded-xl border border-[#e7e2f0] bg-white text-[21px] text-[#6d28d9] max-[900px]:inline-grid max-[900px]:place-items-center max-[900px]:justify-self-end"
                type="button" id="mobileMenuBtn" aria-label="فتح القائمة" aria-expanded="false">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <div
            class="mobile-drawer mx-auto mb-3 hidden w-[min(calc(100%_-_28px),1320px)] rounded-[18px] border border-[#e7e2f0] bg-white p-3 shadow-[0_18px_50px_rgba(76,29,149,0.08)] [&.is-open]:grid"
            id="mobileDrawer">
            <a class="active rounded-xl px-3.5 py-[13px] font-extrabold text-[#344054] hover:bg-[#f4efff] hover:text-[#6d28d9] [&.active]:bg-[#f4efff] [&.active]:text-[#6d28d9]"
                href="#home">الرئيسية</a>
            <a class="rounded-xl px-3.5 py-[13px] font-extrabold text-[#344054] hover:bg-[#f4efff] hover:text-[#6d28d9] [&.active]:bg-[#f4efff] [&.active]:text-[#6d28d9]"
                href="#how">كيف تعمل العجلة؟</a>
            <!-- <a href="#uses">الاستخدامات</a> -->
            <a class="rounded-xl px-3.5 py-[13px] font-extrabold text-[#344054] hover:bg-[#f4efff] hover:text-[#6d28d9] [&.active]:bg-[#f4efff] [&.active]:text-[#6d28d9]"
                href="#faq">الأسئلة الشائعة</a>
            <div class="mobile-drawer__actions mt-2.5 grid grid-cols-2 gap-2.5 max-[620px]:grid-cols-1">
                @auth
                    <details class="group col-span-full">
                        <summary
                            class="flex min-h-12 cursor-pointer list-none items-center gap-2.5 rounded-xl border border-violet-100 bg-violet-50 px-3.5 font-extrabold text-[#1f2937] [&::-webkit-details-marker]:hidden">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-violet-200 text-sm text-violet-700">
                                <i class="fa-regular fa-user"></i>
                            </span>
                            <span class="min-w-0 flex-1 truncate">{{ auth()->user()->name }}</span>
                            <i class="fa-solid fa-chevron-down text-xs text-violet-600 transition-transform duration-200 group-open:rotate-180"></i>
                        </summary>
                        <div class="mt-2 grid gap-1 rounded-xl border border-violet-100 bg-white p-2 text-sm font-bold text-[#344054]">
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-[#f4efff]" href="{{ route('dashboard') }}">
                                <i class="fa-solid fa-bookmark w-5 text-center text-violet-600"></i>
                                قوائمي
                            </a>
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-[#f4efff]" href="{{ route('profile.edit') }}">
                                <i class="fa-regular fa-user w-5 text-center text-violet-600"></i>
                                الملف الشخصي
                            </a>
                            @if(auth()->user()->isAdmin())
                                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-[#f4efff]" href="{{ route('admin.dashboard') }}">
                                    <i class="fa-solid fa-shield-halved w-5 text-center text-violet-600"></i>
                                    لوحة الإدارة
                                </a>
                            @endif
                            <form class="mt-1 border-t border-slate-100 pt-1" method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-right font-bold text-red-700 hover:bg-red-50" type="submit">
                                    <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center"></i>
                                    تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </details>
                @else
                    <a class="btn btn--primary inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-violet-700 px-5 font-extrabold text-white!" href="{{ route('login') }}">تسجيل الدخول</a>
                    <a class="btn btn--ghost inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-violet-100 px-5 font-extrabold" href="{{ route('register') }}">إنشاء حساب</a>
                @endauth
            </div>
        </div>
    </header>

    <main id="home">
        @if($campaigns['top'])
            <!-- الإعلان العلوي: الصورة كلها رابط -->
            <section class="h-banner mx-auto w-[min(calc(100%_-_44px),1040px)] max-[620px]:w-[min(calc(100%_-_28px),1040px)]">
                <a class="ad-link ad-link--top mt-[18px] block overflow-hidden rounded-[18px] leading-none shadow-[0_18px_50px_rgba(76,29,149,0.08)] max-[900px]:mt-3 [&_img]:block [&_img]:h-full [&_img]:w-full [&_img]:object-cover"
                    href="{{ route('ads.click', $campaigns['top']) }}"
                    data-ad-impression-url="{{ route('ads.impression', $campaigns['top']) }}"
                    target="_blank" rel="noopener noreferrer sponsored"
                    aria-label="{{ $campaigns['top']->alt_text }}">
                    <img src="{{ Storage::disk('public')->url($campaigns['top']->image_path) }}"
                        alt="{{ $campaigns['top']->alt_text }}" fetchpriority="high" />
                </a>
            </section>
        @endif

        <nav class="wheel-toolbar relative z-[2] mx-auto mt-7 grid w-[min(calc(100%_-_44px),900px)] grid-cols-6 items-stretch rounded-[18px] border border-[#e7e2f0] bg-white/[0.96] px-3 py-3.5 shadow-[0_14px_38px_rgba(76,29,149,0.08),0_2px_8px_rgba(15,23,42,0.03)] max-[900px]:mt-5 max-[900px]:w-[min(calc(100%_-_28px),720px)] max-[900px]:grid-cols-3 max-[900px]:p-2.5 max-[620px]:grid-cols-6 max-[620px]:rounded-2xl max-[620px]:px-[5px] max-[620px]:py-[7px] [&_.wheel-tool]:grid [&_.wheel-tool]:min-h-[78px] [&_.wheel-tool]:min-w-0 [&_.wheel-tool]:cursor-pointer [&_.wheel-tool]:content-center [&_.wheel-tool]:justify-items-center [&_.wheel-tool]:gap-[7px] [&_.wheel-tool]:rounded-none [&_.wheel-tool]:border-0 [&_.wheel-tool]:border-s [&_.wheel-tool]:border-[#ece8f3] [&_.wheel-tool]:bg-transparent [&_.wheel-tool]:px-3.5 [&_.wheel-tool]:py-[7px] [&_.wheel-tool]:text-center [&_.wheel-tool]:text-[#171243] [&_.wheel-tool]:transition-[color,background,transform] [&_.wheel-tool]:duration-200 [&_.wheel-tool:first-child]:border-s-0 [&_.wheel-tool:hover]:-translate-y-px [&_.wheel-tool:hover]:rounded-[13px] [&_.wheel-tool:hover]:bg-[#f4efff] [&_.wheel-tool:hover]:text-[#6d28d9] [&_.wheel-tool:focus-visible]:-translate-y-px [&_.wheel-tool:focus-visible]:rounded-[13px] [&_.wheel-tool:focus-visible]:bg-[#f4efff] [&_.wheel-tool:focus-visible]:text-[#6d28d9] [&_.wheel-tool:focus-visible]:shadow-[0_0_0_4px_rgba(124,58,237,0.16)] [&_.wheel-tool.is-active]:-translate-y-px [&_.wheel-tool.is-active]:rounded-[13px] [&_.wheel-tool.is-active]:bg-[#f4efff] [&_.wheel-tool.is-active]:text-[#6d28d9] [&_.wheel-tool:disabled]:translate-y-0 [&_.wheel-tool:disabled]:cursor-not-allowed [&_.wheel-tool:disabled]:opacity-50 [&_.wheel-tool__icon]:grid [&_.wheel-tool__icon]:h-[34px] [&_.wheel-tool__icon]:w-[34px] [&_.wheel-tool__icon]:place-items-center [&_.wheel-tool__icon]:rounded-[10px] [&_.wheel-tool__icon]:border [&_.wheel-tool__icon]:border-[#e3dcf2] [&_.wheel-tool__icon]:bg-white [&_.wheel-tool__icon]:text-[17px] [&_.wheel-tool__icon]:text-[#6d28d9] [&_.wheel-tool__icon]:shadow-[0_6px_16px_rgba(76,29,149,0.06)] [&_.wheel-tool__copy]:grid [&_.wheel-tool__copy]:min-w-0 [&_.wheel-tool__copy]:gap-0.5 [&_.wheel-tool_strong]:overflow-hidden [&_.wheel-tool_strong]:text-ellipsis [&_.wheel-tool_strong]:whitespace-nowrap [&_.wheel-tool_strong]:text-[13px] [&_.wheel-tool_strong]:font-black [&_.wheel-tool_small]:overflow-hidden [&_.wheel-tool_small]:text-ellipsis [&_.wheel-tool_small]:whitespace-nowrap [&_.wheel-tool_small]:text-[10px] [&_.wheel-tool_small]:font-bold [&_.wheel-tool_small]:text-[#667085] max-[900px]:[&_.wheel-tool:nth-child(4)]:border-s-0 max-[900px]:[&_.wheel-tool:nth-child(-n+3)]:border-b max-[900px]:[&_.wheel-tool:nth-child(-n+3)]:border-[#ece8f3] max-[620px]:[&_.wheel-tool]:min-h-16 max-[620px]:[&_.wheel-tool]:gap-[5px] max-[620px]:[&_.wheel-tool]:border-b-0 max-[620px]:[&_.wheel-tool]:px-0.5 max-[620px]:[&_.wheel-tool]:py-1 max-[620px]:[&_.wheel-tool:not(:first-child)]:border-s max-[620px]:[&_.wheel-tool:not(:first-child)]:border-[#ece8f3] max-[620px]:[&_.wheel-tool:first-child]:border-s-0 max-[620px]:[&_.wheel-tool__icon]:h-[29px] max-[620px]:[&_.wheel-tool__icon]:w-[29px] max-[620px]:[&_.wheel-tool__icon]:rounded-lg max-[620px]:[&_.wheel-tool__icon]:text-sm max-[620px]:[&_.wheel-tool__copy]:gap-0 max-[620px]:[&_.wheel-tool_strong]:overflow-visible max-[620px]:[&_.wheel-tool_strong]:whitespace-normal max-[620px]:[&_.wheel-tool_strong]:text-[clamp(8px,2.35vw,10px)] max-[620px]:[&_.wheel-tool_strong]:leading-tight max-[620px]:[&_.wheel-tool_small]:hidden"
            aria-label="أدوات العجلة">
            <button class="wheel-tool" type="button" id="newWheelBtn">
                <span
                    class="wheel-tool__icon grid h-[34px] w-[34px] place-items-center rounded-[10px] border border-[#e3dcf2] bg-white text-[17px] text-[#6d28d9] shadow-[0_6px_16px_rgba(76,29,149,0.06)] max-[620px]:h-[29px] max-[620px]:w-[29px] max-[620px]:rounded-lg max-[620px]:text-sm"
                    aria-hidden="true">
                    <i class="fa-solid fa-plus"></i>
                </span>
                <span class="wheel-tool__copy grid min-w-0 gap-0.5 max-[620px]:gap-0">
                    <strong>عجلة جديدة</strong>
                    <small>بدء عجلة فارغة</small>
                </span>
            </button>

            <button class="wheel-tool" type="button" id="addNameBtn">
                <span
                    class="wheel-tool__icon grid h-[34px] w-[34px] place-items-center rounded-[10px] border border-[#e3dcf2] bg-white text-[17px] text-[#6d28d9] shadow-[0_6px_16px_rgba(76,29,149,0.06)] max-[620px]:h-[29px] max-[620px]:w-[29px] max-[620px]:rounded-lg max-[620px]:text-sm"
                    aria-hidden="true">
                    <i class="fa-solid fa-user-plus"></i>
                </span>
                <span class="wheel-tool__copy grid min-w-0 gap-0.5 max-[620px]:gap-0">
                    <strong>إضافة اسم</strong>
                    <small>إضافة سريعة</small>
                </span>
            </button>

            <button class="wheel-tool" type="button" id="shuffleBtn">
                <span
                    class="wheel-tool__icon grid h-[34px] w-[34px] place-items-center rounded-[10px] border border-[#e3dcf2] bg-white text-[17px] text-[#6d28d9] shadow-[0_6px_16px_rgba(76,29,149,0.06)] max-[620px]:h-[29px] max-[620px]:w-[29px] max-[620px]:rounded-lg max-[620px]:text-sm"
                    aria-hidden="true">
                    <i class="fa-solid fa-shuffle"></i>
                </span>
                <span class="wheel-tool__copy grid min-w-0 gap-0.5 max-[620px]:gap-0">
                    <strong>خلط الأسماء</strong>
                    <small>خلط عشوائي</small>
                </span>
            </button>

            <label class="wheel-tool" id="importTrigger" for="importInput" role="button" tabindex="0"
                aria-controls="importLoader" aria-disabled="false">
                <span
                    class="wheel-tool__icon grid h-[34px] w-[34px] place-items-center rounded-[10px] border border-[#e3dcf2] bg-white text-[17px] text-[#6d28d9] shadow-[0_6px_16px_rgba(76,29,149,0.06)] max-[620px]:h-[29px] max-[620px]:w-[29px] max-[620px]:rounded-lg max-[620px]:text-sm"
                    aria-hidden="true">
                    <i class="fa-solid fa-upload"></i>
                </span>
                <span class="wheel-tool__copy grid min-w-0 gap-0.5 max-[620px]:gap-0">
                    <strong>استيراد أسماء</strong>
                    <small>من ملف أو نص</small>
                </span>
            </label>
            <input class="sr-only" id="importInput" type="file" accept=".txt,.csv,text/plain,text/csv" />

            <button class="wheel-tool" type="button" id="toolbarFullscreenBtn">
                <span
                    class="wheel-tool__icon grid h-[34px] w-[34px] place-items-center rounded-[10px] border border-[#e3dcf2] bg-white text-[17px] text-[#6d28d9] shadow-[0_6px_16px_rgba(76,29,149,0.06)] max-[620px]:h-[29px] max-[620px]:w-[29px] max-[620px]:rounded-lg max-[620px]:text-sm"
                    aria-hidden="true">
                    <i class="fa-solid fa-expand"></i>
                </span>
                <span class="wheel-tool__copy grid min-w-0 gap-0.5 max-[620px]:gap-0">
                    <strong>تكبير</strong>
                    <small>ملء الشاشة</small>
                </span>
            </button>

            <button class="wheel-tool" type="button" id="toolbarSoundBtn" aria-pressed="false">
                <span
                    class="wheel-tool__icon grid h-[34px] w-[34px] place-items-center rounded-[10px] border border-[#e3dcf2] bg-white text-[17px] text-[#6d28d9] shadow-[0_6px_16px_rgba(76,29,149,0.06)] max-[620px]:h-[29px] max-[620px]:w-[29px] max-[620px]:rounded-lg max-[620px]:text-sm"
                    aria-hidden="true">
                    <i class="fa-solid fa-volume-high"></i>
                </span>
                <span class="wheel-tool__copy grid min-w-0 gap-0.5 max-[620px]:gap-0">
                    <strong>كتم الصوت</strong>
                    <small>كتم / تشغيل</small>
                </span>
            </button>
        </nav>

        <div class="fixed inset-0 z-[120] hidden items-center justify-center bg-slate-950/45 p-5 backdrop-blur-sm"
            id="importLoader" role="status" aria-live="assertive" aria-hidden="true">
            <div class="grid w-[min(100%,390px)] justify-items-center gap-4 rounded-3xl border border-white/60 bg-white p-6 text-center shadow-2xl"
                aria-labelledby="importLoaderTitle" aria-describedby="importLoaderProgress">
                <span class="grid h-14 w-14 place-items-center rounded-full bg-violet-50 text-2xl text-violet-700"
                    aria-hidden="true">
                    <i class="fa-solid fa-spinner animate-spin"></i>
                </span>
                <div class="grid gap-1">
                    <strong class="text-lg font-black text-slate-900" id="importLoaderTitle">جارٍ قراءة الملف…</strong>
                    <span class="text-sm font-bold text-slate-500" id="importLoaderProgress">يتم تجهيز الأسماء</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100" role="progressbar"
                    aria-label="تقدم استيراد الملف" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"
                    id="importProgressTrack">
                    <span class="block h-full w-0 rounded-full bg-linear-to-l from-violet-700 to-indigo-500 transition-[width] duration-200"
                        id="importProgressBar"></span>
                </div>
                <small class="font-bold text-slate-500">يمكن استيراد ملفات TXT وCSV حتى 20 ميجابايت.</small>
            </div>
        </div>

        <section @class([
            'app-shell mx-auto mt-7 grid items-start gap-[30px] max-[1180px]:grid-cols-[minmax(320px,1fr)_390px] max-[900px]:mt-[26px] max-[900px]:w-[min(calc(100%_-_28px),1320px)] max-[900px]:grid-cols-1 max-[900px]:gap-6 max-[620px]:mt-[22px] [&>*]:min-w-0',
            'w-[min(calc(100%_-_44px),1320px)] grid-cols-[250px_minmax(360px,1fr)_410px]' => $campaigns['side'],
            'w-[min(calc(100%_-_44px),1040px)] grid-cols-[minmax(360px,1fr)_410px]' => ! $campaigns['side'],
        ])
            aria-label="تطبيق العجلة">
            @if($campaigns['side'])
                <!-- إعلان جانبي: الصورة كلها رابط -->
                <aside class="side-ad-card rounded-[22px] max-[1180px]:col-span-full max-[900px]:order-3">
                    <a class="ad-link ad-link--side block overflow-hidden rounded-[18px] border border-[#e7e2f0] bg-white leading-none shadow-[0_18px_50px_rgba(76,29,149,0.08)] max-[1180px]:max-h-[220px] max-[900px]:max-h-[260px] max-[620px]:max-h-none [&_img]:block [&_img]:h-full [&_img]:w-full [&_img]:object-cover"
                        href="{{ route('ads.click', $campaigns['side']) }}"
                        data-ad-impression-url="{{ route('ads.impression', $campaigns['side']) }}"
                        target="_blank" rel="noopener noreferrer sponsored"
                        aria-label="{{ $campaigns['side']->alt_text }}">
                        <img src="{{ Storage::disk('public')->url($campaigns['side']->image_path) }}"
                            alt="{{ $campaigns['side']->alt_text }}" loading="lazy" />
                    </a>
                </aside>
            @endif

            <section
                class="wheel-stage grid w-full min-w-0 max-w-full justify-items-center gap-4 [&:fullscreen]:h-screen [&:fullscreen]:w-screen [&:fullscreen]:content-center [&:fullscreen]:justify-items-center [&:fullscreen]:gap-3.5 [&:fullscreen]:overflow-auto [&:fullscreen]:bg-white [&:fullscreen]:p-[clamp(18px,3vw,34px)] [&:fullscreen]:grid-rows-[auto_minmax(0,1fr)_auto_auto_auto]">
                <div class="wheel-wrap relative aspect-square w-[var(--wheel-size)] [--wheel-size:min(100%,520px)] before:absolute before:inset-[9%] before:-z-10 before:animate-wheel-halo before:rounded-full before:bg-[radial-gradient(circle,rgba(51,65,85,0.16),transparent_62%)] before:opacity-[0.55] before:blur-[10px] before:content-[''] [&.is-few]:[--wheel-size:min(100%,570px)] [&.is-many]:[--wheel-size:min(100%,485px)] max-[900px]:[--wheel-size:min(100%,440px)] max-[900px]:[&.is-few]:[--wheel-size:min(100%,480px)] max-[900px]:[&.is-many]:[--wheel-size:min(100%,400px)] max-[620px]:[--wheel-size:min(100%,342px)] max-[620px]:[&.is-few]:[--wheel-size:min(100%,342px)] max-[620px]:[&.is-many]:[--wheel-size:min(100%,342px)] [.wheel-stage:fullscreen_&]:[--wheel-size:min(74vmin,720px)]"
                    id="wheelWrap">
                    <canvas
                        class="block h-full w-full origin-center drop-shadow-[0_24px_40px_rgba(45,21,89,0.14)] transition-transform duration-[5s] ease-[cubic-bezier(0.11,0.75,0.13,1)] will-change-transform"
                        id="wheelCanvas" width="720" height="720"
                        aria-label="عجلة اختيار الأسماء"></canvas>
                    <button
                        class="wheel-center absolute top-1/2 left-1/2 z-[2] grid h-[clamp(62px,15%,88px)] w-[clamp(62px,15%,88px)] -translate-x-1/2 -translate-y-1/2 cursor-pointer place-items-center rounded-full border-0 bg-white text-[clamp(28px,5vw,45px)] font-black text-[#6d28d9] shadow-[inset_0_0_0_8px_#f4f0ff,0_12px_30px_rgba(0,0,0,0.16)] transition-[transform,box-shadow] duration-200 hover:scale-[1.04] hover:shadow-[inset_0_0_0_8px_#f4f0ff,0_16px_34px_rgba(51,65,85,0.24)] active:scale-[0.97] disabled:cursor-not-allowed disabled:scale-100 disabled:opacity-[0.58] disabled:shadow-none max-[620px]:shadow-[inset_0_0_0_6px_#f4f0ff,0_10px_24px_rgba(0,0,0,0.14)] [&.is-loading_i]:animate-spin-control"
                        type="button" id="centerSpinBtn" aria-label="لف العجلة">
                        <i class="fa-solid fa-rotate"></i>
                    </button>
                    <div class="wheel-pointer absolute top-1/2 -left-1 h-[clamp(46px,11%,64px)] w-[clamp(46px,11%,64px)] -translate-y-1/2 animate-pointer-pulse rounded-full bg-linear-to-br from-[#ef4444] to-[#dc2626] [clip-path:polygon(0_50%,78%_8%,78%_35%,100%_35%,100%_65%,78%_65%,78%_92%)]"
                        aria-hidden="true"></div>
                </div>

                <div
                    class="wheel-actions grid w-[min(100%,520px)] grid-cols-[minmax(180px,1fr)] max-[620px]:grid-cols-1 [.wheel-stage:fullscreen_&]:w-[min(520px,calc(100vw_-_32px))] [.wheel-stage:fullscreen_&]:grid-cols-[minmax(160px,420px)] [.wheel-stage:fullscreen_&]:justify-center">
                    <button
                        class="btn btn--primary spin-btn relative isolate inline-flex min-h-[52px] cursor-pointer items-center justify-center justify-self-stretch gap-[9px] overflow-hidden whitespace-nowrap rounded-xl border-0 bg-linear-to-br from-[#7c3aed] to-[#5b21b6] px-5 text-lg font-extrabold text-white shadow-[0_12px_28px_rgba(51,65,85,0.24)] transition-[transform,box-shadow,border-color,background] duration-200 before:absolute before:inset-0 before:translate-x-[115%] before:animate-button-sheen before:bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.34),transparent)] before:content-[''] hover:-translate-y-px disabled:cursor-not-allowed disabled:translate-y-0 disabled:opacity-[0.58] disabled:shadow-none max-[620px]:min-h-12 max-[620px]:text-base [&>*]:relative [&>*]:z-[1] [&.is-loading_i]:animate-spin-control [.wheel-stage:fullscreen_&]:min-h-[52px] [.wheel-stage:fullscreen_&]:w-full [.wheel-stage:fullscreen_&]:px-5 [.wheel-stage:fullscreen_&]:text-lg"
                        type="button" id="spinBtn"
                        aria-label="لف العجلة واختيار اسم">
                        <span class="spin-btn__text">لف العجلة</span>
                        <i class="fa-solid fa-rotate"></i>
                    </button>
                </div>

                <label
                    class="auto-spin inline-flex items-center gap-2 font-bold text-[#667085] [.wheel-stage:fullscreen_&]:hidden">
                    <input id="autoSpin" type="checkbox" />
                    لف تلقائي بعد 5 ثواني
                </label>

                <div class="result-card w-[min(100%,520px)] rounded-2xl border border-[rgba(51,65,85,0.16)] bg-white px-[18px] py-3.5 text-center shadow-[0_16px_35px_rgba(51,65,85,0.08)] [.wheel-stage:fullscreen_&]:w-[min(520px,calc(100vw_-_32px))]"
                    id="resultCard" role="status" aria-live="polite" hidden>
                    <span class="mb-[5px] block font-extrabold text-[#667085]">النتيجة</span>
                    <strong class="block text-[28px] font-black text-[#6d28d9]" id="resultName">—</strong>
                </div>
            </section>

            <aside
                class="panel names-panel overflow-hidden rounded-[22px] border border-[#e7e2f0] bg-white/[0.92] p-[18px] shadow-[0_8px_30px_rgba(15,23,42,0.04)] max-[900px]:w-full">
                <div @class([
                    'mode-tabs grid gap-2.5 [&_button]:inline-flex [&_button]:min-h-[46px] [&_button]:cursor-pointer [&_button]:items-center [&_button]:justify-center [&_button]:gap-2 [&_button]:rounded-[14px] [&_button]:border [&_button]:border-[#e7e2f0] [&_button]:bg-[#fafafa] [&_button]:font-black [&_button]:text-[#344054] [&_button.active]:border-[#d8c7ff] [&_button.active]:bg-white [&_button.active]:text-[#6d28d9] [&_button.active]:shadow-[inset_0_-3px_0_#6d28d9] max-[620px]:[&_button]:min-h-11 max-[620px]:[&_button]:text-sm',
                    'grid-cols-2' => auth()->guest(),
                    'grid-cols-1' => auth()->check(),
                ])
                    aria-label="وضع الاستخدام" role="group">
                    @guest
                        <button class="active" type="button" data-mode="guest" aria-pressed="true">
                            <i class="fa-regular fa-user"></i>
                            وضع الضيف
                        </button>
                    @endguest
                    <button @class(['active' => auth()->check()]) type="button" data-mode="save"
                        aria-pressed="{{ auth()->check() ? 'true' : 'false' }}">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        وضع الحفظ
                    </button>
                </div>

                <div class="mode-hint my-3 mb-3.5 rounded-[14px] bg-[#f4efff] px-[13px] py-[11px] text-sm leading-relaxed font-extrabold text-[#1e293b]"
                    id="modeHint">
                    @auth
                        وضع الحفظ: أنشئ قائمة جديدة أو حمّل إحدى قوائمك المحفوظة.
                    @else
                        وضع الضيف: استخدم العجلة مباشرة بدون حفظ دائم.
                    @endauth
                </div>

                @guest
                    <section class="mb-3.5 hidden rounded-2xl border border-violet-100 bg-white p-3.5 shadow-sm"
                        id="cloudSavePanel" aria-labelledby="cloudSaveTitle">
                        <h3 class="m-0 text-base font-black text-slate-900" id="cloudSaveTitle">تسجيل الدخول مطلوب</h3>
                        <div class="mt-3 grid gap-3" id="guestSaveActions">
                            <div class="rounded-xl bg-violet-50 p-3 text-sm leading-6 text-slate-700">
                                سجّل الدخول لحفظ قوائم الأسماء وفتحها من أي جهاز. ستبقى مسودتك الحالية محفوظة
                                على هذا الجهاز بعد تسجيل الدخول.
                            </div>
                            <a class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-violet-700 px-3 py-2.5 text-center text-sm font-black text-white!"
                                href="{{ route('login') }}">
                                <i class="fa-solid fa-right-to-bracket"></i>
                                تسجيل الدخول
                            </a>
                            <p class="m-0 text-center text-xs font-bold text-slate-500">
                                ليس لديك حساب؟
                                <a class="text-violet-700 underline" href="{{ route('register') }}">أنشئ حسابًا مجانًا</a>
                            </p>
                        </div>
                    </section>
                @endguest

                @auth
                    <section @class([
                        'grid gap-3.5',
                        'hidden' => filled($wheelConfig['savedWheel']),
                    ]) id="savedWheelsBrowser" aria-labelledby="savedWheelsBrowserTitle">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="m-0 text-lg font-black text-slate-900" id="savedWheelsBrowserTitle">قوائمي المحفوظة</h3>
                                <p class="mt-1 text-xs font-bold text-slate-500">ابحث عن قائمة أو أنشئ واحدة جديدة للبدء.</p>
                            </div>
                            <button
                                class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-violet-700 px-3.5 text-sm font-black text-white shadow-sm transition hover:bg-violet-800 disabled:cursor-not-allowed disabled:opacity-55"
                                id="createSavedWheelBtn" type="button"
                                @disabled(! auth()->user()->hasVerifiedEmail())>
                                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                قائمة جديدة
                            </button>
                        </div>

                        <label class="relative block">
                            <span class="sr-only">البحث في القوائم المحفوظة</span>
                            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute top-1/2 right-3.5 -translate-y-1/2 text-slate-400"
                                aria-hidden="true"></i>
                            <input
                                class="min-h-12 w-full rounded-xl border border-slate-200 bg-white pr-10 pl-3.5 text-sm font-bold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                                id="savedWheelsSearch" type="search" maxlength="120" autocomplete="off"
                                placeholder="ابحث باسم القائمة…" />
                        </label>

                        @unless(auth()->user()->hasVerifiedEmail())
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm leading-6 text-amber-900"
                                id="unverifiedSaveActions">
                                يمكنك تحميل قوائمك، ولإنشاء قائمة أو حفظ تعديلاتك يلزم تفعيل بريدك أولًا.
                                <a class="font-black underline" href="{{ route('verification.notice') }}">تفعيل البريد</a>
                            </div>
                        @endunless

                        <p class="m-0 min-h-5 text-xs font-bold text-slate-500" id="savedWheelsStatus"
                            role="status" aria-live="polite"></p>

                        <div class="grid max-h-[410px] gap-2.5 overflow-y-auto pe-1" id="savedWheelsCards"></div>

                        <div class="hidden min-h-36 place-items-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 text-center"
                            id="savedWheelsEmpty">
                            <div class="grid justify-items-center gap-2 text-slate-500">
                                <span class="grid h-11 w-11 place-items-center rounded-full bg-violet-100 text-violet-700">
                                    <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
                                </span>
                                <strong class="text-sm font-black text-slate-800">لا توجد قوائم مطابقة</strong>
                                <span class="text-xs font-bold">أنشئ قائمة جديدة أو غيّر عبارة البحث.</span>
                            </div>
                        </div>

                        <div class="hidden items-center justify-center gap-2 py-3 text-sm font-black text-violet-700"
                            id="savedWheelsLoader" role="status">
                            <i class="fa-solid fa-spinner animate-spin" aria-hidden="true"></i>
                            جارٍ تحميل القوائم…
                        </div>
                        <div class="h-px" id="savedWheelsSentinel" aria-hidden="true"></div>
                    </section>
                @endauth

                <div @class([
                    'wheel-editor',
                    'hidden' => auth()->check() && blank($wheelConfig['savedWheel']),
                ]) id="wheelEditor">
                    @auth
                        <div class="mb-3.5 grid gap-2.5" id="savedWheelEditorHeader">
                            <button
                                class="inline-flex min-h-10 w-fit items-center justify-center gap-2 rounded-xl border border-violet-200 bg-white px-3 text-sm font-black text-violet-700 transition hover:border-violet-300 hover:bg-violet-50 disabled:cursor-wait disabled:opacity-60"
                                id="backToSavedWheelsBtn" type="button">
                                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                العودة إلى قوائمي
                            </button>

                            <div class="grid grid-cols-[auto_minmax(0,1fr)] items-center gap-x-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5"
                                id="savedWheelActiveState">
                                <span class="row-span-2 grid h-8 w-8 place-items-center rounded-lg bg-white text-emerald-700"
                                    aria-hidden="true"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                                <strong class="truncate text-sm font-black text-emerald-900"
                                    id="activeSavedWheelTitle"></strong>
                                <span class="text-xs font-bold text-emerald-700">يتم حفظ تغييرات الأسماء تلقائيًا</span>
                            </div>

                            <p class="m-0 min-h-5 text-xs font-bold text-slate-500" id="saveStatus"
                                role="status" aria-live="polite"></p>

                            <div class="hidden rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900"
                                id="saveConflict">
                                عُدّلت القائمة من جهاز آخر.
                                <div class="mt-2 flex gap-3">
                                    <button class="font-black underline" id="reloadConflictBtn" type="button">تحميل نسخة الخادم</button>
                                    <button class="font-black underline" id="copyConflictBtn" type="button">حفظ الحالية كنسخة</button>
                                </div>
                            </div>
                        </div>
                    @endauth

                <div class="panel-tabs mb-3.5 grid grid-cols-2 gap-2.5 [&_button]:inline-flex [&_button]:min-h-[46px] [&_button]:cursor-pointer [&_button]:items-center [&_button]:justify-center [&_button]:gap-2 [&_button]:rounded-[14px] [&_button]:border [&_button]:border-[#e7e2f0] [&_button]:bg-[#fafafa] [&_button]:font-black [&_button]:text-[#344054] [&_button.active]:border-[#d8c7ff] [&_button.active]:bg-white [&_button.active]:text-[#6d28d9] [&_button.active]:shadow-[inset_0_-3px_0_#6d28d9] max-[620px]:[&_button]:min-h-11 max-[620px]:[&_button]:text-sm"
                    aria-label="تبويبات اللوحة" role="tablist">
                    <button class="active" type="button" data-tab="data" id="dataTab" role="tab"
                        aria-selected="true" aria-controls="dataPage" tabindex="0">
                        <i class="fa-solid fa-list"></i>
                        <span id="dataTabLabel">البيانات (0)</span>
                    </button>
                    <button type="button" data-tab="results" id="resultsTab" role="tab" aria-selected="false"
                        aria-controls="resultsPage" tabindex="-1">
                        <i class="fa-solid fa-trophy"></i>
                        <span id="resultsTabLabel">النتائج (0)</span>
                    </button>
                </div>

                <div class="tab-page active hidden [&.active]:block" id="dataPage" role="tabpanel"
                    aria-labelledby="dataTab">
                    <div
                        class="count-row list-controls grid grid-cols-[28px_auto_minmax(0,1fr)] items-center gap-2.5 rounded-[14px] bg-[#fbfbfd] px-3.5 py-3 text-[#1f2937] max-[620px]:grid-cols-[28px_minmax(0,1fr)]">
                        <span class="list-controls__order w-7" aria-hidden="true"></span>
                        <label
                            class="count-select col-start-2 inline-flex cursor-pointer items-center gap-[9px] [&_input]:h-[18px] [&_input]:w-[18px] [&_input]:cursor-pointer [&_input]:accent-[#6d28d9]">
                            <input id="selectAllNames" type="checkbox" />
                            <strong
                                class="rounded-full bg-[#eefaf2] px-2.5 py-[5px] text-[13px] font-extrabold text-[#16a34a]">تحديد
                                الكل</strong>
                        </label>
                        <strong
                            class="col-start-3 justify-self-start rounded-full bg-[#eefaf2] px-2.5 py-[5px] text-[13px] font-extrabold text-[#16a34a] max-[620px]:col-start-2"
                            id="selectedCount">عدد المحدد (0)</strong>
                    </div>

                    <div class="virtual-list relative mt-3 h-[282px] overflow-auto rounded-2xl border border-[#e7e2f0] bg-white [&.is-empty]:grid [&.is-empty]:place-items-center [&::-webkit-scrollbar]:w-[9px] [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-[#c4bdce] max-[900px]:h-[260px] [&_.name-row]:flex [&_.name-row]:h-[46px] [&_.name-row]:cursor-grab [&_.name-row]:items-center [&_.name-row]:gap-2 [&_.name-row]:border-b [&_.name-row]:border-[#f0eef7] [&_.name-row]:pr-2.5 [&_.name-row]:pl-3 [&_.name-row]:[direction:rtl] [&_.name-row]:transition-[background,box-shadow] [&_.name-row]:duration-200 [&_.name-row.is-dragging]:cursor-grabbing [&_.name-row.is-dragging]:opacity-[0.55] [&_.name-row.is-drop-target]:bg-[#f7f2ff] [&_.name-row.is-drop-target]:shadow-[inset_0_0_0_2px_rgba(51,65,85,0.16)] [&_.name-row_input]:accent-[#6d28d9] [&_.name-row__order]:min-w-7 [&_.name-row__order]:shrink-0 [&_.name-row__order]:text-center [&_.name-row__order]:text-xs [&_.name-row__order]:font-black [&_.name-row__order]:text-[#667085] [&_.name-row__name]:flex-1 [&_.name-row__name]:overflow-hidden [&_.name-row__name]:text-ellipsis [&_.name-row__name]:whitespace-nowrap [&_.name-row__name]:font-bold [&_.name-row__name]:text-[#344054] [&_.name-row__actions]:inline-flex [&_.name-row__actions]:shrink-0 [&_.name-row__actions]:items-center [&_.name-row__actions]:gap-[5px] [&_.name-row__btn]:grid [&_.name-row__btn]:h-[30px] [&_.name-row__btn]:w-[30px] [&_.name-row__btn]:cursor-pointer [&_.name-row__btn]:place-items-center [&_.name-row__btn]:rounded-[9px] [&_.name-row__btn]:border [&_.name-row__btn]:border-[#e7e2f0] [&_.name-row__btn]:bg-white [&_.name-row__btn]:text-[13px] [&_.name-row__btn]:text-[#1e293b] [&_.name-row__btn]:transition-[background,border-color,color,transform] [&_.name-row__btn]:duration-200 [&_.name-row__btn:hover:not(:disabled)]:-translate-y-px [&_.name-row__btn:hover:not(:disabled)]:border-[#cbd5e1] [&_.name-row__btn:hover:not(:disabled)]:bg-[#f4efff] [&_.name-row__btn:disabled]:cursor-not-allowed [&_.name-row__btn:disabled]:opacity-[0.38] [&_.name-row__btn--danger]:text-[#ef4444] [&_.name-row__btn--danger:hover:not(:disabled)]:border-[rgba(239,68,68,0.25)] [&_.name-row__btn--danger:hover:not(:disabled)]:bg-[#fff1f2] max-[620px]:[&_.name-row]:gap-1.5 max-[620px]:[&_.name-row]:px-2 max-[620px]:[&_.name-row__actions]:gap-[3px] max-[620px]:[&_.name-row__btn]:h-7 max-[620px]:[&_.name-row__btn]:w-7 max-[620px]:[&_.name-row__btn]:rounded-lg max-[620px]:[&_.name-row__btn]:text-xs"
                        id="virtualList" role="listbox" aria-label="قائمة الأسماء" aria-live="polite">
                        <div class="virtual-spacer hidden" id="virtualSpacer"></div>
                        <div class="virtual-items relative z-[1]" id="virtualItems"></div>
                        <div class="empty-state empty-state--names grid w-[min(100%,360px)] justify-items-center gap-1.5 rounded-2xl border border-dashed border-[rgba(51,65,85,0.2)] bg-[#fbf9ff] px-4 py-[18px] text-center text-[#667085] [&[hidden]]:hidden"
                            id="emptyNames" hidden>
                            <span
                                class="grid h-[42px] w-[42px] place-items-center rounded-full bg-[#f4efff] text-lg text-[#6d28d9]"><i
                                    class="fa-solid fa-list-check"></i></span>
                            <strong class="font-black text-[#1e293b]">لا توجد أسماء حالياً</strong>
                            <p class="m-0 leading-[1.7] font-bold">أضف اسماً أو استورد ملف أسماء لبدء استخدام العجلة.
                            </p>
                            <div class="mt-2 grid w-full grid-cols-2 gap-2">
                                <button
                                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-violet-700 px-3 text-sm font-black text-white shadow-sm transition hover:bg-violet-800 disabled:cursor-not-allowed disabled:opacity-55"
                                    id="emptyAddNameBtn" type="button">
                                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                                    إضافة اسم
                                </button>
                                <button
                                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-violet-200 bg-white px-3 text-sm font-black text-violet-700 transition hover:border-violet-300 hover:bg-violet-50 disabled:cursor-not-allowed disabled:opacity-55"
                                    id="emptyImportNamesBtn" type="button">
                                    <i class="fa-solid fa-file-arrow-up" aria-hidden="true"></i>
                                    استيراد أسماء
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        class="panel-footer mt-3.5 flex items-center justify-start gap-3 max-[620px]:grid max-[620px]:grid-cols-2">
                        <button
                            class="danger-btn danger-btn--soft inline-flex min-h-[42px] cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-xl border border-[rgba(239,68,68,0.22)] bg-[#fff7f7] px-3.5 font-black text-[#b42318] transition-[transform,box-shadow,border-color,background] duration-200 hover:-translate-y-px disabled:cursor-not-allowed disabled:translate-y-0 disabled:opacity-45 disabled:shadow-none"
                            id="clearSelectedBtn" type="button">
                            <i class="fa-solid fa-trash"></i>
                            مسح المحدد
                        </button>
                        <button
                            class="danger-btn inline-flex min-h-[42px] cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-xl border border-[rgba(239,68,68,0.22)] bg-white px-3.5 font-black text-[#ef4444] transition-[transform,box-shadow,border-color,background] duration-200 hover:-translate-y-px disabled:cursor-not-allowed disabled:translate-y-0 disabled:opacity-45 disabled:shadow-none"
                            id="clearBtn" type="button">
                            <i class="fa-solid fa-trash"></i>
                            مسح الكل
                        </button>
                    </div>
                </div>

                <div class="tab-page hidden [&.active]:block" id="resultsPage" role="tabpanel"
                    aria-labelledby="resultsTab" hidden>
                    <div
                        class="results-head mb-3 flex items-center justify-between gap-3 max-[620px]:flex-col max-[620px]:items-start">
                        <strong class="font-black text-[#1e293b]" id="winnersTitle">الفائزون بالترتيب (0)</strong>
                        <div
                            class="results-head__actions flex flex-wrap items-center justify-end gap-2 max-[620px]:grid max-[620px]:w-full max-[620px]:grid-cols-2">
                            <button
                                class="tool-btn inline-flex min-h-10 cursor-pointer items-center justify-center gap-[7px] whitespace-nowrap rounded-xl border border-[#e7e2f0] bg-white px-2.5 text-sm font-extrabold text-[#475467] transition-[transform,box-shadow,border-color,background] duration-200 hover:-translate-y-px disabled:cursor-not-allowed disabled:translate-y-0 disabled:opacity-[0.58] disabled:shadow-none"
                                type="button" id="restoreAllResultsBtn">
                                <i class="fa-solid fa-rotate-left"></i>
                                إرجاع الكل
                            </button>
                            <button
                                class="tool-btn inline-flex min-h-10 cursor-pointer items-center justify-center gap-[7px] whitespace-nowrap rounded-xl border border-[#e7e2f0] bg-white px-2.5 text-sm font-extrabold text-[#475467] transition-[transform,box-shadow,border-color,background] duration-200 hover:-translate-y-px disabled:cursor-not-allowed disabled:translate-y-0 disabled:opacity-[0.58] disabled:shadow-none"
                                type="button" id="clearResultsBtn">
                                <i class="fa-solid fa-eraser"></i>
                                مسح النتائج
                            </button>
                        </div>
                    </div>
                    <ol class="winners-list m-0 min-h-[282px] max-h-[282px] overflow-auto rounded-2xl border border-[#e7e2f0] bg-white py-0 pr-[22px] pl-0 [&_li]:grid [&_li]:grid-cols-[minmax(0,1fr)_auto] [&_li]:items-center [&_li]:gap-2.5 [&_li]:border-b [&_li]:border-[#f0eef7] [&_li]:px-2.5 [&_li]:py-3 [&_li]:font-extrabold [&_li]:text-[#344054] [&_.winner-entry]:min-w-0 [&_.winner-restore-btn]:grid [&_.winner-restore-btn]:h-[34px] [&_.winner-restore-btn]:w-[34px] [&_.winner-restore-btn]:cursor-pointer [&_.winner-restore-btn]:place-items-center [&_.winner-restore-btn]:rounded-[10px] [&_.winner-restore-btn]:border [&_.winner-restore-btn]:border-[rgba(51,65,85,0.16)] [&_.winner-restore-btn]:bg-white [&_.winner-restore-btn]:text-[#6d28d9] [&_.winner-restore-btn]:transition-[transform,background,border-color] [&_.winner-restore-btn]:duration-200 [&_.winner-restore-btn:hover:not(:disabled)]:-translate-y-px [&_.winner-restore-btn:hover:not(:disabled)]:border-[#cbd5e1] [&_.winner-restore-btn:hover:not(:disabled)]:bg-[#f4efff] [&_.winner-restore-btn:disabled]:cursor-not-allowed [&_.winner-restore-btn:disabled]:opacity-45 [&_.winner-time]:mt-[3px] [&_.winner-time]:block [&_.winner-time]:text-xs [&_.winner-time]:font-bold [&_.winner-time]:text-[#667085]"
                        id="winnersList"></ol>
                    <div class="empty-state empty-results mx-auto mt-3 grid w-[min(100%,360px)] justify-items-center gap-1.5 rounded-2xl border border-dashed border-[rgba(51,65,85,0.2)] bg-[#fbf9ff] px-4 py-[18px] text-center text-[#667085] [&[hidden]]:hidden"
                        id="emptyResults" role="status">
                        <span
                            class="grid h-[42px] w-[42px] place-items-center rounded-full bg-[#f4efff] text-lg text-[#6d28d9]"><i
                                class="fa-regular fa-clock"></i></span>
                        <strong class="font-black text-[#1e293b]">لا توجد نتائج بعد</strong>
                        <p class="m-0 leading-[1.7] font-bold">لف العجلة لإضافة أول فائز إلى القائمة.</p>
                    </div>
                </div>
                </div>
            </aside>
        </section>

        <section
            class="stats-section mx-auto mt-16 w-[min(calc(100%_-_44px),1320px)] max-[900px]:mt-[52px] max-[900px]:w-[min(calc(100%_-_28px),1320px)]">
            <h2 class="m-0 text-center text-[clamp(24px,3vw,32px)] font-black text-[#101828] max-[620px]:text-2xl">
                نشاط المنصة</h2>
            <div class="stats-grid mt-6 grid grid-cols-4 gap-[18px] max-[1180px]:grid-cols-2 max-[620px]:grid-cols-1">
                <article
                    class="stat-card group grid min-h-28 grid-cols-[auto_1fr] items-center gap-x-4 rounded-[18px] border border-[#e7e2f0] bg-white/[0.92] p-[22px] shadow-[0_8px_30px_rgba(15,23,42,0.04)] max-[620px]:min-h-24">
                    <span
                        class="stat-icon row-span-3 grid h-14 w-14 place-items-center rounded-full bg-[#f4efff] text-[22px] font-black text-[#7c3aed] transition-transform duration-200 group-hover:scale-[1.08] group-hover:-rotate-4"><i
                            class="fa-solid fa-rotate"></i></span>
                    <small class="font-extrabold text-[#344054]">عدد مرات الدوران</small>
                    <strong class="text-[25px] font-black text-[#6d28d9]">{{ number_format($publicStats['spins']) }}</strong>
                </article>
                <article
                    class="stat-card group grid min-h-28 grid-cols-[auto_1fr] items-center gap-x-4 rounded-[18px] border border-[#e7e2f0] bg-white/[0.92] p-[22px] shadow-[0_8px_30px_rgba(15,23,42,0.04)] max-[620px]:min-h-24">
                    <span
                        class="stat-icon row-span-3 grid h-14 w-14 place-items-center rounded-full bg-[#ecfeff] text-[22px] font-black text-[#06b6d4] transition-transform duration-200 group-hover:scale-[1.08] group-hover:-rotate-4"><i
                            class="fa-regular fa-bookmark"></i></span>
                    <small class="font-extrabold text-[#344054]">العجلات المحفوظة</small>
                    <strong class="text-[25px] font-black text-[#6d28d9]">{{ number_format($publicStats['wheels']) }}</strong>
                </article>
                <article
                    class="stat-card group grid min-h-28 grid-cols-[auto_1fr] items-center gap-x-4 rounded-[18px] border border-[#e7e2f0] bg-white/[0.92] p-[22px] shadow-[0_8px_30px_rgba(15,23,42,0.04)] max-[620px]:min-h-24">
                    <span
                        class="stat-icon row-span-3 grid h-14 w-14 place-items-center rounded-full bg-[#fffbeb] text-[22px] font-black text-[#f59e0b] transition-transform duration-200 group-hover:scale-[1.08] group-hover:-rotate-4"><i
                            class="fa-regular fa-clock"></i></span>
                    <small class="font-extrabold text-[#344054]">المستخدمون المسجلون</small>
                    <strong class="text-[25px] font-black text-[#6d28d9]">{{ number_format($publicStats['users']) }}</strong>
                </article>
                <article
                    class="stat-card group grid min-h-28 grid-cols-[auto_1fr] items-center gap-x-4 rounded-[18px] border border-[#e7e2f0] bg-white/[0.92] p-[22px] shadow-[0_8px_30px_rgba(15,23,42,0.04)] max-[620px]:min-h-24">
                    <span
                        class="stat-icon row-span-3 grid h-14 w-14 place-items-center rounded-full bg-[#ecfdf5] text-[22px] font-black text-[#22c55e] transition-transform duration-200 group-hover:scale-[1.08] group-hover:-rotate-4"><i
                            class="fa-solid fa-users"></i></span>
                    <small class="font-extrabold text-[#344054]">الأسماء المضافة</small>
                    <strong class="text-[25px] font-black text-[#6d28d9]">{{ number_format($publicStats['names']) }}</strong>
                </article>
            </div>
        </section>

        <section
            class="section-block mx-auto mt-[78px] w-[min(calc(100%_-_44px),1320px)] max-[900px]:mt-[62px] max-[900px]:w-[min(calc(100%_-_28px),1320px)]"
            id="how">
            <div class="section-heading text-center">
                <h2 class="m-0 text-center text-[clamp(24px,3vw,32px)] font-black text-[#101828] max-[620px]:text-2xl">
                    كيف تعمل العجلة؟</h2>
                <p class="mt-2 mb-0 font-bold text-[#667085]">4 خطوات بسيطة لاختيار عادل وسريع</p>
            </div>

            <div class="steps-grid mt-7 grid grid-cols-4 gap-[18px] max-[1180px]:grid-cols-2 max-[620px]:grid-cols-1">
                <article
                    class="step-card group relative min-h-[190px] rounded-[20px] border border-[#e7e2f0] bg-white/[0.92] px-[22px] pt-[30px] pb-6 text-center shadow-[0_8px_30px_rgba(15,23,42,0.04)] max-[620px]:min-h-[164px]">
                    <span
                        class="step-number absolute top-3 right-3.5 grid h-7 w-7 place-items-center rounded-full bg-[#6d28d9] font-black text-white shadow-[0_8px_18px_rgba(51,65,85,0.24)]">1</span>
                    <span
                        class="step-icon text-[42px] leading-none text-[#7c3aed] transition-transform duration-200 group-hover:scale-[1.08] group-hover:-rotate-4"><i
                            class="fa-solid fa-users"></i></span>
                    <h3 class="mt-4 mb-2 text-[19px] font-black">أضف الأسماء</h3>
                    <p class="m-0 leading-[1.75] font-semibold text-[#667085]">أدخل الأسماء يدويًا أو استورد قائمة
                        بسهولة.</p>
                </article>
                <article
                    class="step-card group relative min-h-[190px] rounded-[20px] border border-[#e7e2f0] bg-white/[0.92] px-[22px] pt-[30px] pb-6 text-center shadow-[0_8px_30px_rgba(15,23,42,0.04)] max-[620px]:min-h-[164px]">
                    <span
                        class="step-number absolute top-3 right-3.5 grid h-7 w-7 place-items-center rounded-full bg-[#6d28d9] font-black text-white shadow-[0_8px_18px_rgba(51,65,85,0.24)]">2</span>
                    <span
                        class="step-icon text-[42px] leading-none text-[#06b6d4] transition-transform duration-200 group-hover:scale-[1.08] group-hover:-rotate-4"><i
                            class="fa-solid fa-circle-notch"></i></span>
                    <h3 class="mt-4 mb-2 text-[19px] font-black">لف العجلة</h3>
                    <p class="m-0 leading-[1.75] font-semibold text-[#667085]">اضغط زر الدوران أو استخدم الوضع
                        التلقائي.</p>
                </article>
                <article
                    class="step-card group relative min-h-[190px] rounded-[20px] border border-[#e7e2f0] bg-white/[0.92] px-[22px] pt-[30px] pb-6 text-center shadow-[0_8px_30px_rgba(15,23,42,0.04)] max-[620px]:min-h-[164px]">
                    <span
                        class="step-number absolute top-3 right-3.5 grid h-7 w-7 place-items-center rounded-full bg-[#6d28d9] font-black text-white shadow-[0_8px_18px_rgba(51,65,85,0.24)]">3</span>
                    <span
                        class="step-icon text-[42px] leading-none text-[#f59e0b] transition-transform duration-200 group-hover:scale-[1.08] group-hover:-rotate-4"><i
                            class="fa-solid fa-trophy"></i></span>
                    <h3 class="mt-4 mb-2 text-[19px] font-black">اعرض النتيجة</h3>
                    <p class="m-0 leading-[1.75] font-semibold text-[#667085]">تظهر النتيجة بشكل عادل وفوري.</p>
                </article>
                <article
                    class="step-card group relative min-h-[190px] rounded-[20px] border border-[#e7e2f0] bg-white/[0.92] px-[22px] pt-[30px] pb-6 text-center shadow-[0_8px_30px_rgba(15,23,42,0.04)] max-[620px]:min-h-[164px]">
                    <span
                        class="step-number absolute top-3 right-3.5 grid h-7 w-7 place-items-center rounded-full bg-[#6d28d9] font-black text-white shadow-[0_8px_18px_rgba(51,65,85,0.24)]">4</span>
                    <span
                        class="step-icon text-[42px] leading-none text-[#22c55e] transition-transform duration-200 group-hover:scale-[1.08] group-hover:-rotate-4"><i
                            class="fa-solid fa-share-nodes"></i></span>
                    <h3 class="mt-4 mb-2 text-[19px] font-black">احفظ وشارك النتيجة</h3>
                    <p class="m-0 leading-[1.75] font-semibold text-[#667085]">احفظ النتيجة وشاركها مع الآخرين.</p>
                </article>
            </div>
        </section>

        <section
            class="save-mode mx-auto mt-[78px] grid w-[min(calc(100%_-_44px),1320px)] grid-cols-[300px_minmax(0,1fr)] items-center gap-[clamp(24px,5vw,52px)] rounded-3xl border border-[#e7e2f0] bg-[radial-gradient(circle_at_15%_50%,rgba(245,158,11,0.15),transparent_25%),radial-gradient(circle_at_90%_15%,rgba(34,197,94,0.12),transparent_28%),linear-gradient(135deg,#fbf9ff,#f3f8ff)] p-[clamp(24px,4vw,40px)] shadow-[0_8px_30px_rgba(15,23,42,0.04)] max-[1180px]:grid-cols-1 max-[1180px]:text-center max-[900px]:mt-[62px] max-[900px]:w-[min(calc(100%_-_28px),1320px)] max-[620px]:rounded-[20px] max-[620px]:px-4 max-[620px]:py-[22px]">
            <div class="save-mode__art relative h-[170px] animate-soft-float max-[1180px]:order-first max-[1180px]:mx-auto max-[1180px]:w-[260px]"
                aria-hidden="true">
                <div
                    class="save-screen absolute right-0 bottom-[18px] h-[118px] w-[198px] rounded-[18px] border-[5px] border-white bg-linear-to-b from-[#f8f5ff] to-white shadow-[0_20px_35px_rgba(30,41,59,0.12)] before:absolute before:inset-[22px] before:rounded-[9px] before:bg-[linear-gradient(90deg,#7c3aed_0_12px,transparent_12px_100%),linear-gradient(90deg,#c4b5fd_0_70%,transparent_70%),linear-gradient(90deg,#c4b5fd_0_55%,transparent_55%)] before:bg-[size:100%_18px,100%_10px,100%_10px] before:bg-[position:100%_0,100%_32px,100%_58px] before:bg-no-repeat before:content-['']">
                </div>
                <div
                    class="lock-cloud absolute bottom-0 left-2 grid h-[70px] w-[86px] animate-lock-pulse place-items-center rounded-full bg-linear-to-br from-[#ede9fe] to-[#c4b5fd] text-[30px] text-[#6d28d9] shadow-[0_18px_30px_rgba(30,41,59,0.13)]">
                    <i class="fa-solid fa-lock"></i>
                </div>
            </div>

            <div class="save-mode__content min-w-0">
                <h2 class="mt-3 mb-0 text-[clamp(24px,3vw,34px)] leading-[1.35] font-black text-[#1e293b] max-[620px]:text-2xl">
                    استخدم وضع الحفظ إذا كنت تستخدم العجلة باستمرار</h2>
                <p class="mt-2.5 mb-5 leading-[1.8] font-bold text-[#475467]">
                    احفظ القوائم والنتائج وارجع لها لاحقًا من أي جهاز بعد تسجيل الدخول.
                </p>
                <div class="save-mode__flow mb-[18px] grid grid-cols-3 gap-2.5 max-[620px]:grid-cols-1 [&_span]:flex [&_span]:min-h-[58px] [&_span]:items-center [&_span]:gap-2.5 [&_span]:border-y [&_span]:border-[rgba(51,65,85,0.12)] [&_span]:px-[13px] [&_span]:py-[11px] [&_span]:leading-[1.4] [&_span]:font-black [&_span]:text-[#1e293b] [&_strong]:text-lg [&_strong]:font-black [&_strong]:text-[#f59e0b]"
                    aria-label="مميزات وضع الحفظ">
                    <span><strong>01</strong> قوائمك تبقى جاهزة</span>
                    <span><strong>02</strong> النتائج تحفظ بالترتيب</span>
                    <span><strong>03</strong> الرجوع لها أسرع</span>
                </div>

                <div
                    class="save-mode__actions flex flex-wrap items-center gap-3.5 max-[1180px]:justify-center max-[620px]:grid max-[620px]:justify-items-center">
                    @auth
                        <a class="btn btn--primary inline-flex min-h-11 items-center justify-center gap-[9px] rounded-xl bg-violet-700 px-5 py-2 font-extrabold text-white!" href="{{ route('dashboard') }}">إدارة قوائمي <i class="fa-solid fa-bookmark"></i></a>
                    @else
                        <a class="btn btn--primary inline-flex min-h-11 items-center justify-center gap-[9px] rounded-xl bg-violet-700 px-5 py-2 font-extrabold text-white!" href="{{ route('login') }}">سجل دخول لتفعيل وضع الحفظ <i class="fa-solid fa-user"></i></a>
                    @endauth
                </div>
            </div>
        </section>

        <!-- <section class="container section-block" id="uses">
        <div class="section-heading">
          <h2>متى تحتاج عجلة الأسماء؟</h2>
        </div>

        <div class="uses-grid">
          <article class="use-card">
            <span><i class="fa-solid fa-star"></i></span>
            <h3>أنشطة صفية ومسابقات</h3>
            <p>إضافة المتعة والعدالة في المواقف الصفية.</p>
          </article>
          <article class="use-card">
            <span><i class="fa-solid fa-trophy"></i></span>
            <h3>اختيار فائز</h3>
            <p>اختيار فائز عشوائي للمسابقات والجوائز.</p>
          </article>
          <article class="use-card">
            <span><i class="fa-solid fa-user-group"></i></span>
            <h3>توزيع مجموعات</h3>
            <p>تقسيم المجموعات بشكل عادل وسلس.</p>
          </article>
          <article class="use-card">
            <span><i class="fa-solid fa-user-check"></i></span>
            <h3>اختيار مشاركين</h3>
            <p>اختيار مشاركين عشوائيًا للأنشطة أو العروض.</p>
          </article>
        </div>
      </section> -->

        <section
            class="faq-section mx-auto mt-[76px] w-[min(calc(100%_-_44px),860px)] max-[900px]:mt-[62px] max-[900px]:w-[min(calc(100%_-_28px),860px)]"
            id="faq">
            <div class="section-heading text-center">
                <h2 class="m-0 text-center text-[clamp(24px,3vw,32px)] font-black text-[#101828] max-[620px]:text-2xl">
                    الأسئلة الشائعة</h2>
            </div>

            <div class="faq-list mt-[22px] mb-[50px] grid gap-2.5">
                <details
                    class="group overflow-hidden rounded-[14px] border border-[#e7e2f0] bg-white/[0.92] shadow-[0_8px_30px_rgba(15,23,42,0.04)]"
                    open>
                    <summary
                        class="flex min-h-[54px] cursor-pointer list-none items-center justify-between gap-4 px-[18px] text-right font-black text-[#101828] after:shrink-0 after:text-[15px] after:font-black after:text-[#6d28d9] after:content-['\f078'] after:transition-transform after:duration-200 after:[font-family:'Font_Awesome_6_Free'] group-open:after:rotate-180 [.is-closing_&]:after:rotate-0 max-[620px]:px-3.5 max-[620px]:py-3 max-[620px]:text-[15px] max-[620px]:leading-relaxed [&::-webkit-details-marker]:hidden">
                        هل يمكنني استخدام العجلة بدون تسجيل دخول؟</summary>
                    <p class="m-0 px-[18px] pb-[18px] text-right leading-[1.8] font-bold text-[#667085] group-open:animate-faq-open">
                        نعم، يمكنك استخدام العجلة مباشرة. تسجيل الدخول مطلوب فقط لحفظ
                        القوائم والنتائج.
                    </p>
                </details>
                <details
                    class="group overflow-hidden rounded-[14px] border border-[#e7e2f0] bg-white/[0.92] shadow-[0_8px_30px_rgba(15,23,42,0.04)]">
                    <summary
                        class="flex min-h-[54px] cursor-pointer list-none items-center justify-between gap-4 px-[18px] text-right font-black text-[#101828] after:shrink-0 after:text-[15px] after:font-black after:text-[#6d28d9] after:content-['\f078'] after:transition-transform after:duration-200 after:[font-family:'Font_Awesome_6_Free'] group-open:after:rotate-180 [.is-closing_&]:after:rotate-0 max-[620px]:px-3.5 max-[620px]:py-3 max-[620px]:text-[15px] max-[620px]:leading-relaxed [&::-webkit-details-marker]:hidden">
                        هل بياناتي آمنة؟</summary>
                    <p class="m-0 px-[18px] pb-[18px] text-right leading-[1.8] font-bold text-[#667085] group-open:animate-faq-open">
                        نعم، يتم حفظ القوائم الخاصة بحسابك ولا تظهر للمستخدمين الآخرين.
                    </p>
                </details>
                <details
                    class="group overflow-hidden rounded-[14px] border border-[#e7e2f0] bg-white/[0.92] shadow-[0_8px_30px_rgba(15,23,42,0.04)]">
                    <summary
                        class="flex min-h-[54px] cursor-pointer list-none items-center justify-between gap-4 px-[18px] text-right font-black text-[#101828] after:shrink-0 after:text-[15px] after:font-black after:text-[#6d28d9] after:content-['\f078'] after:transition-transform after:duration-200 after:[font-family:'Font_Awesome_6_Free'] group-open:after:rotate-180 [.is-closing_&]:after:rotate-0 max-[620px]:px-3.5 max-[620px]:py-3 max-[620px]:text-[15px] max-[620px]:leading-relaxed [&::-webkit-details-marker]:hidden">
                        هل يمكن استيراد ملف أسماء؟</summary>
                    <p class="m-0 px-[18px] pb-[18px] text-right leading-[1.8] font-bold text-[#667085] group-open:animate-faq-open">
                        نعم، يمكنك استيراد ملف TXT أو CSV، وكل سطر يتم التعامل معه كاسم
                        مستقل.
                    </p>
                </details>
                <details
                    class="group overflow-hidden rounded-[14px] border border-[#e7e2f0] bg-white/[0.92] shadow-[0_8px_30px_rgba(15,23,42,0.04)]">
                    <summary
                        class="flex min-h-[54px] cursor-pointer list-none items-center justify-between gap-4 px-[18px] text-right font-black text-[#101828] after:shrink-0 after:text-[15px] after:font-black after:text-[#6d28d9] after:content-['\f078'] after:transition-transform after:duration-200 after:[font-family:'Font_Awesome_6_Free'] group-open:after:rotate-180 [.is-closing_&]:after:rotate-0 max-[620px]:px-3.5 max-[620px]:py-3 max-[620px]:text-[15px] max-[620px]:leading-relaxed [&::-webkit-details-marker]:hidden">
                        هل أستطيع تخصيص العجلة؟</summary>
                    <p class="m-0 px-[18px] pb-[18px] text-right leading-[1.8] font-bold text-[#667085] group-open:animate-faq-open">
                        نعم، يمكن تخصيص الأسماء، خلط القائمة، وتشغيل أو إيقاف الصوت والوضع
                        التلقائي.
                    </p>
                </details>
            </div>
        </section>

        @if($campaigns['bottom'])
            <section class="h-banner mx-auto w-[min(calc(100%_-_44px),1040px)] max-[620px]:w-[min(calc(100%_-_28px),1040px)]">
                <a class="ad-link ad-link--top mt-[18px] block overflow-hidden rounded-[18px] leading-none shadow-[0_18px_50px_rgba(76,29,149,0.08)] max-[900px]:mt-3 [&_img]:block [&_img]:h-full [&_img]:w-full [&_img]:object-cover"
                    href="{{ route('ads.click', $campaigns['bottom']) }}"
                    data-ad-impression-url="{{ route('ads.impression', $campaigns['bottom']) }}"
                    target="_blank" rel="noopener noreferrer sponsored"
                    aria-label="{{ $campaigns['bottom']->alt_text }}">
                    <img src="{{ Storage::disk('public')->url($campaigns['bottom']->image_path) }}"
                        alt="{{ $campaigns['bottom']->alt_text }}" loading="lazy" />
                </a>
            </section>
        @endif
    </main>

    <footer
        class="footer mt-[50px] border-t border-white/[0.18] bg-linear-to-br from-[#7c3aed] to-[#5b21b6] p-6 text-center font-bold text-white">
        © 2026 نرد - جميع الحقوق محفوظة</footer>

    <dialog
        class="name-dialog fixed inset-0 m-auto rounded-[20px] border-0 p-0 shadow-[0_28px_90px_rgba(17,24,39,0.22)] backdrop:bg-[rgba(17,24,39,0.35)] backdrop:backdrop-blur-[4px]"
        id="nameDialog">
        <form class="w-[min(420px,calc(100vw_-_40px))] p-6" method="dialog">
            <h3 class="mt-0 mb-4 text-2xl">إضافة اسم جديد</h3>
            <input
                class="min-h-12 w-full rounded-[14px] border border-[#e7e2f0] px-3.5 outline-none focus:border-[#cbd5e1] focus:shadow-[0_0_0_4px_rgba(51,65,85,0.12)]"
                id="nameInput" type="text" placeholder="اكتب الاسم هنا" />
            <div class="dialog-actions mt-[18px] flex justify-start gap-2.5">
                <button
                    class="btn btn--ghost inline-flex min-h-11 cursor-pointer items-center justify-center gap-[9px] whitespace-nowrap rounded-xl border border-[#e7e2f0] bg-white px-5 font-extrabold text-[#1f2937] transition-[transform,box-shadow,border-color,background] duration-200 hover:-translate-y-px disabled:cursor-not-allowed disabled:opacity-[0.58] disabled:shadow-none disabled:hover:translate-y-0"
                    value="cancel" type="submit">
                    إلغاء
                </button>
                <button
                    class="btn btn--primary inline-flex min-h-11 cursor-pointer items-center justify-center gap-[9px] whitespace-nowrap rounded-xl border-0 bg-linear-to-br from-[#7c3aed] to-[#5b21b6] px-5 font-extrabold text-white shadow-[0_12px_28px_rgba(51,65,85,0.24)] transition-[transform,box-shadow,border-color,background] duration-200 hover:-translate-y-px disabled:cursor-not-allowed disabled:opacity-[0.58] disabled:shadow-none disabled:hover:translate-y-0"
                    value="confirm" id="confirmAddName" type="submit">
                    إضافة
                </button>
            </div>
        </form>
    </dialog>

    @auth
        <dialog
            class="fixed inset-0 m-auto max-h-[calc(100dvh_-_32px)] overflow-y-auto rounded-3xl border-0 p-0 text-right shadow-[0_30px_100px_rgba(17,24,39,0.28)] backdrop:bg-slate-950/45 backdrop:backdrop-blur-[4px]"
            id="createSavedWheelDialog" aria-labelledby="createSavedWheelDialogTitle">
            <form class="w-[min(440px,calc(100vw_-_32px))] p-5 sm:p-6" id="createSavedWheelForm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="m-0 text-xl font-black text-slate-900" id="createSavedWheelDialogTitle">إنشاء قائمة جديدة</h3>
                        <p class="mt-1 text-sm font-bold leading-6 text-slate-500">
                            اكتب اسمًا واضحًا للقائمة. ستُفتح فارغة لتضيف الأسماء يدويًا أو من ملف.
                        </p>
                    </div>
                    <button class="grid h-9 w-9 shrink-0 place-items-center rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-900"
                        type="button" data-close-dialog aria-label="إغلاق نافذة إنشاء القائمة">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                @if(auth()->user()->hasVerifiedEmail())
                    <div class="mt-5 grid gap-4">
                        <div class="flex items-center gap-3 rounded-xl bg-violet-50 px-3 py-2.5 text-sm font-bold text-violet-800">
                            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                            <span>يمكنك حفظ حتى 2,000 اسم داخل كل قائمة.</span>
                        </div>
                        <label class="grid gap-1.5 text-sm font-black text-slate-700">
                            اسم القائمة
                            <input class="min-h-12 rounded-xl border border-slate-200 px-3.5 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                                id="savedWheelTitle" maxlength="120" autocomplete="off"
                                placeholder="مثال: الصف الثاني - الشعبة الأولى">
                        </label>
                        <p class="m-0 min-h-5 text-xs font-bold text-slate-500" id="createSavedWheelStatus"
                            role="status" aria-live="polite"></p>
                        <div class="grid grid-cols-2 gap-2.5">
                            <button class="min-h-11 rounded-xl border border-slate-200 bg-white px-4 font-black text-slate-600 hover:bg-slate-50"
                                type="button" data-close-dialog>إلغاء</button>
                            <button class="min-h-11 rounded-xl bg-violet-700 px-4 font-black text-white hover:bg-violet-800 disabled:cursor-wait disabled:opacity-60"
                                id="confirmCreateSavedWheelBtn" type="submit">
                                إنشاء وفتح القائمة
                            </button>
                        </div>
                    </div>
                @else
                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-7 text-amber-900">
                        فعّل بريدك الإلكتروني أولًا لتتمكن من إنشاء القوائم وحفظها.
                        <a class="mt-3 flex min-h-11 items-center justify-center rounded-xl bg-amber-600 px-4 font-black text-white!"
                            href="{{ route('verification.notice') }}">تفعيل البريد</a>
                    </div>
                @endif
            </form>
        </dialog>

    @endauth

    <div class="celebration pointer-events-none fixed inset-0 z-[110] hidden place-items-center bg-[radial-gradient(circle_at_50%_42%,rgba(245,158,11,0.16),transparent_34%),radial-gradient(circle,rgba(51,65,85,0.2),transparent_50%)] [&.is-show]:pointer-events-auto [&.is-show]:grid"
        id="celebration" aria-hidden="true">
        <div
            class="celebration__box relative min-w-[min(440px,calc(100vw_-_36px))] animate-celebration rounded-[26px] border border-[rgba(51,65,85,0.18)] bg-white/[0.94] p-7 text-center shadow-[0_30px_90px_rgba(30,41,59,0.25)]">
            <button
                class="celebration__close absolute top-3 left-3 grid h-[38px] w-[38px] cursor-pointer place-items-center rounded-full border border-[rgba(51,65,85,0.14)] bg-white text-lg text-[#1e293b] shadow-[0_10px_24px_rgba(30,41,59,0.12)] hover:border-[rgba(220,38,38,0.22)] hover:text-[#dc2626]"
                type="button" id="celebrationCloseBtn" aria-label="إيقاف الاحتفال">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div
                class="celebration__icon mx-auto mb-3 grid h-[76px] w-[76px] animate-trophy-pulse place-items-center rounded-full bg-linear-to-br from-[#fbbf24] to-[#f59e0b] text-[34px] text-white">
                <i class="fa-solid fa-trophy"></i>
            </div>
            <span class="block font-black text-[#667085]">مبروك للفائز</span>
            <strong class="mt-1.5 block text-[clamp(28px,5vw,44px)] font-black text-[#6d28d9]"
                id="celebrationName">—</strong>
        </div>
    </div>

    <button
        class="back-to-top pointer-events-none fixed bottom-[18px] left-[18px] z-80 grid h-[42px] w-[42px] translate-y-2.5 cursor-pointer place-items-center rounded-xl border border-[rgba(51,65,85,0.18)] bg-white text-[#6d28d9] opacity-0 shadow-[0_14px_34px_rgba(30,41,59,0.16)] transition-[opacity,transform,background,color] duration-200 hover:bg-[#6d28d9] hover:text-white [&.is-visible]:pointer-events-auto [&.is-visible]:translate-y-0 [&.is-visible]:opacity-100"
        type="button" id="backToTopBtn" aria-label="الرجوع للأعلى" title="الرجوع للأعلى">
        <i class="fa-solid fa-arrow-up"></i>
    </button>
</body>

</html>
