<!doctype html>
<html class="w-screen max-w-full scroll-smooth overflow-x-hidden motion-reduce:scroll-auto @yield('htmlClass')"
    lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('assets/icon.png') }}" sizes="32x32">
    <title>
        @isset($seo)
            @yield('title', $seo->title)
        @else
            @yield('title', 'لوحة التحكم') | معلم
        @endisset
    </title>
    @isset($seo)
        @if ($seo->description)
            <meta name="description" content="{{ $seo->description }}">
        @endif
        @if ($seo->keywords)
            <meta name="keywords" content="{{ $seo->keywords }}">
        @endif
        <meta name="robots" content="{{ $seo->allow_indexing ? 'index,follow' : 'noindex,nofollow' }}">
        <link rel="canonical" href="@yield('canonical', $seo->canonical_url ?: route('home'))">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $seo->site_name }}">
        <meta property="og:title" content="@yield('title', $seo->title)">
        @if ($seo->description)
            <meta property="og:description" content="{{ $seo->description }}">
        @endif
        <meta property="og:url" content="@yield('canonical', $seo->canonical_url ?: route('home'))">
        @if ($seo->og_image_path)
            <meta property="og:image" content="{{ Storage::disk('public')->url($seo->og_image_path) }}">
        @endif
        <meta name="twitter:card" content="{{ $seo->twitter_card }}">
    @endisset


    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-NNBQPQ9Q');
    </script>
    <!-- End Google Tag Manager -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body
    class="m-0 min-h-screen w-screen max-w-full overflow-x-hidden bg-[#fbfbff] pt-20 font-['Tajawal',system-ui,sans-serif] text-slate-900 antialiased">
    <x-public.site-header :active="$activeNavigation ?? ''" />

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NNBQPQ9Q" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    @if ($fullWidth ?? false)
        <main>
            @yield('content')
        </main>
    @else
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
    @endif

    <x-public.site-footer />

    <button
        class="back-to-top pointer-events-none fixed bottom-[18px] left-[18px] z-80 grid h-[42px] w-[42px] translate-y-2.5 cursor-pointer place-items-center rounded-xl border border-slate-300/70 bg-white text-violet-700 opacity-0 shadow-[0_14px_34px_rgba(30,41,59,0.16)] transition-[opacity,transform,background,color] duration-200 hover:bg-violet-700 hover:text-white [&.is-visible]:pointer-events-auto [&.is-visible]:translate-y-0 [&.is-visible]:opacity-100"
        type="button" id="backToTopBtn" aria-label="الرجوع للأعلى" title="الرجوع للأعلى">
        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
    </button>

    @stack('scripts')
</body>

</html>
