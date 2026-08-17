<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'الإدارة') | معلم</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $adminNavigation = [
        ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'fa-chart-pie', 'label' => 'نظرة عامة'],
        ['route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => 'fa-users', 'label' => 'المستخدمون'],
        [
            'route' => 'admin.saved-wheels.index',
            'active' => 'admin.saved-wheels.*',
            'icon' => 'fa-list-check',
            'label' => 'القوائم',
        ],
        [
            'route' => 'admin.ad-campaigns.index',
            'active' => 'admin.ad-campaigns.*',
            'icon' => 'fa-rectangle-ad',
            'label' => 'الحملات الإعلانية',
        ],
        [
            'route' => 'admin.qr-templates.index',
            'active' => 'admin.qr-templates.*',
            'icon' => 'fa-qrcode',
            'label' => 'قوالب QR',
        ],
        [
            'route' => 'admin.certificate-templates.index',
            'active' => 'admin.certificate-templates.*',
            'icon' => 'fa-award',
            'label' => 'قوالب الشهادات',
        ],
        [
            'route' => 'admin.analytics',
            'active' => 'admin.analytics',
            'icon' => 'fa-chart-line',
            'label' => 'الإحصائيات',
        ],
        [
            'route' => 'admin.seo.edit',
            'active' => 'admin.seo.*',
            'icon' => 'fa-magnifying-glass-chart',
            'label' => 'إعدادات SEO',
        ],
        [
            'route' => 'admin.audit-logs',
            'active' => 'admin.audit-logs',
            'icon' => 'fa-clock-rotate-left',
            'label' => 'سجل الإدارة',
        ],
    ];
@endphp

