<!doctype html>
<html class="w-screen max-w-full scroll-smooth overflow-x-hidden motion-reduce:scroll-auto" lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('assets/icon.png') }}" sizes="32x32">
    <title>@yield('title', $seo->title)</title>
    @if($seo->description)<meta name="description" content="{{ $seo->description }}">@endif
    @if($seo->keywords)<meta name="keywords" content="{{ $seo->keywords }}">@endif
    <meta name="robots" content="{{ $seo->allow_indexing ? 'index,follow' : 'noindex,nofollow' }}">
    <link rel="canonical" href="@yield('canonical', $seo->canonical_url ?: route('home'))">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $seo->site_name }}">
    <meta property="og:title" content="@yield('title', $seo->title)">
    @if($seo->description)<meta property="og:description" content="{{ $seo->description }}">@endif
    <meta property="og:url" content="@yield('canonical', $seo->canonical_url ?: route('home'))">
    @if($seo->og_image_path)<meta property="og:image" content="{{ Storage::disk('public')->url($seo->og_image_path) }}">@endif
    <meta name="twitter:card" content="{{ $seo->twitter_card }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="m-0 min-h-screen w-screen max-w-full overflow-x-hidden bg-[#fbfbff] pt-20 font-['Tajawal',system-ui,sans-serif] text-slate-900 antialiased">
    <x-public.site-header :active="$activeNavigation ?? ''" />
    <main>
        @yield('content')
    </main>
    <x-public.site-footer />
</body>
</html>
