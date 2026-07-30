@extends('layouts.admin')

@section('title', 'سجل الإدارة')

@section('content')
    <div>
        <p class="text-sm font-black text-violet-600">الأمان والشفافية</p>
        <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">سجل الإدارة</h1>
        <p class="mt-2 leading-7 text-slate-500">راجع التغييرات الإدارية ومعرفة من نفّذها والقيم المتأثرة.</p>
    </div>

    <form class="mt-7 grid gap-3 rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm sm:grid-cols-[1fr_auto]" method="GET">
        <label class="sr-only" for="auditAction">نوع الإجراء</label>
        <input class="min-h-12 min-w-0 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" id="auditAction" name="action" value="{{ request('action') }}" placeholder="ابحث بنوع الإجراء">
        <button class="min-h-12 rounded-xl bg-violet-700 px-6 font-black text-white hover:bg-violet-800">بحث</button>
    </form>

    <div class="mt-6 grid gap-3">
        @forelse($logs as $log)
            <details class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm open:border-violet-200">
                <summary class="grid cursor-pointer list-none grid-cols-[1fr_auto] items-center gap-3 font-bold [&::-webkit-details-marker]:hidden">
                    <span class="min-w-0">
                        <span class="block truncate">{{ $log->action }}</span>
                        <span class="mt-1 block text-sm font-normal text-slate-500">{{ $log->actor?->name ?? 'حساب محذوف' }}</span>
                    </span>
                    <span class="flex items-center gap-3 text-xs font-normal text-slate-500 sm:text-sm">
                        {{ $log->created_at->format('Y-m-d H:i') }}
                        <i class="fa-solid fa-chevron-down text-violet-600 transition-transform group-open:rotate-180"></i>
                    </span>
                </summary>
                <div class="mt-4 grid gap-4 border-t border-slate-100 pt-4 text-sm lg:grid-cols-2">
                    <div><strong>قبل</strong><pre class="mt-2 max-h-80 overflow-auto rounded-xl bg-slate-950 p-3 text-left text-xs leading-6 text-white" dir="ltr">{{ json_encode($log->before_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div>
                    <div><strong>بعد</strong><pre class="mt-2 max-h-80 overflow-auto rounded-xl bg-slate-950 p-3 text-left text-xs leading-6 text-white" dir="ltr">{{ json_encode($log->after_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div>
                    <p class="text-slate-500 lg:col-span-2"><i class="fa-solid fa-location-dot ms-2 text-violet-600"></i>IP: {{ $log->ip_address }}</p>
                </div>
            </details>
        @empty
            <div class="rounded-3xl border border-dashed border-violet-200 bg-white p-10 text-center text-slate-500">
                <i class="fa-solid fa-clock-rotate-left text-3xl text-violet-300"></i>
                <p class="mt-3 font-bold">لا توجد إجراءات مسجلة.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $logs->links() }}</div>
@endsection
