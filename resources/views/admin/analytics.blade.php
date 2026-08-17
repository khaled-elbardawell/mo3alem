@extends('layouts.admin')

@use('Illuminate\Support\Number')

@section('title', 'الإحصائيات')

@section('content')
    @php
        $metricDefinitions = [
            'site_visits' => ['label' => 'زوار الموقع', 'short' => 'زوار', 'icon' => 'fa-chart-line'],
            'registrations' => ['label' => 'التسجيلات', 'short' => 'تسجيل', 'icon' => 'fa-user-plus'],
            'active_users' => ['label' => 'المستخدمون النشطون', 'short' => 'نشط', 'icon' => 'fa-user-check'],
            'competitions' => ['label' => 'المسابقات الجديدة', 'short' => 'مسابقات', 'icon' => 'fa-trophy'],
            'saved_wheels' => ['label' => 'القوائم الجديدة', 'short' => 'قوائم', 'icon' => 'fa-list-check'],
            'names_saved' => ['label' => 'الأسماء المضافة', 'short' => 'أسماء', 'icon' => 'fa-signature'],
            'spins' => ['label' => 'مرات تحريك العجلة', 'short' => 'تحريك', 'icon' => 'fa-arrows-rotate'],
            'imports' => ['label' => 'عمليات الاستيراد', 'short' => 'استيراد', 'icon' => 'fa-file-import'],
            'qr_generated' => ['label' => 'رموز QR المُنشأة', 'short' => 'QR مُنشأ', 'icon' => 'fa-qrcode'],
            'qr_saved' => ['label' => 'رموز QR المحفوظة', 'short' => 'QR محفوظ', 'icon' => 'fa-floppy-disk'],
            'certificate_generated' => ['label' => 'الشهادات المُنشأة', 'short' => 'شهادات مُنشأة', 'icon' => 'fa-wand-magic-sparkles'],
            'certificate_saved' => ['label' => 'الشهادات المحفوظة', 'short' => 'شهادات محفوظة', 'icon' => 'fa-certificate'],
            'ad_impressions' => ['label' => 'ظهور الإعلانات', 'short' => 'ظهور', 'icon' => 'fa-eye'],
            'ad_clicks' => ['label' => 'نقرات الإعلانات', 'short' => 'نقر', 'icon' => 'fa-arrow-pointer'],
        ];
    @endphp

    <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
        <div>
            <p class="text-sm font-black text-violet-600">الأداء</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">الإحصائيات</h1>
            <p class="mt-2 max-w-3xl leading-7 text-slate-500">بيانات يومية مباشرة تشمل استخدام الأدوات والحسابات والإعلانات.</p>
        </div>
        <div class="rounded-2xl border border-violet-100 bg-violet-50 px-4 py-3 text-sm font-black text-violet-800">
            <i class="fa-regular fa-calendar-days ms-2" aria-hidden="true"></i>
            <time datetime="{{ $from->toDateString() }}">{{ $from->locale('ar')->translatedFormat('j F Y') }}</time>
            <span class="mx-1 text-violet-400">—</span>
            <time datetime="{{ $to->toDateString() }}">{{ $to->locale('ar')->translatedFormat('j F Y') }}</time>
        </div>
    </div>

    <form class="mt-7 grid gap-4 rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm sm:grid-cols-2 xl:grid-cols-[1fr_1fr_1fr_auto] xl:items-end" method="GET">
        <label class="grid gap-2 text-sm font-bold text-slate-700">
            الفترة
            <select class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="range">
                <option value="7" @selected($range === '7')>آخر 7 أيام</option>
                <option value="30" @selected($range === '30')>آخر 30 يومًا</option>
                <option value="year" @selected($range === 'year')>هذه السنة</option>
                <option value="custom" @selected($range === 'custom')>نطاق مخصص</option>
            </select>
        </label>
        <label class="grid gap-2 text-sm font-bold text-slate-700">
            من تاريخ
            <input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" type="date" name="from" value="{{ request('from', $from->toDateString()) }}">
        </label>
        <label class="grid gap-2 text-sm font-bold text-slate-700">
            إلى تاريخ
            <input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" type="date" name="to" value="{{ request('to', $to->toDateString()) }}">
        </label>
        <button class="min-h-12 rounded-xl bg-violet-700 px-6 font-black text-white shadow-[0_10px_24px_rgba(109,40,217,0.2)] transition hover:bg-violet-800">تطبيق</button>
    </form>

    <section class="mt-7" aria-labelledby="analytics-period-totals">
        <div>
            <h2 class="text-xl font-black" id="analytics-period-totals">إجمالي الفترة</h2>
            <p class="mt-1 text-sm font-bold text-slate-500">مجموع الأحداث المسجلة بين التاريخين المحددين.</p>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-3 2xl:grid-cols-4">
            @foreach ($metricDefinitions as $key => $metric)
                <article class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-bold leading-6 text-slate-500">{{ $metric['label'] }}</p>
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-violet-50 text-sm text-violet-700">
                            <i class="fa-solid {{ $metric['icon'] }}" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-2xl font-black text-slate-900">{{ Number::format($totals[$key], locale: 'ar') }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mt-7 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex flex-col justify-between gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center">
            <div>
                <h2 class="font-black">التفاصيل اليومية</h2>
                <p class="mt-1 text-xs font-bold text-slate-500">تظهر الأيام التي لا تحتوي على نشاط بقيم صفرية لإكمال التسلسل الزمني.</p>
            </div>
            <span class="text-xs font-black text-violet-700">{{ Number::format($rows->count(), locale: 'ar') }} يوم</span>
        </div>

        <div class="hidden max-h-[70vh] overflow-auto md:block">
            <table class="w-full min-w-[1500px] text-right text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="sticky top-0 right-0 z-30 min-w-48 bg-slate-50 p-3">التاريخ</th>
                        @foreach ($metricDefinitions as $metric)
                            <th class="sticky top-0 z-20 whitespace-nowrap bg-slate-50 p-3">{{ $metric['short'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($rows as $row)
                        <tr class="group hover:bg-violet-50/40">
                            <td class="sticky right-0 z-10 bg-white p-3 font-bold group-hover:bg-violet-50">
                                <time datetime="{{ $row['date']->toDateString() }}">{{ $row['date']->locale('ar')->translatedFormat('l، j F Y') }}</time>
                            </td>
                            @foreach ($metricDefinitions as $key => $metric)
                                <td class="p-3">{{ Number::format($row[$key], locale: 'ar') }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid gap-3 p-3 md:hidden">
            @foreach ($rows as $row)
                <article class="rounded-2xl border border-slate-200 p-4">
                    <time class="font-black text-violet-700" datetime="{{ $row['date']->toDateString() }}">{{ $row['date']->locale('ar')->translatedFormat('l، j F Y') }}</time>
                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        @foreach ($metricDefinitions as $key => $metric)
                            <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2">
                                <dt class="text-slate-500">{{ $metric['short'] }}</dt>
                                <dd class="font-black">{{ Number::format($row[$key], locale: 'ar') }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </article>
            @endforeach
        </div>
    </section>
@endsection