<body
    class="min-h-screen bg-[radial-gradient(circle_at_top_right,rgba(124,58,237,0.08),transparent_30%),#f8f7fc] pt-[76px] font-['Tajawal',sans-serif] text-slate-900 max-[620px]:pt-16">
    <header class="fixed inset-x-0 top-0 z-50 border-b border-[#e7e2f0] bg-white/[0.94] backdrop-blur-[16px]">
        <div
            class="mx-auto grid min-h-[76px] w-[min(calc(100%_-_32px),1500px)] grid-cols-[auto_1fr_auto] items-center gap-4 max-[620px]:min-h-16">
            <a class="inline-flex items-center" href="{{ route('home') }}" aria-label="معلم - الصفحة الرئيسية">
                <img class="h-11 w-auto max-[620px]:h-10" src="{{ asset('assets/logo.png') }}" alt="معلم">
            </a>

            <div
                class="justify-self-center rounded-full bg-violet-50 px-4 py-2 text-sm font-black text-violet-700 max-[700px]:hidden">
                <i class="fa-solid fa-shield-halved ms-2"></i>
                لوحة الإدارة
            </div>

            <details class="group relative justify-self-end max-[900px]:hidden">
                <summary
                    class="inline-flex min-h-11 cursor-pointer list-none items-center gap-2.5 rounded-xl border border-[#e7e2f0] bg-white px-3.5 font-extrabold shadow-sm hover:border-violet-300 group-open:border-violet-300 [&::-webkit-details-marker]:hidden">
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-violet-100 text-sm text-violet-700">
                        <i class="fa-regular fa-user"></i>
                    </span>
                    <span class="max-w-36 truncate">{{ auth()->user()->name }}</span>
                    <i
                        class="fa-solid fa-chevron-down text-xs text-violet-600 transition-transform group-open:rotate-180"></i>
                </summary>
                <div
                    class="absolute top-[calc(100%+10px)] left-0 z-[60] grid min-w-56 gap-1 rounded-2xl border border-[#e7e2f0] bg-white p-2 text-sm font-bold shadow-[0_20px_55px_rgba(30,41,59,0.16)]">
                    <a class="flex items-center gap-3 rounded-xl px-3.5 py-3 hover:bg-violet-50"
                        id="adminBackToSiteLink" href="{{ route('home') }}">
                        <i class="fa-solid fa-house w-5 text-center text-violet-600"></i>
                        العودة للموقع
                    </a>
                    <a class="flex items-center gap-3 rounded-xl px-3.5 py-3 hover:bg-violet-50"
                        href="{{ route('profile.edit') }}">
                        <i class="fa-regular fa-user w-5 text-center text-violet-600"></i>
                        الملف الشخصي
                    </a>
                    <form class="mt-1 border-t border-slate-100 pt-1" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            class="flex w-full cursor-pointer items-center gap-3 rounded-xl px-3.5 py-3 text-right font-bold text-red-700 hover:bg-red-50"
                            type="submit">
                            <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center"></i>
                            تسجيل الخروج
                        </button>
                    </form>
                </div>
            </details>

            <button
                class="hidden h-11 w-11 cursor-pointer rounded-xl border border-[#e7e2f0] bg-white text-xl text-violet-700 max-[900px]:inline-grid max-[900px]:place-items-center max-[900px]:justify-self-end"
                id="mobileMenuBtn" type="button" aria-label="فتح قائمة الإدارة" aria-expanded="false">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <div class="mx-auto mb-3 hidden w-[min(calc(100%_-_28px),720px)] rounded-[18px] border border-[#e7e2f0] bg-white p-3 shadow-[0_18px_50px_rgba(76,29,149,0.12)] [&.is-open]:grid"
            id="mobileDrawer">
            <p class="px-3.5 pt-1 pb-2 text-xs font-black tracking-wider text-violet-600">لوحة الإدارة</p>
            @foreach ($adminNavigation as $item)
                <a @class([
                    'flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-extrabold transition-colors',
                    'bg-violet-700 text-white' => request()->routeIs($item['active']),
                    'text-slate-600 hover:bg-violet-50 hover:text-violet-700' => !request()->routeIs(
                        $item['active']),
                ]) href="{{ route($item['route']) }}">
                    <i class="fa-solid {{ $item['icon'] }} w-5 text-center"></i>
                    {{ $item['label'] }}
                </a>
            @endforeach
            <div class="mt-2 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3">
                <a class="rounded-xl bg-slate-100 px-3 py-3 text-center text-sm font-bold text-slate-700"
                    id="adminMobileBackToSiteLink" href="{{ route('home') }}">الموقع</a>
                <a class="rounded-xl bg-violet-50 px-3 py-3 text-center text-sm font-bold text-violet-700"
                    href="{{ route('profile.edit') }}">حسابي</a>
            </div>
        </div>
    </header>

    <aside
        class="fixed inset-y-[76px] right-0 hidden w-[280px] border-l border-violet-100 bg-white/[0.9] p-5 backdrop-blur-xl lg:flex lg:flex-col">
        <div
            class="rounded-2xl bg-linear-to-br from-violet-700 to-indigo-700 p-4 text-white shadow-[0_16px_35px_rgba(91,33,182,0.2)]">
            <p class="text-xs font-bold text-violet-100">مساحة التحكم</p>
            <p class="mt-1 text-lg font-black">إدارة منصة معلم</p>
            <p class="mt-1 text-xs leading-5 text-violet-100">المستخدمون والمحتوى والأداء من مكان واحد.</p>
        </div>
        <nav class="mt-5 grid gap-1.5" aria-label="أقسام لوحة الإدارة">
            @foreach ($adminNavigation as $item)
                <a @class([
                    'flex min-h-12 items-center gap-3 rounded-xl px-3.5 text-sm font-extrabold transition-[color,background,transform,box-shadow]',
                    'bg-violet-700 text-white shadow-[0_10px_25px_rgba(109,40,217,0.2)]' => request()->routeIs(
                        $item['active']),
                    'text-slate-600 hover:translate-x-[-2px] hover:bg-violet-50 hover:text-violet-700' => !request()->routeIs(
                        $item['active']),
                ]) href="{{ route($item['route']) }}">
                    <span @class([
                        'grid h-8 w-8 place-items-center rounded-lg',
                        'bg-white/15' => request()->routeIs($item['active']),
                        'bg-violet-100 text-violet-700' => !request()->routeIs($item['active']),
                    ])>
                        <i class="fa-solid {{ $item['icon'] }}"></i>
                    </span>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <a class="mt-auto flex items-center justify-center gap-2 rounded-xl border border-violet-200 px-4 py-3 text-sm font-black text-violet-700 hover:bg-violet-50"
            href="{{ route('home') }}">
            <i class="fa-solid fa-arrow-right"></i>
            العودة إلى الموقع
        </a>
    </aside>

    <main class="min-w-0 px-4 py-6 sm:px-6 sm:py-8 lg:pr-[304px] lg:pl-6">
        <div class="mx-auto max-w-7xl">
            @if (session('status'))
                <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 font-bold text-emerald-800 shadow-sm"
                    role="status">
                    <i class="fa-solid fa-circle-check mt-1"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 shadow-sm"
                    role="alert">
                    <i class="fa-solid fa-circle-exclamation mt-1"></i>
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</body>

</html>
