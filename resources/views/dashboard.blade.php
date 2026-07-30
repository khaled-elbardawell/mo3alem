@extends('layouts.app')

@section('title', 'قوائمي')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black">قوائمي</h1>
            <p class="mt-2 text-slate-500">افتح أي قائمة في العجلة أو أدرها من هنا.</p>
        </div>
        <a class="rounded-xl bg-violet-700 px-5 py-3 font-black text-white hover:bg-violet-800" href="{{ route('home') }}">عجلة جديدة</a>
    </div>

    @unless(auth()->user()->hasVerifiedEmail())
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
            يمكنك قراءة قوائمك، لكن التعديل والحفظ متاحان بعد تفعيل البريد.
            <a class="font-black underline" href="{{ route('verification.notice') }}">تفعيل البريد</a>
        </div>
    @endunless

    <form class="mt-7 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_220px_auto]" method="GET">
        <input class="rounded-xl border border-slate-200 px-4 py-3" type="search" name="search" value="{{ $search }}" placeholder="ابحث باسم القائمة">
        <select class="rounded-xl border border-slate-200 px-4 py-3" name="sort">
            <option value="">آخر تعديل</option>
            <option value="title" @selected($sort === 'title')>الاسم</option>
            <option value="names" @selected($sort === 'names')>عدد الأسماء</option>
            <option value="oldest" @selected($sort === 'oldest')>الأقدم تعديلًا</option>
        </select>
        <button class="rounded-xl bg-slate-900 px-5 py-3 font-bold text-white" type="submit">تطبيق</button>
    </form>

    @if($savedWheels->isEmpty())
        <div class="mt-7 rounded-3xl border border-dashed border-violet-200 bg-white p-10 text-center">
            <p class="text-xl font-black">لا توجد قوائم محفوظة بعد</p>
            <p class="mt-2 text-slate-500">استخدم العجلة ثم اختر «حفظ باسم».</p>
        </div>
    @else
        <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($savedWheels as $wheel)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="truncate text-lg font-black">{{ $wheel->title }}</h2>
                    <div class="mt-3 flex justify-between gap-3 text-sm text-slate-500">
                        <span>{{ number_format($wheel->names_count) }} اسم</span>
                        <time datetime="{{ $wheel->updated_at->toAtomString() }}">{{ $wheel->updated_at->diffForHumans() }}</time>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-2">
                        <a class="rounded-xl bg-violet-700 px-4 py-2.5 text-center font-bold text-white" href="{{ route('home', ['wheel' => $wheel]) }}">فتح</a>
                        <a class="rounded-xl border border-violet-200 px-4 py-2.5 text-center font-bold text-violet-700" href="{{ route('home', ['wheel' => $wheel, 'copy' => 1]) }}">حفظ كنسخة</a>
                    </div>
                    @if(auth()->user()->hasVerifiedEmail())
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <button class="rounded-xl px-4 py-2.5 font-bold text-violet-700 hover:bg-violet-50" type="button"
                                data-rename-wheel="{{ route('saved-wheels.update', $wheel) }}"
                                data-current-title="{{ $wheel->title }}"
                                data-version="{{ $wheel->version }}">إعادة تسمية</button>
                            <form method="POST" action="{{ route('saved-wheels.destroy', $wheel) }}" data-confirm="حذف هذه القائمة؟">
                                @csrf
                                @method('DELETE')
                                <button class="w-full rounded-xl px-4 py-2.5 font-bold text-red-700 hover:bg-red-50" type="submit">حذف</button>
                            </form>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
        <div class="mt-7">{{ $savedWheels->links() }}</div>
    @endif
@endsection
