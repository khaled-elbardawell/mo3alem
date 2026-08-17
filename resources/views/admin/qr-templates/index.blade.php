@extends('layouts.admin')

@section('title', 'قوالب QR')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-black text-violet-600">أدوات الموقع</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">قوالب QR</h1>
            <p class="mt-2 leading-7 text-slate-500">أدر صور الإطارات ومكان الرمز وحجمه داخل كل قالب.</p>
        </div>
        <a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white shadow-[0_10px_24px_rgba(109,40,217,0.2)] hover:bg-violet-800" href="{{ route('admin.qr-templates.create') }}"><i class="fa-solid fa-plus"></i> قالب جديد</a>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($templates as $template)
            <article class="{{ $template->trashed() ? 'border-red-200 bg-red-50/30' : 'border-slate-200/80 bg-white' }} overflow-hidden rounded-3xl border shadow-sm">
                <a class="block bg-slate-100" href="{{ $template->imageUrl() }}" target="_blank" rel="noopener"><img class="aspect-square w-full object-contain" src="{{ $template->imageUrl() }}" alt="{{ $template->label }}" loading="lazy"></a>
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-black">{{ $template->label }}</h2>
                            <p class="mt-1 text-xs font-bold text-slate-500">{{ $template->width }} × {{ $template->height }} · الرمز {{ $template->qr_size }}px · ترتيب {{ $template->sort_order }}</p>
                        </div>
                        @if($template->trashed())<span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">محذوف</span>@elseif($template->is_active)<span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">ظاهر</span>@else<span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">مخفي</span>@endif
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        @if($template->trashed())
                            <form class="col-span-2" method="POST" action="{{ route('admin.qr-templates.restore', $template) }}">@csrf @method('PATCH')<button class="min-h-11 w-full rounded-xl bg-emerald-600 px-4 font-bold text-white hover:bg-emerald-700">استعادة</button></form>
                        @else
                            <a class="inline-flex min-h-11 items-center justify-center rounded-xl bg-violet-50 px-4 font-bold text-violet-700 hover:bg-violet-100" href="{{ route('admin.qr-templates.edit', $template) }}">تعديل</a>
                            <form method="POST" action="{{ route('admin.qr-templates.destroy', $template) }}" data-confirm="حذف هذا القالب من الموقع؟">@csrf @method('DELETE')<button class="min-h-11 w-full rounded-xl bg-red-50 px-4 font-bold text-red-700 hover:bg-red-100">حذف</button></form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="sm:col-span-2 xl:col-span-3 rounded-3xl border border-dashed border-violet-200 bg-white p-10 text-center text-slate-500"><i class="fa-solid fa-qrcode text-4xl text-violet-300"></i><p class="mt-3 font-bold">لا توجد قوالب QR بعد.</p></div>
        @endforelse
    </div>
    <div class="mt-6">{{ $templates->links() }}</div>
@endsection
