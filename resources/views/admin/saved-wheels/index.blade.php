@extends('layouts.admin')

@section('title', 'القوائم')

@section('content')
    <div>
        <p class="text-sm font-black text-violet-600">المحتوى المحفوظ</p>
        <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">القوائم المحفوظة</h1>
        <p class="mt-2 leading-7 text-slate-500">راجع القوائم ومالكيها وعدد الأسماء، أو عدّل القائمة عند الحاجة.</p>
    </div>

    <form class="mt-7 grid gap-3 rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm sm:grid-cols-[1fr_auto]" method="GET">
        <label class="sr-only" for="savedWheelSearch">البحث في القوائم</label>
        <input class="min-h-12 min-w-0 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" id="savedWheelSearch" type="search" name="search" value="{{ request('search') }}" placeholder="اسم القائمة أو المالك">
        <button class="min-h-12 rounded-xl bg-violet-700 px-7 font-black text-white hover:bg-violet-800">بحث</button>
    </form>

    <section class="mt-6 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full min-w-3xl text-right text-sm">
                <thead class="bg-slate-50 text-slate-500"><tr><th class="p-4">القائمة</th><th class="p-4">المالك</th><th class="p-4">الأسماء</th><th class="p-4">آخر تعديل</th><th class="p-4">الإجراءات</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($savedWheels as $wheel)
                        <tr class="{{ $wheel->trashed() ? 'bg-red-50/50' : 'hover:bg-violet-50/40' }}">
                            <td class="p-4 font-black">{{ $wheel->title }}</td>
                            <td class="p-4">
                                <span class="block font-bold">{{ $wheel->user?->name ?? 'حساب محذوف' }}</span>
                                <span class="block text-slate-500">{{ $wheel->user?->email }}</span>
                            </td>
                            <td class="p-4"><span class="rounded-full bg-blue-50 px-3 py-1 font-bold text-blue-700">{{ number_format($wheel->names_count) }}</span></td>
                            <td class="p-4 text-slate-500">{{ $wheel->updated_at->diffForHumans() }}</td>
                            <td class="p-4"><div class="flex items-center gap-2">
                                @if($wheel->trashed())
                                    <form method="POST" action="{{ route('admin.saved-wheels.restore', $wheel) }}">@csrf @method('PATCH')<button class="rounded-lg bg-emerald-50 px-3 py-2 font-bold text-emerald-700 hover:bg-emerald-100">استعادة</button></form>
                                @else
                                    <a class="rounded-lg bg-violet-50 px-3 py-2 font-bold text-violet-700 hover:bg-violet-100" href="{{ route('admin.saved-wheels.edit', $wheel) }}">عرض وتعديل</a>
                                    <form method="POST" action="{{ route('admin.saved-wheels.destroy', $wheel) }}" data-confirm="حذف هذه القائمة؟">@csrf @method('DELETE')<button class="rounded-lg bg-red-50 px-3 py-2 font-bold text-red-700 hover:bg-red-100">حذف</button></form>
                                @endif
                            </div></td>
                        </tr>
                    @empty
                        <tr><td class="p-10 text-center text-slate-500" colspan="5">لا توجد قوائم مطابقة للبحث.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="grid gap-3 p-3 md:hidden">
            @forelse($savedWheels as $wheel)
                <article class="{{ $wheel->trashed() ? 'border-red-200 bg-red-50/40' : 'border-slate-200 bg-white' }} rounded-2xl border p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="truncate font-black">{{ $wheel->title }}</h2>
                            <p class="mt-1 truncate text-sm text-slate-500">{{ $wheel->user?->name ?? 'حساب محذوف' }}</p>
                        </div>
                        @if($wheel->trashed())
                            <span class="shrink-0 rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">محذوفة</span>
                        @endif
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3 text-sm">
                        <p><span class="block text-xs text-slate-500">عدد الأسماء</span><strong>{{ number_format($wheel->names_count) }}</strong></p>
                        <p><span class="block text-xs text-slate-500">آخر تعديل</span><strong>{{ $wheel->updated_at->diffForHumans() }}</strong></p>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        @if($wheel->trashed())
                            <form class="col-span-2" method="POST" action="{{ route('admin.saved-wheels.restore', $wheel) }}">@csrf @method('PATCH')<button class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 font-bold text-white">استعادة القائمة</button></form>
                        @else
                            <a class="rounded-xl bg-violet-700 px-4 py-2.5 text-center font-bold text-white" href="{{ route('admin.saved-wheels.edit', $wheel) }}">عرض وتعديل</a>
                            <form method="POST" action="{{ route('admin.saved-wheels.destroy', $wheel) }}" data-confirm="حذف هذه القائمة؟">@csrf @method('DELETE')<button class="w-full rounded-xl bg-red-50 px-4 py-2.5 font-bold text-red-700">حذف</button></form>
                        @endif
                    </div>
                </article>
            @empty
                <p class="p-8 text-center text-slate-500">لا توجد قوائم مطابقة للبحث.</p>
            @endforelse
        </div>
    </section>
    <div class="mt-6">{{ $savedWheels->links() }}</div>
@endsection
