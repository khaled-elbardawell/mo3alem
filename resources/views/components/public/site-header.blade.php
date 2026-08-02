@props(['active' => ''])

<header class="site-header fixed inset-x-0 top-0 z-50 border-b border-violet-100/80 bg-white/95 backdrop-blur-xl">
    <div class="nav mx-auto flex min-h-20 w-[min(calc(100%_-_2rem),1280px)] items-center justify-between gap-6">
        <a class="brand inline-flex shrink-0 items-center" href="{{ route('home') }}" aria-label="معلم - الصفحة الرئيسية">
            <img class="h-11 w-auto sm:h-12" src="{{ asset('assets/logo.png') }}" alt="معلم">
        </a>

        <nav class="nav-links hidden items-center gap-7 text-sm font-extrabold text-slate-700 xl:flex"
            aria-label="التنقل الرئيسي">
            <a class="border-b-2 border-transparent py-7 transition-[color,border-color,transform] duration-300 hover:text-violet-700 data-active:-translate-y-0.5 data-active:border-violet-700 data-active:text-violet-800"
                href="{{ route('home') }}#home"
                @if ($active === 'home') data-scrollspy-target="home" @endif
                @if ($active === 'home') data-active aria-current="location" @endif>الرئيسية</a>
            <a class="border-b-2 border-transparent py-7 transition-[color,border-color,transform] duration-300 hover:text-violet-700 data-active:-translate-y-0.5 data-active:border-violet-700 data-active:text-violet-800"
                href="{{ route('home') }}#tools"
                @if ($active === 'home') data-scrollspy-target="tools" @endif>الأدوات</a>
            <a class="border-b-2 border-transparent py-7 transition-[color,border-color,transform] duration-300 hover:text-violet-700 data-active:-translate-y-0.5 data-active:border-violet-700 data-active:text-violet-800"
                href="{{ route('home') }}#features"
                @if ($active === 'home') data-scrollspy-target="features" @endif>مميزاتنا</a>
            <a class="border-b-2 border-transparent py-7 transition-[color,border-color,transform] duration-300 hover:text-violet-700 data-active:-translate-y-0.5 data-active:border-violet-700 data-active:text-violet-800"
                href="{{ route('home') }}#activity"
                @if ($active === 'home') data-scrollspy-target="activity" @endif>نشاط المنصة</a>
            <a class="border-b-2 border-transparent py-7 transition-[color,border-color,transform] duration-300 hover:text-violet-700 data-active:-translate-y-0.5 data-active:border-violet-700 data-active:text-violet-800"
                href="{{ route('home') }}#how"
                @if ($active === 'home') data-scrollspy-target="how" @endif>كيف تعمل المنصة؟</a>
            <a class="border-b-2 border-transparent py-7 transition-[color,border-color,transform] duration-300 hover:text-violet-700 data-active:-translate-y-0.5 data-active:border-violet-700 data-active:text-violet-800"
                href="{{ route('home') }}#faq"
                @if ($active === 'home') data-scrollspy-target="faq" @endif>الأسئلة الشائعة</a>
        </nav>

        <div class="hidden items-center gap-2.5 xl:flex">
            @auth
                <details class="group relative">
                    <summary
                        class="inline-flex min-h-11 list-none items-center gap-2 rounded-xl border border-violet-100 bg-white px-3.5 font-extrabold text-slate-800 shadow-sm hover:border-violet-300 [&::-webkit-details-marker]:hidden">
                        <span class="grid h-8 w-8 place-items-center rounded-full bg-violet-100 text-violet-700">
                            <i class="fa-regular fa-user" aria-hidden="true"></i>
                        </span>
                        <span class="max-w-36 truncate">{{ auth()->user()->name }}</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-violet-500 transition-transform group-open:rotate-180"
                            aria-hidden="true"></i>
                    </summary>
                    <div
                        class="absolute top-[calc(100%+10px)] left-0 z-50 grid min-w-60 gap-1 rounded-2xl border border-violet-100 bg-white p-2 text-sm font-bold text-slate-700 shadow-2xl">
                        <a class="flex items-center gap-3 rounded-xl px-3.5 py-3 hover:bg-violet-50"
                            href="{{ route('dashboard') }}">
                            <i class="fa-solid fa-table-columns w-5 text-center text-violet-600" aria-hidden="true"></i>
                            لوحة التحكم
                        </a>
                        <a class="flex items-center gap-3 rounded-xl px-3.5 py-3 hover:bg-violet-50"
                            href="{{ route('profile.edit') }}">
                            <i class="fa-regular fa-user w-5 text-center text-violet-600" aria-hidden="true"></i>
                            الملف الشخصي
                        </a>
                        @if (auth()->user()->isAdmin())
                            <a class="flex items-center gap-3 rounded-xl px-3.5 py-3 hover:bg-violet-50"
                                href="{{ route('admin.dashboard') }}">
                                <i class="fa-solid fa-shield-halved w-5 text-center text-violet-600" aria-hidden="true"></i>
                                لوحة الإدارة
                            </a>
                        @endif
                        <form class="mt-1 border-t border-slate-100 pt-1" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                class="flex w-full items-center gap-3 rounded-xl px-3.5 py-3 text-right font-bold text-red-700 hover:bg-red-50"
                                type="submit">
                                <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center" aria-hidden="true"></i>
                                تسجيل الخروج
                            </button>
                        </form>
                    </div>
                </details>
            @else
                <a class="inline-flex min-h-11 items-center justify-center rounded-xl border border-violet-200 bg-white px-4 font-extrabold text-violet-800 transition-colors hover:bg-violet-50"
                    href="{{ route('register') }}">إنشاء حساب</a>
                <a class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-linear-to-l from-violet-700 to-indigo-700 px-5 font-extrabold text-white shadow-lg shadow-violet-900/15 transition-transform hover:-translate-y-0.5"
                    href="{{ route('login') }}">
                    <i class="fa-regular fa-user" aria-hidden="true"></i>
                    تسجيل الدخول
                </a>
            @endauth
        </div>

        <button
            class="mobile-menu grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-violet-100 bg-white text-xl text-violet-700 xl:hidden"
            type="button" id="mobileMenuBtn" aria-label="فتح القائمة" aria-controls="mobileDrawer"
            aria-expanded="false">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>
    </div>

    <div class="mobile-drawer mx-auto mb-3 hidden w-[min(calc(100%_-_2rem),1280px)] gap-1 rounded-2xl border border-violet-100 bg-white p-3 shadow-xl [&.is-open]:grid"
        id="mobileDrawer">
        <a class="rounded-xl px-4 py-3 font-extrabold text-slate-700 transition-colors hover:bg-violet-50 hover:text-violet-700 data-active:bg-violet-50 data-active:text-violet-800"
            href="{{ route('home') }}#home"
            @if ($active === 'home') data-scrollspy-target="home" @endif
            @if ($active === 'home') data-active aria-current="location" @endif>الرئيسية</a>
        <a class="rounded-xl px-4 py-3 font-extrabold text-slate-700 transition-colors hover:bg-violet-50 hover:text-violet-700 data-active:bg-violet-50 data-active:text-violet-800"
            href="{{ route('home') }}#tools"
            @if ($active === 'home') data-scrollspy-target="tools" @endif>كل الأدوات</a>
        <a class="rounded-xl px-4 py-3 font-extrabold text-slate-700 transition-colors hover:bg-violet-50 hover:text-violet-700 data-active:bg-violet-50 data-active:text-violet-800"
            href="{{ route('home') }}#features"
            @if ($active === 'home') data-scrollspy-target="features" @endif>مميزاتنا</a>
        <a class="rounded-xl px-4 py-3 font-extrabold text-slate-700 transition-colors hover:bg-violet-50 hover:text-violet-700 data-active:bg-violet-50 data-active:text-violet-800"
            href="{{ route('home') }}#activity"
            @if ($active === 'home') data-scrollspy-target="activity" @endif>نشاط المنصة</a>
        <a class="rounded-xl px-4 py-3 font-extrabold text-slate-700 transition-colors hover:bg-violet-50 hover:text-violet-700 data-active:bg-violet-50 data-active:text-violet-800"
            href="{{ route('home') }}#how"
            @if ($active === 'home') data-scrollspy-target="how" @endif>كيف تعمل المنصة؟</a>
        <a class="rounded-xl px-4 py-3 font-extrabold text-slate-700 transition-colors hover:bg-violet-50 hover:text-violet-700 data-active:bg-violet-50 data-active:text-violet-800"
            href="{{ route('home') }}#faq"
            @if ($active === 'home') data-scrollspy-target="faq" @endif>الأسئلة الشائعة</a>
        <div class="mt-2 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3">
            @auth
                <a class="rounded-xl bg-violet-700 px-4 py-3 text-center font-extrabold text-white"
                    href="{{ route('dashboard') }}">لوحة التحكم</a>
                <a class="rounded-xl border border-violet-200 px-4 py-3 text-center font-extrabold text-violet-800"
                    href="{{ route('profile.edit') }}">حسابي</a>
            @else
                <a class="rounded-xl bg-violet-700 px-4 py-3 text-center font-extrabold text-white"
                    href="{{ route('login') }}">تسجيل الدخول</a>
                <a class="rounded-xl border border-violet-200 px-4 py-3 text-center font-extrabold text-violet-800"
                    href="{{ route('register') }}">إنشاء حساب</a>
            @endauth
        </div>
    </div>
</header>
