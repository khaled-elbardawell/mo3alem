@extends('layouts.public')

@section('title', 'الرابط غير متاح')

@section('content')
    <section class="mx-auto grid min-h-[65vh] w-[min(calc(100%_-_2rem),760px)] place-items-center py-16 text-center">
        <div class="w-full rounded-3xl border border-violet-100 bg-white p-8 shadow-[0_24px_70px_rgba(76,29,149,0.10)] sm:p-12">
            <span class="mx-auto grid size-20 place-items-center rounded-3xl bg-violet-50 text-3xl text-violet-700">
                <i class="fa-solid {{ $isPaused ? 'fa-pause' : 'fa-link-slash' }}" aria-hidden="true"></i>
            </span>
            <h1 class="mt-6 text-3xl font-black text-[#111a35]">{{ $isPaused ? 'الرابط متوقف مؤقتًا' : 'انتهت صلاحية هذا الرابط' }}</h1>
            <p class="mx-auto mt-3 max-w-xl font-medium leading-8 text-slate-500">
                {{ $isPaused ? 'أوقف صاحب الرابط الوصول إليه مؤقتًا. يمكنك المحاولة لاحقًا.' : 'الرابط محذوف أو تجاوز تاريخ الانتهاء الذي حدده صاحبه.' }}
            </p>
            <a class="mt-7 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-violet-700 px-6 font-black text-white hover:bg-violet-800" href="{{ route('home') }}">
                العودة إلى معلم
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            </a>
        </div>
    </section>
@endsection
