@extends('layouts.admin')

@use('Illuminate\Support\Number')

@section('title', 'نظرة عامة')

@section('content')
    @php
        $currentMetrics = [
            ['label' => 'المستخدمون الحاليون', 'value' => $metrics['users'], 'icon' => 'fa-users', 'color' => 'bg-violet-100 text-violet-700'],
            ['label' => 'المسابقات المحفوظة', 'value' => $metrics['competitions'], 'icon' => 'fa-trophy', 'color' => 'bg-amber-100 text-amber-700'],
            ['label' => 'قوائم الأسماء', 'value' => $metrics['saved_wheels'], 'icon' => 'fa-list-check', 'color' => 'bg-blue-100 text-blue-700'],
            ['label' => 'رموز QR المحفوظة', 'value' => $metrics['qr_codes'], 'icon' => 'fa-qrcode', 'color' => 'bg-cyan-100 text-cyan-700'],
            ['label' => 'الشهادات المحفوظة', 'value' => $metrics['certificates'], 'icon' => 'fa-certificate', 'color' => 'bg-rose-100 text-rose-700'],
            ['label' => 'الحملات النشطة الآن', 'value' => $metrics['active_campaigns'], 'icon' => 'fa-rectangle-ad', 'color' => 'bg-emerald-100 text-emerald-700'],
        ];

        $todayMetrics = [
            ['label' => 'زوار الموقع', 'value' => $metrics['site_visits_today'], 'icon' => 'fa-chart-line'],
            ['label' => 'التسجيلات الجديدة', 'value' => $metrics['registrations_today'], 'icon' => 'fa-user-plus'],
            ['label' => 'المستخدمون النشطون', 'value' => $metrics['active_users_today'], 'icon' => 'fa-user-check'],
            ['label' => 'المسابقات الجديدة', 'value' => $metrics['competitions_today'], 'icon' => 'fa-trophy'],
            ['label' => 'القوائم الجديدة', 'value' => $metrics['saved_wheels_today'], 'icon' => 'fa-list'],
            ['label' => 'الأسماء المضافة', 'value' => $metrics['names_saved_today'], 'icon' => 'fa-signature'],
            ['label' => 'مرات تحريك العجلة', 'value' => $metrics['spins_today'], 'icon' => 'fa-arrows-rotate'],
            ['label' => 'عمليات الاستيراد', 'value' => $metrics['imports_today'], 'icon' => 'fa-file-import'],
            ['label' => 'رموز QR المُنشأة', 'value' => $metrics['qr_generated_today'], 'icon' => 'fa-qrcode'],
            ['label' => 'رموز QR المحفوظة', 'value' => $metrics['qr_saved_today'], 'icon' => 'fa-floppy-disk'],
            ['label' => 'الشهادات المُنشأة', 'value' => $metrics['certificate_generated_today'], 'icon' => 'fa-wand-magic-sparkles'],
            ['label' => 'الشهادات المحفوظة', 'value' => $metrics['certificate_saved_today'], 'icon' => 'fa-certificate'],
            ['label' => 'ظهور الإعلانات', 'value' => $metrics['ad_impressions_today'], 'icon' => 'fa-eye'],
            ['label' => 'نقرات الإعلانات', 'value' => $metrics['ad_clicks_today'], 'icon' => 'fa-arrow-pointer'],
        ];
    @endphp

    <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
        <div>
            <p class="text-sm font-black text-violet-600">لوحة الإدارة</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">نظرة عامة دقيقة</h1>
            <p class="mt-2 max-w-2xl leading-7 text-slate-500">الحالة الحالية للمنصة، يليها النشاط المسجل خلال اليوم.</p>
        </div>
        <div class="inline-flex w-fit items-center gap-2 rounded-2xl border border-violet-100 bg-white px-4 py-3 text-sm font-black text-violet-700 shadow-sm">
            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
            <time datetime="{{ $today->toDateString() }}">{{ $today->locale('ar')->translatedFormat('l، j F Y') }}</time>
        </div>
    </div>

    <section class="mt-8" aria-labelledby="current-platform-state">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black" id="current-platform-state">الحالة الحالية</h2>
                <p class="mt-1 text-sm font-bold text-slate-500">العناصر الموجودة حاليًا دون احتساب العناصر المحذوفة.</p>
            </div>
            <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">مباشر</span>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($currentMetrics as $metric)
                <article class="group rounded-3xl border border-slate-200/80 bg-white p-5 shadow-[0_10px_35px_rgba(15,23,42,0.05)] transition-[transform,box-shadow] hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(76,29,149,0.1)]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-slate-500">{{ $metric['label'] }}</p>
                            <p class="mt-3 text-3xl font-black text-slate-900">{{ Number::format($metric['value'], locale: 'ar') }}</p>
                        </div>
                        <span class="{{ $metric['color'] }} grid h-12 w-12 shrink-0 place-items-center rounded-2xl text-lg">
                            <i class="fa-solid {{ $metric['icon'] }}" aria-hidden="true"></i>
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mt-9" aria-labelledby="today-platform-activity">
        <div>
            <h2 class="text-xl font-black" id="today-platform-activity">نشاط اليوم</h2>
            <p class="mt-1 text-sm font-bold text-slate-500">أحداث اليوم فقط، مع منع تكرار الزائر والمستخدم النشط.</p>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-3 2xl:grid-cols-4">
            @foreach ($todayMetrics as $metric)
                <article class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-bold leading-6 text-slate-500">{{ $metric['label'] }}</p>
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-violet-50 text-sm text-violet-700">
                            <i class="fa-solid {{ $metric['icon'] }}" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-2xl font-black text-slate-900">{{ Number::format($metric['value'], locale: 'ar') }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endsection
