@extends('layouts.admin')

@section('title', 'فقرات مدرب الطباعة')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-black text-violet-600">مدرب الطباعة</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">فقرات التدريب</h1>
            <p class="mt-2 leading-7 text-slate-500">ابحث في الفقرات العربية والإنجليزية وعدّل زمنها أو حالة ظهورها.</p>
        </div>
        <a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white shadow-[0_10px_24px_rgba(109,40,217,0.2)] hover:bg-violet-800" href="{{ route('admin.typing-passages.create') }}">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            فقرة جديدة
        </a>
    </div>

    <form class="mt-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[minmax(0,1fr)_180px_180px_auto]" method="GET">
        <label class="grid gap-1.5 text-sm font-bold text-slate-700">البحث
            <input class="min-h-11 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="search" value="{{ request('search') }}" placeholder="ابحث داخل نص الفقرة">
        </label>
        <label class="grid gap-1.5 text-sm font-bold text-slate-700">اللغة
            <select class="min-h-11 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="language">
                <option value="">كل اللغات</option>
                @foreach($languages as $language)
                    <option value="{{ $language->value }}" @selected(request('language') === $language->value)>{{ $language->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1.5 text-sm font-bold text-slate-700">الحالة
            <select class="min-h-11 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="status">
                <option value="">الكل</option>
                <option value="active" @selected(request('status') === 'active')>مفعلة</option>
                <option value="inactive" @selected(request('status') === 'inactive')>معطلة</option>
                <option value="deleted" @selected(request('status') === 'deleted')>محذوفة</option>
            </select>
        </label>
        <button class="min-h-11 self-end rounded-xl bg-slate-900 px-5 font-black text-white hover:bg-slate-800" type="submit">تصفية</button>
    </form>

    <div class="mt-6 grid gap-4">
        @forelse($passages as $passage)
            <article class="{{ $passage->trashed() ? 'border-red-200 bg-red-50/30' : 'border-slate-200 bg-white' }} rounded-2xl border p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                            <span class="rounded-full bg-violet-100 px-3 py-1 text-violet-700">{{ $passage->language->label() }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ $passage->word_count }} كلمة</span>
                            <span class="rounded-full bg-blue-100 px-3 py-1 text-blue-700">{{ $passage->expected_seconds }} ثانية</span>
                            @if($passage->trashed())
                                <span class="rounded-full bg-red-100 px-3 py-1 text-red-700">محذوفة</span>
                            @elseif($passage->is_active)
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-700">مفعلة</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-700">معطلة</span>
                            @endif
                        </div>
                        <p class="mt-3 line-clamp-3 leading-8 font-medium text-slate-700" dir="{{ $passage->language->direction() }}">{{ $passage->content }}</p>
                    </div>
                    <div class="grid shrink-0 grid-cols-2 gap-2">
                        @if($passage->trashed())
                            <form class="col-span-2" method="POST" action="{{ route('admin.typing-passages.restore', $passage) }}">@csrf @method('PATCH')<button class="min-h-11 rounded-xl bg-emerald-600 px-4 font-bold text-white hover:bg-emerald-700" type="submit">استعادة</button></form>
                        @else
                            <a class="inline-flex min-h-11 items-center justify-center rounded-xl bg-violet-50 px-4 font-bold text-violet-700 hover:bg-violet-100" href="{{ route('admin.typing-passages.edit', $passage) }}">تعديل</a>
                            <form method="POST" action="{{ route('admin.typing-passages.destroy', $passage) }}" data-confirm="حذف فقرة التدريب؟">@csrf @method('DELETE')<button class="min-h-11 rounded-xl bg-red-50 px-4 font-bold text-red-700 hover:bg-red-100" type="submit">حذف</button></form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-violet-200 bg-white p-10 text-center text-slate-500">
                <i class="fa-regular fa-keyboard text-4xl text-violet-300" aria-hidden="true"></i>
                <p class="mt-3 font-bold">لا توجد فقرات مطابقة.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $passages->links() }}</div>
@endsection
