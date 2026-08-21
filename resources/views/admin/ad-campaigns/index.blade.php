@extends('layouts.admin')

@section('title', 'الحملات الإعلانية')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-black text-violet-600">الإعلانات</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">الحملات الإعلانية</h1>
            <p class="mt-2 leading-7 text-slate-500">أدر الصور والروابط والجدولة، وتابع الظهور والنقرات لكل حملة.</p>
        </div>
        <a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white shadow-[0_10px_24px_rgba(109,40,217,0.2)] hover:bg-violet-800" href="{{ route('admin.ad-campaigns.create') }}">
            <i class="fa-solid fa-plus"></i>
            حملة جديدة
        </a>
    </div>

    @php
        $statusLabels = ['draft' => 'مسودة', 'active' => 'نشطة', 'paused' => 'متوقفة'];
    @endphp
    <div class="mt-6 grid gap-4">
        @forelse($campaigns as $campaign)
            <article class="{{ $campaign->trashed() ? 'border-red-200 bg-red-50/30' : 'border-slate-200/80 bg-white' }} grid gap-4 rounded-3xl border p-4 shadow-sm sm:grid-cols-[180px_1fr] xl:grid-cols-[180px_1fr_auto] xl:items-center">
                <img class="aspect-[16/7] h-full max-h-32 w-full rounded-2xl bg-slate-100 object-cover sm:aspect-auto" src="{{ Storage::disk('public')->url($campaign->image_path) }}" alt="{{ $campaign->alt_text }}">
                <div class="min-w-0">
                    @php
                        $impressions = (int) ($campaign->impressions ?? 0);
                        $clicks = (int) ($campaign->clicks ?? 0);
                        $clickThroughRate = $impressions > 0 ? ($clicks / $impressions) * 100 : 0;
                    @endphp
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="truncate text-lg font-black">{{ $campaign->title }}</h2>
                        @if($campaign->trashed())
                            <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">محذوفة</span>
                        @else
                            <span @class([
                                'rounded-full px-2.5 py-1 text-xs font-bold',
                                'bg-emerald-100 text-emerald-700' => $campaign->status->value === 'active',
                                'bg-amber-100 text-amber-700' => $campaign->status->value === 'paused',
                                'bg-slate-100 text-slate-600' => $campaign->status->value === 'draft',
                            ])>{{ $statusLabels[$campaign->status->value] }}</span>
                        @endif
                    </div>
                    <p class="mt-2 text-sm text-slate-500">{{ $campaign->placement->label() }} · وزن {{ $campaign->weight }}</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-sm">
                        <span class="rounded-lg bg-violet-50 px-3 py-1.5 font-bold text-violet-700"><i class="fa-regular fa-eye ms-1"></i>{{ number_format($impressions) }} مشاهد</span>
                        <span class="rounded-lg bg-blue-50 px-3 py-1.5 font-bold text-blue-700"><i class="fa-solid fa-arrow-pointer ms-1"></i>{{ number_format($clicks) }} نقرة</span>
                        <span class="rounded-lg bg-emerald-50 px-3 py-1.5 font-bold text-emerald-700"><i class="fa-solid fa-chart-line ms-1"></i>{{ number_format($clickThroughRate, 2) }}% فعالية</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:col-span-2 xl:col-span-1 xl:flex">
                    @if($campaign->trashed())
                        <form class="col-span-2" method="POST" action="{{ route('admin.ad-campaigns.restore', $campaign) }}">@csrf @method('PATCH')<button class="min-h-11 w-full rounded-xl bg-emerald-600 px-4 font-bold text-white">استعادة</button></form>
                    @else
                        <a class="inline-flex min-h-11 items-center justify-center rounded-xl bg-violet-50 px-4 font-bold text-violet-700 hover:bg-violet-100" href="{{ route('admin.ad-campaigns.edit', $campaign) }}">تعديل</a>
                        <form method="POST" action="{{ route('admin.ad-campaigns.destroy', $campaign) }}" data-confirm="حذف هذه الحملة؟">@csrf @method('DELETE')<button class="min-h-11 w-full rounded-xl bg-red-50 px-4 font-bold text-red-700 hover:bg-red-100">حذف</button></form>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-violet-200 bg-white p-10 text-center text-slate-500">
                <i class="fa-solid fa-rectangle-ad text-4xl text-violet-300"></i>
                <p class="mt-3 font-bold">لا توجد حملات إعلانية بعد.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $campaigns->links() }}</div>
@endsection
