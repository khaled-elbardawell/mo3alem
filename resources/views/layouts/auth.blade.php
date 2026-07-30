<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'الحساب') | نرد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center bg-[radial-gradient(circle_at_top,#ede9fe,transparent_45%),#fafafa] p-4 font-['Tajawal',sans-serif] text-slate-900">
    <main class="w-full max-w-md rounded-3xl border border-violet-100 bg-white p-6 shadow-[0_24px_70px_rgba(76,29,149,0.12)] sm:p-8">
        <a class="mb-6 inline-flex w-full items-center justify-center gap-2.5 text-[30px] font-black" href="{{ route('home') }}" aria-label="نرد">
            <span>نرد</span>
            <span
                class="relative inline-block h-[42px] w-[42px] overflow-hidden rounded-full border-[3px] border-white bg-[conic-gradient(#fb923c_0_60deg,#22c55e_60deg_120deg,#3b82f6_120deg_180deg,#8b5cf6_180deg_240deg,#ef4444_240deg_300deg,#eab308_300deg_360deg)] shadow-[0_0_0_1px_rgba(109,40,217,0.16),0_8px_20px_rgba(109,40,217,0.2)] after:absolute after:inset-3.5 after:rounded-full after:bg-white after:content-['']"
                aria-hidden="true"></span>
        </a>
        @if(session('status'))
            <div class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                <ul class="list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
