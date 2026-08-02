<footer class="relative overflow-hidden bg-[#111a35] text-white">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_12%_15%,rgba(124,58,237,0.2),transparent_28%),radial-gradient(circle_at_88%_82%,rgba(37,99,235,0.14),transparent_25%)]"
        aria-hidden="true"></div>
    <div class="relative mx-auto grid w-[min(calc(100%_-_2rem),1280px)] gap-10 py-14 lg:grid-cols-[1.15fr_2fr]">
        <div class="max-w-md">
            <img class="h-14 w-auto brightness-0 invert" src="{{ asset('assets/logo.png') }}" alt="معلم">
            <p class="mt-4 text-sm font-medium leading-7 text-slate-300">منصة عربية تمنح المعلم أدوات عملية وسهلة لإنجاز
                مهامه التعليمية في مكان واحد.</p>
            <div class="mt-5 flex items-center gap-2" aria-label="روابط معلم">
                <a class="grid h-10 w-10 place-items-center rounded-xl border border-white/10 bg-white/5 text-slate-300 transition-colors hover:bg-violet-600 hover:text-white"
                    href="https://cmp-tch.com" target="_blank" rel="noopener noreferrer"
                    aria-label="موقع معلم الحاسب"><i class="fa-solid fa-globe" aria-hidden="true"></i></a>
                <a class="grid h-10 w-10 place-items-center rounded-xl border border-white/10 bg-white/5 text-slate-300 transition-colors hover:bg-violet-600 hover:text-white"
                    href="{{ route('home') }}#tools" aria-label="أدوات معلم"><i class="fa-solid fa-toolbox"
                        aria-hidden="true"></i></a>
                <a class="grid h-10 w-10 place-items-center rounded-xl border border-white/10 bg-white/5 text-slate-300 transition-colors hover:bg-violet-600 hover:text-white"
                    href="{{ route('home') }}#faq" aria-label="مركز المساعدة"><i class="fa-regular fa-circle-question"
                        aria-hidden="true"></i></a>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-8 sm:grid-cols-3">
            <div>
                <h2 class="font-black">الأدوات</h2>
                <div class="mt-4 grid gap-3 text-sm font-medium text-slate-300">
                    <a class="hover:text-white" href="{{ route('tools.wheel') }}">عجلة الأسماء</a>
                    <a class="hover:text-white" href="{{ route('home') }}#tools">إنشاء QR</a>
                    <a class="hover:text-white" href="{{ route('home') }}#tools">إنشاء الشهادات</a>
                </div>
            </div>
            <div>
                <h2 class="font-black">المنصة</h2>
                <div class="mt-4 grid gap-3 text-sm font-medium text-slate-300">
                    <a class="hover:text-white" href="{{ route('home') }}#features">مميزاتنا</a>
                    <a class="hover:text-white" href="{{ route('home') }}#how">كيف تعمل المنصة؟</a>
                    <a class="hover:text-white" href="{{ route('home') }}#faq">الأسئلة الشائعة</a>
                    <a class="hover:text-white" href="https://cmp-tch.com" target="_blank"
                        rel="noopener noreferrer">معلم الحاسب</a>
                </div>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <h2 class="font-black">حسابك</h2>
                <div class="mt-4 grid gap-3 text-sm font-medium text-slate-300">
                    @auth
                        <a class="hover:text-white" href="{{ route('dashboard') }}">لوحة التحكم</a>
                        <a class="hover:text-white" href="{{ route('profile.edit') }}">الملف الشخصي</a>
                    @else
                        <a class="hover:text-white" href="{{ route('login') }}">تسجيل الدخول</a>
                        <a class="hover:text-white" href="{{ route('register') }}">إنشاء حساب</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
    <div class="relative border-t border-white/10">
        <div
            class="mx-auto grid w-[min(calc(100%_-_2rem),1280px)] gap-1 py-5 text-center text-xs font-medium text-slate-400">
            <p>© {{ now()->year }} معلم. جميع الحقوق محفوظة.</p>
            <p>صُممت لتجعل يوم المعلم أسهل.</p>
        </div>
    </div>
</footer>
