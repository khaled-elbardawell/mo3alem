@extends('layouts.admin')

@section('title', 'نظرة عامة')

@section('content')
    <div>
        <p class="text-sm font-black text-violet-600">لوحة الإدارة</p>
        <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">نظرة عامة</h1>
        <p class="mt-2 max-w-2xl leading-7 text-slate-500">ملخص سريع لحالة المستخدمين والقوائم والحملات ونشاط الموقع اليوم.</p>
    </div>

    <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach([
            ['label' => 'كل المستخدمين', 'value' => $metrics['users'], 'icon' => 'fa-users', 'color' => 'bg-violet-100 text-violet-700'],
            ['label' => 'المستخدمون النشطون', 'value' => $metrics['active_users'], 'icon' => 'fa-user-check', 'color' => 'bg-emerald-100 text-emerald-700'],
            ['label' => 'القوائم المحفوظة', 'value' => $metrics['saved_wheels'], 'icon' => 'fa-list-check', 'color' => 'bg-blue-100 text-blue-700'],
            ['label' => 'الحملات النشطة', 'value' => $metrics['active_campaigns'], 'icon' => 'fa-rectangle-ad', 'color' => 'bg-amber-100 text-amber-700'],
            ['label' => 'زوار الموقع اليوم', 'value' => $metrics['site_visits_today'], 'icon' => 'fa-chart-line', 'color' => 'bg-indigo-100 text-indigo-700'],
            ['label' => 'دورات اليوم', 'value' => $metrics['spins_today'], 'icon' => 'fa-arrows-rotate', 'color' => 'bg-fuchsia-100 text-fuchsia-700'],
            ['label' => 'تسجيلات اليوم', 'value' => $metrics['registrations_today'], 'icon' => 'fa-user-plus', 'color' => 'bg-cyan-100 text-cyan-700'],
            ['label' => 'مشاهدو الإعلانات اليوم', 'value' => $metrics['ad_impressions_today'], 'icon' => 'fa-eye', 'color' => 'bg-rose-100 text-rose-700'],
            ['label' => 'نقرات الإعلانات اليوم', 'value' => $metrics['ad_clicks_today'], 'icon' => 'fa-arrow-pointer', 'color' => 'bg-teal-100 text-teal-700'],
        ] as $metric)
            <article class="group rounded-3xl border border-slate-200/80 bg-white p-5 shadow-[0_10px_35px_rgba(15,23,42,0.05)] transition-[transform,box-shadow] hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(76,29,149,0.1)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-slate-500">{{ $metric['label'] }}</p>
                        <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($metric['value']) }}</p>
                    </div>
                    <span class="{{ $metric['color'] }} grid h-12 w-12 shrink-0 place-items-center rounded-2xl text-lg">
                        <i class="fa-solid {{ $metric['icon'] }}"></i>
                    </span>
                </div>
            </article>
        @endforeach
    </div>
@endsection
