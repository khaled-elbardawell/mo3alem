@extends('layouts.app')

@section('title', $shortLink->title)

@section('content')
    @php
        $shortUrl = route('short-links.redirect', $shortLink->slug);
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a class="inline-flex items-center gap-2 text-sm font-bold text-violet-700 hover:underline" href="{{ route('dashboard', ['section' => 'links']) }}">
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                العودة إلى روابطي
            </a>
            <h1 class="mt-3 text-3xl font-black text-[#111a35]">{{ $shortLink->title }}</h1>
            <p class="mt-2 max-w-3xl break-all text-left text-sm font-medium text-slate-500" dir="ltr">{{ $shortLink->destination_url }}</p>
        </div>
        <span @class([
            'inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm font-black',
            'bg-emerald-50 text-emerald-700' => $shortLink->isAvailableForRedirect(),
            'bg-amber-50 text-amber-700' => ! $shortLink->isAvailableForRedirect(),
        ])>
            <i class="fa-solid {{ $shortLink->isAvailableForRedirect() ? 'fa-circle-check' : 'fa-circle-pause' }}" aria-hidden="true"></i>
            {{ $shortLink->isAvailableForRedirect() ? 'نشط' : ($shortLink->isExpired() ? 'منتهي' : 'متوقف') }}
        </span>
    </div>

    <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="ملخص الإحصائيات">
        @foreach([
            ['label' => 'إجمالي الزيارات', 'value' => $shortLink->visit_count, 'icon' => 'fa-arrow-pointer', 'tone' => 'bg-violet-100 text-violet-700'],
            ['label' => 'زيارات فريدة يوميًا', 'value' => $shortLink->unique_visit_count, 'icon' => 'fa-users', 'tone' => 'bg-emerald-100 text-emerald-700'],
            ['label' => 'زيارات الفترة', 'value' => $periodVisits, 'icon' => 'fa-chart-line', 'tone' => 'bg-blue-100 text-blue-700'],
            ['label' => 'فريدة في الفترة', 'value' => $periodUniqueVisits, 'icon' => 'fa-user-check', 'tone' => 'bg-amber-100 text-amber-700'],
        ] as $metric)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="grid size-11 place-items-center rounded-xl {{ $metric['tone'] }}"><i class="fa-solid {{ $metric['icon'] }}" aria-hidden="true"></i></span>
                <strong class="mt-4 block text-3xl font-black text-[#111a35]">{{ number_format($metric['value']) }}</strong>
                <span class="mt-1 block text-sm font-bold text-slate-500">{{ $metric['label'] }}</span>
            </article>
        @endforeach
    </section>

    <div class="mt-7 grid items-start gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(340px,.75fr)]">
        <div class="grid gap-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black text-[#111a35]">الزيارات عبر الوقت</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">الزائر الفريد يُحتسب مرة واحدة في كل يوم.</p>
                    </div>
                    <nav class="flex flex-wrap gap-2" aria-label="الفترة الإحصائية">
                        @foreach(['7' => '7 أيام', '30' => '30 يومًا', '90' => '90 يومًا', 'all' => 'كل المدة'] as $periodValue => $periodLabel)
                            <a @class([
                                'rounded-xl px-3 py-2 text-sm font-black',
                                'bg-violet-700 text-white' => $period === $periodValue,
                                'border border-slate-200 text-slate-600 hover:bg-slate-50' => $period !== $periodValue,
                            ]) href="{{ route('user.short-links.show', ['shortLink' => $shortLink, 'period' => $periodValue]) }}">{{ $periodLabel }}</a>
                        @endforeach
                    </nav>
                </div>

                @if($chartData->isEmpty() || $chartData->sum('visits') === 0)
                    <div class="mt-6 rounded-2xl border border-dashed border-violet-200 bg-violet-50/50 p-10 text-center text-slate-500">
                        <i class="fa-solid fa-chart-column text-3xl text-violet-300" aria-hidden="true"></i>
                        <p class="mt-3 font-bold">لا توجد زيارات في هذه الفترة بعد.</p>
                    </div>
                @else
                    @php
                        $chartWidth = 800;
                        $chartHeight = 320;
                        $chartLeft = 54;
                        $chartRight = 18;
                        $chartTop = 20;
                        $chartBottom = 52;
                        $plotWidth = $chartWidth - $chartLeft - $chartRight;
                        $plotHeight = $chartHeight - $chartTop - $chartBottom;
                        $chartBottomY = $chartTop + $plotHeight;
                        $chartPoints = $chartData->values();
                        $pointCount = $chartPoints->count();
                        $axisMaximum = max(4, (int) ceil($maximumDailyVisits / 4) * 4);
                        $pointX = fn (int $index): float => $chartLeft + ($pointCount === 1 ? $plotWidth / 2 : ($index / ($pointCount - 1)) * $plotWidth);
                        $pointY = fn (int $value): float => $chartTop + $plotHeight - (($value / $axisMaximum) * $plotHeight);
                        $formatPoint = fn (float $x, float $y): string => number_format($x, 2, '.', '').','.number_format($y, 2, '.', '');
                        $visitsPoints = $chartPoints->map(fn (array $day, int $index): string => $formatPoint($pointX($index), $pointY($day['visits'])))->implode(' ');
                        $uniqueVisitsPoints = $chartPoints->map(fn (array $day, int $index): string => $formatPoint($pointX($index), $pointY($day['unique_visits'])))->implode(' ');
                        $visitsAreaPoints = $formatPoint($pointX(0), $chartBottomY).' '.$visitsPoints.' '.$formatPoint($pointX($pointCount - 1), $chartBottomY);
                        $xLabelStep = max(1, (int) ceil(($pointCount - 1) / 5));
                        $xLabelIndexes = collect(range(0, $pointCount - 1, $xLabelStep))->push($pointCount - 1)->unique()->values();
                    @endphp

                    <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-bold text-slate-600" aria-hidden="true">
                        <span class="inline-flex items-center gap-2"><span class="size-3 rounded-full bg-violet-600"></span> إجمالي الزيارات</span>
                        <span class="inline-flex items-center gap-2"><span class="size-3 rounded-full bg-emerald-500"></span> الزيارات الفريدة</span>
                    </div>

                    <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-100 bg-slate-50/60 p-2 sm:p-4">
                        <svg class="h-auto min-w-[640px]" viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" role="img" aria-labelledby="visits-chart-title visits-chart-description" data-visit-chart>
                            <title id="visits-chart-title">الزيارات عبر الوقت</title>
                            <desc id="visits-chart-description">رسم بياني يقارن إجمالي الزيارات بالزيارات الفريدة يوميًا خلال الفترة المحددة.</desc>

                            @foreach(range(0, 4) as $tick)
                                @php
                                    $tickY = $chartTop + (($plotHeight / 4) * $tick);
                                    $tickValue = $axisMaximum - (($axisMaximum / 4) * $tick);
                                @endphp
                                <line x1="{{ $chartLeft }}" y1="{{ $tickY }}" x2="{{ $chartWidth - $chartRight }}" y2="{{ $tickY }}" stroke="#e2e8f0" stroke-width="1" @if($tick < 4) stroke-dasharray="4 5" @endif />
                                <text x="{{ $chartLeft - 12 }}" y="{{ $tickY + 4 }}" fill="#64748b" font-size="12" font-weight="700" text-anchor="end">{{ number_format($tickValue) }}</text>
                            @endforeach

                            <polygon points="{{ $visitsAreaPoints }}" fill="#7c3aed" fill-opacity="0.1" data-chart-area="visits" />
                            <polyline points="{{ $visitsPoints }}" fill="none" stroke="#7c3aed" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" data-chart-series="visits" />
                            <polyline points="{{ $uniqueVisitsPoints }}" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" data-chart-series="unique-visits" />

                            @foreach($chartPoints as $index => $day)
                                <circle cx="{{ $pointX($index) }}" cy="{{ $pointY($day['visits']) }}" r="4" fill="#7c3aed" stroke="white" stroke-width="2">
                                    <title>{{ $day['label'] }}: {{ number_format($day['visits']) }} زيارة</title>
                                </circle>
                                <circle cx="{{ $pointX($index) }}" cy="{{ $pointY($day['unique_visits']) }}" r="3.5" fill="#10b981" stroke="white" stroke-width="2">
                                    <title>{{ $day['label'] }}: {{ number_format($day['unique_visits']) }} زيارة فريدة</title>
                                </circle>
                            @endforeach

                            @foreach($xLabelIndexes as $index)
                                <text x="{{ $pointX($index) }}" y="{{ $chartHeight - 20 }}" fill="#64748b" font-size="12" font-weight="700" text-anchor="middle">{{ $chartPoints[$index]['label'] }}</text>
                            @endforeach
                        </svg>
                    </div>

                    <details class="mt-4 rounded-2xl border border-slate-200 bg-slate-50/50">
                        <summary class="cursor-pointer px-4 py-3 text-sm font-black text-violet-700">عرض جدول البيانات</summary>
                        <div class="overflow-x-auto border-t border-slate-200">
                            <table class="w-full min-w-[480px] text-sm">
                                <caption class="sr-only">بيانات الزيارات اليومية للرابط المختصر</caption>
                                <thead class="bg-slate-100 text-slate-600">
                                    <tr>
                                        <th class="px-4 py-3 text-right font-black" scope="col">التاريخ</th>
                                        <th class="px-4 py-3 text-right font-black" scope="col">إجمالي الزيارات</th>
                                        <th class="px-4 py-3 text-right font-black" scope="col">الزيارات الفريدة</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach($chartPoints as $day)
                                        <tr>
                                            <th class="px-4 py-3 text-right font-bold text-slate-700" scope="row"><time datetime="{{ $day['date'] }}">{{ $day['label'] }}</time></th>
                                            <td class="px-4 py-3 font-bold text-slate-700">{{ number_format($day['visits']) }}</td>
                                            <td class="px-4 py-3 font-bold text-slate-700">{{ number_format($day['unique_visits']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @endif
            </section>

            @if(auth()->user()->hasVerifiedEmail())
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <h2 class="text-xl font-black text-[#111a35]">تعديل الرابط</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">يمكن تغيير الوجهة والحالة والانتهاء، بينما يبقى المقطع المنشور ثابتًا.</p>

                <form class="mt-6 grid gap-5" method="POST" action="{{ route('short-links.update', $shortLink) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="version" value="{{ $shortLink->version }}">

                    <label class="grid gap-2 font-bold text-slate-800">
                        <span>الاسم الداخلي</span>
                        <input class="min-h-12 rounded-xl border border-slate-200 px-4 focus:border-violet-500 focus:outline-none focus:ring-3 focus:ring-violet-100" type="text" name="title" value="{{ old('title', $shortLink->title) }}" maxlength="120">
                        @error('title')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
                    </label>
                    <label class="grid gap-2 font-bold text-slate-800">
                        <span>الرابط الطويل</span>
                        <input class="min-h-12 rounded-xl border border-slate-200 px-4 text-left focus:border-violet-500 focus:outline-none focus:ring-3 focus:ring-violet-100" type="url" name="destination_url" value="{{ old('destination_url', $shortLink->destination_url) }}" maxlength="2048" dir="ltr" required>
                        @error('destination_url')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
                    </label>
                    <div class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-end">
                        <label class="grid gap-2 font-bold text-slate-800">
                            <span>تاريخ الانتهاء</span>
                            <input class="min-h-12 rounded-xl border border-slate-200 px-4 focus:border-violet-500 focus:outline-none focus:ring-3 focus:ring-violet-100" type="datetime-local" name="expires_at" value="{{ old('expires_at', $shortLink->expires_at?->format('Y-m-d\TH:i')) }}">
                            @error('expires_at')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
                        </label>
                        <label class="flex min-h-12 items-center gap-3 rounded-xl border border-slate-200 px-4 font-bold text-slate-700">
                            <input type="hidden" name="is_active" value="0">
                            <input class="size-5 accent-violet-700" type="checkbox" name="is_active" value="1" @checked(old('is_active', $shortLink->is_active))>
                            الرابط نشط
                        </label>
                    </div>
                    @error('version')<span class="text-sm text-red-700">{{ $message }}</span>@enderror

                    <button class="min-h-12 rounded-xl bg-violet-700 px-6 font-black text-white hover:bg-violet-800" type="submit">حفظ التعديلات</button>
                </form>
            </section>
            @else
                <section class="rounded-3xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
                    <h2 class="text-xl font-black">فعّل بريدك لتعديل الرابط</h2>
                    <p class="mt-2 font-medium">الإحصائيات متاحة للعرض، بينما التعديل والتصدير والحذف تتطلب تفعيل البريد.</p>
                    <a class="mt-4 inline-flex rounded-xl bg-amber-600 px-5 py-3 font-black text-white hover:bg-amber-700" href="{{ route('verification.notice') }}">تفعيل البريد</a>
                </section>
            @endif
        </div>

        <aside class="grid gap-6">
            @if(auth()->user()->hasVerifiedEmail())
            <section class="rounded-3xl border border-violet-100 bg-white p-5 shadow-sm" data-short-link-tools data-short-url="{{ $shortUrl }}" data-qr-url="{{ route('short-links.qr', $shortLink) }}" data-filename="muallem-{{ $shortLink->slug }}">
                <h2 class="text-lg font-black text-[#111a35]">الرابط المختصر</h2>
                <input class="mt-3 w-full rounded-xl border border-violet-200 bg-violet-50 px-4 py-3 text-left font-bold text-violet-800" value="{{ $shortUrl }}" dir="ltr" readonly>
                <button class="mt-3 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-violet-700 px-4 font-black text-white hover:bg-violet-800" type="button" data-copy-short-link>
                    <i class="fa-regular fa-copy" aria-hidden="true"></i> نسخ الرابط
                </button>
                <div class="mt-5 grid place-items-center rounded-2xl bg-slate-50 p-4">
                    <img class="size-52 max-w-full" src="{{ route('short-links.qr', $shortLink) }}" alt="رمز QR للرابط {{ $shortLink->title }}">
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <button class="rounded-xl border border-violet-200 px-3 py-2.5 font-bold text-violet-800 hover:bg-violet-50 disabled:opacity-50" type="button" data-download-qr="png">PNG</button>
                    <button class="rounded-xl border border-violet-200 px-3 py-2.5 font-bold text-violet-800 hover:bg-violet-50 disabled:opacity-50" type="button" data-download-qr="svg">SVG</button>
                </div>
                <p class="mt-3 min-h-5 text-sm font-bold text-emerald-700" data-short-link-status aria-live="polite"></p>
            </section>

            <section class="rounded-3xl border border-red-100 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black text-red-800">حذف الرابط</h2>
                <p class="mt-2 text-sm font-medium leading-6 text-slate-500">سيتوقف الرابط نهائيًا، وسيبقى المقطع <bdi dir="ltr">{{ $shortLink->slug }}</bdi> محجوزًا لحماية المشاركات القديمة.</p>
                <form class="mt-4" method="POST" action="{{ route('short-links.destroy', $shortLink) }}" data-confirm="هل تريد حذف هذا الرابط نهائيًا؟">
                    @csrf
                    @method('DELETE')
                    <button class="min-h-11 w-full rounded-xl border border-red-200 px-4 font-black text-red-700 hover:bg-red-50" type="submit">حذف الرابط</button>
                </form>
            </section>
            @endif
        </aside>
    </div>
@endsection
