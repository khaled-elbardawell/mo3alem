@extends('layouts.admin')

@section('title', 'الإحصائيات')

@section('content')
    <div>
        <p class="text-sm font-black text-violet-600">الأداء</p>
        <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">الإحصائيات</h1>
        <p class="mt-2 leading-7 text-slate-500">تابع نمو الحسابات واستخدام العجلة وأداء الإعلانات ضمن الفترة التي تختارها.</p>
    </div>

    <form class="mt-7 grid gap-4 rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm sm:grid-cols-2 xl:grid-cols-[1fr_1fr_1fr_auto] xl:items-end" method="GET">
        <label class="grid gap-2 text-sm font-bold text-slate-700">
            الفترة
            <select class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="range">
                <option value="7" @selected($range === '7')>7 أيام</option>
                <option value="30" @selected($range === '30')>30 يومًا</option>
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
        <button class="min-h-12 rounded-xl bg-violet-700 px-6 font-black text-white shadow-[0_10px_24px_rgba(109,40,217,0.2)] hover:bg-violet-800">تطبيق</button>
    </form>

    <div class="mt-6 grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach([
            'زوار الموقع' => 'site_visits',
            'التسجيلات' => 'registrations',
            'المستخدمون النشطون' => 'active_users',
            'القوائم الجديدة' => 'saved_wheels',
            'الأسماء المحفوظة' => 'names_saved',
            'مرات الدوران' => 'spins',
            'الاستيراد' => 'imports',
            'ظهور الإعلانات' => 'ad_impressions',
            'نقرات الإعلانات' => 'ad_clicks',
        ] as $label => $key)
            <article class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                <p class="text-sm font-bold text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($totals[$key]) }}</p>
            </article>
        @endforeach
    </div>

    <section class="mt-6 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-black">التفاصيل اليومية</h2>
        </div>
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full min-w-4xl text-right text-sm">
                <thead class="bg-slate-50 text-slate-500"><tr><th class="p-3">التاريخ</th><th class="p-3">زوار</th><th class="p-3">تسجيل</th><th class="p-3">نشط</th><th class="p-3">قوائم</th><th class="p-3">أسماء</th><th class="p-3">دوران</th><th class="p-3">استيراد</th><th class="p-3">ظهور</th><th class="p-3">نقر</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $row)
                        <tr class="hover:bg-violet-50/40"><td class="p-3 font-bold">{{ $row['date'] }}</td><td class="p-3">{{ $row['site_visits'] }}</td><td class="p-3">{{ $row['registrations'] }}</td><td class="p-3">{{ $row['active_users'] }}</td><td class="p-3">{{ $row['saved_wheels'] }}</td><td class="p-3">{{ $row['names_saved'] }}</td><td class="p-3">{{ $row['spins'] }}</td><td class="p-3">{{ $row['imports'] }}</td><td class="p-3">{{ $row['ad_impressions'] }}</td><td class="p-3">{{ $row['ad_clicks'] }}</td></tr>
                    @empty
                        <tr><td class="p-8 text-center text-slate-500" colspan="10">لا توجد بيانات في هذا النطاق.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="grid gap-3 p-3 md:hidden">
            @forelse($rows as $row)
                <article class="rounded-2xl border border-slate-200 p-4">
                    <p class="font-black text-violet-700">{{ $row['date'] }}</p>
                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        @foreach(['زوار' => 'site_visits', 'تسجيل' => 'registrations', 'نشط' => 'active_users', 'قوائم' => 'saved_wheels', 'أسماء' => 'names_saved', 'دوران' => 'spins', 'استيراد' => 'imports', 'ظهور' => 'ad_impressions', 'نقر' => 'ad_clicks'] as $label => $key)
                            <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2">
                                <dt class="text-slate-500">{{ $label }}</dt>
                                <dd class="font-black">{{ $row[$key] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </article>
            @empty
                <p class="p-6 text-center text-slate-500">لا توجد بيانات في هذا النطاق.</p>
            @endforelse
        </div>
    </section>
@endsection
