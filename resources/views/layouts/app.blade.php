<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') | معلم</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f8f7fc] pt-[76px] font-['Tajawal',sans-serif] text-slate-900 max-[620px]:pt-16">
    <header
        class="site-header fixed inset-x-0 top-0 z-50 border-b border-[#e7e2f0] bg-white/[0.92] backdrop-blur-[16px]">
        <div
            class="mx-auto grid min-h-[76px] w-[min(calc(100%_-_44px),1320px)] grid-cols-[auto_1fr_auto_auto] items-center gap-[26px] max-[900px]:w-[min(calc(100%_-_28px),1320px)] max-[900px]:grid-cols-[auto_1fr_auto] max-[620px]:min-h-16">
            <a class="inline-flex items-center" href="{{ route('home') }}" aria-label="معلم - الصفحة الرئيسية">
                <img class="h-11 w-auto max-[620px]:h-10" src="{{ asset('assets/logo.png') }}" alt="معلم">
            </a>

            <nav class="flex items-center justify-self-center gap-[clamp(18px,4vw,58px)] font-bold text-[#1f2937] max-[900px]:hidden"
                aria-label="روابط الموقع">
                <a class="relative pt-7 pb-[23px] hover:text-[#6d28d9]" href="{{ route('home') }}">الرئيسية</a>
                <a class="relative pt-7 pb-[23px] hover:text-[#6d28d9]" href="{{ route('tools.wheel') }}">عجلة
                    الأسماء</a>
                <a class="relative pt-7 pb-[23px] hover:text-[#6d28d9]" href="{{ route('home') }}#tools">الأدوات</a>
            </nav>

            <div class="flex items-center gap-3 max-[900px]:hidden">
                <details class="group relative">
                    <summary
                        class="inline-flex min-h-11 cursor-pointer list-none items-center justify-center gap-2.5 rounded-xl border border-[#e7e2f0] bg-white px-4 font-extrabold text-[#1f2937] shadow-[0_8px_22px_rgba(76,29,149,0.08)] transition-[border-color,box-shadow] hover:border-violet-300 group-open:border-violet-300 group-open:shadow-[0_12px_30px_rgba(76,29,149,0.13)] [&::-webkit-details-marker]:hidden">
                        <span
                            class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-violet-100 text-sm text-violet-700">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <span class="max-w-40 truncate">{{ auth()->user()->name }}</span>
                        <i
                            class="fa-solid fa-chevron-down text-xs text-violet-600 transition-transform duration-200 group-open:rotate-180"></i>
                    </summary>
                    <div
                        class="absolute top-[calc(100%+10px)] left-0 z-[60] grid min-w-60 gap-1 rounded-2xl border border-[#e7e2f0] bg-white p-2 text-sm font-bold text-[#344054] shadow-[0_20px_55px_rgba(30,41,59,0.16)]">
                        <a class="flex items-center gap-3 rounded-xl px-3.5 py-3 hover:bg-[#f4efff]"
                            href="{{ route('dashboard') }}">
                            <i class="fa-solid fa-bookmark w-5 text-center text-violet-600"></i>
                            مسابقاتي وقوائمي
                        </a>
                        <a class="flex items-center gap-3 rounded-xl px-3.5 py-3 hover:bg-[#f4efff]"
                            href="{{ route('profile.edit') }}">
                            <i class="fa-regular fa-user w-5 text-center text-violet-600"></i>
                            الملف الشخصي
                        </a>
                        @if (auth()->user()->isAdmin())
                            <a class="flex items-center gap-3 rounded-xl px-3.5 py-3 hover:bg-[#f4efff]"
                                href="{{ route('admin.dashboard') }}">
                                <i class="fa-solid fa-shield-halved w-5 text-center text-violet-600"></i>
                                لوحة الإدارة
                            </a>
                        @endif
                        <form class="mt-1 border-t border-slate-100 pt-1" method="POST"
                            action="{{ route('logout') }}">
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
            </div>

            <button
                class="hidden h-11 w-11 cursor-pointer rounded-xl border border-[#e7e2f0] bg-white text-[21px] text-[#6d28d9] max-[900px]:inline-grid max-[900px]:place-items-center max-[900px]:justify-self-end"
                type="button" id="mobileMenuBtn" aria-label="فتح القائمة" aria-expanded="false">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <div class="mx-auto mb-3 hidden w-[min(calc(100%_-_28px),1320px)] rounded-[18px] border border-[#e7e2f0] bg-white p-3 shadow-[0_18px_50px_rgba(76,29,149,0.08)] [&.is-open]:grid"
            id="mobileDrawer">
            <a class="rounded-xl px-3.5 py-[13px] font-extrabold text-[#344054] hover:bg-[#f4efff] hover:text-[#6d28d9]"
                href="{{ route('home') }}">الرئيسية</a>
            <a class="rounded-xl px-3.5 py-[13px] font-extrabold text-[#344054] hover:bg-[#f4efff] hover:text-[#6d28d9]"
                href="{{ route('tools.wheel') }}">عجلة الأسماء</a>
            <a class="rounded-xl px-3.5 py-[13px] font-extrabold text-[#344054] hover:bg-[#f4efff] hover:text-[#6d28d9]"
                href="{{ route('home') }}#tools">الأدوات</a>
            <details class="group mt-2.5">
                <summary
                    class="flex min-h-12 cursor-pointer list-none items-center gap-2.5 rounded-xl border border-violet-100 bg-violet-50 px-3.5 font-extrabold text-[#1f2937] [&::-webkit-details-marker]:hidden">
                    <span
                        class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-violet-200 text-sm text-violet-700">
                        <i class="fa-regular fa-user"></i>
                    </span>
                    <span class="min-w-0 flex-1 truncate">{{ auth()->user()->name }}</span>
                    <i
                        class="fa-solid fa-chevron-down text-xs text-violet-600 transition-transform duration-200 group-open:rotate-180"></i>
                </summary>
                <div
                    class="mt-2 grid gap-1 rounded-xl border border-violet-100 bg-white p-2 text-sm font-bold text-[#344054]">
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-[#f4efff]"
                        href="{{ route('dashboard') }}">
                        <i class="fa-solid fa-bookmark w-5 text-center text-violet-600"></i>
                        مسابقاتي وقوائمي
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-[#f4efff]"
                        href="{{ route('profile.edit') }}">
                        <i class="fa-regular fa-user w-5 text-center text-violet-600"></i>
                        الملف الشخصي
                    </a>
                    @if (auth()->user()->isAdmin())
                        <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-[#f4efff]"
                            href="{{ route('admin.dashboard') }}">
                            <i class="fa-solid fa-shield-halved w-5 text-center text-violet-600"></i>
                            لوحة الإدارة
                        </a>
                    @endif
                    <form class="mt-1 border-t border-slate-100 pt-1" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-right font-bold text-red-700 hover:bg-red-50"
                            type="submit">
                            <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center"></i>
                            تسجيل الخروج
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </header>

    <main class="mx-auto w-[min(calc(100%_-_2rem),1180px)] py-8">
        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 font-bold text-emerald-800"
                role="status">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
</body>

</html>
