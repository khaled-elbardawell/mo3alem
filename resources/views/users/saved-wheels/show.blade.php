@extends('layouts.app')

@section('title', $savedWheel->title)

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a class="inline-flex items-center gap-2 font-bold text-violet-700 hover:text-violet-900"
                href="{{ route('dashboard', ['section' => 'lists']) }}">
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                العودة إلى قوائمي
            </a>
            <h1 class="mt-4 text-3xl font-black">{{ $savedWheel->title }}</h1>
            <p class="mt-2 text-slate-500">تفاصيل قائمة الأسماء المحفوظة والقابلة لإعادة الاستخدام.</p>
        </div>
        <a class="inline-flex min-h-12 items-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white hover:bg-violet-800"
            href="{{ route('tools.wheel', ['wheel' => $savedWheel]) }}">
            <i class="fa-solid fa-play" aria-hidden="true"></i>
            استخدام القائمة
        </a>
    </div>

    <section class="mt-7 grid gap-3 sm:grid-cols-3" aria-label="ملخص القائمة">
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-sm font-bold text-slate-500">عدد الأسماء</p>
            <p class="mt-2 text-2xl font-black">{{ number_format($savedWheel->names_count) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-sm font-bold text-slate-500">نسخة الحفظ</p>
            <p class="mt-2 text-2xl font-black">{{ number_format($savedWheel->version) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-sm font-bold text-slate-500">آخر تعديل</p>
            <time class="mt-2 block font-black" datetime="{{ $savedWheel->updated_at->toAtomString() }}">
                {{ $savedWheel->updated_at->locale('ar')->translatedFormat('j F Y، g:i A') }}
            </time>
        </div>
    </section>

    <div class="mt-4 rounded-2xl border border-sky-100 bg-sky-50 p-4 text-sm text-sky-900">
        <i class="fa-solid fa-circle-info ml-1" aria-hidden="true"></i>
        هذه القائمة تحفظ الأسماء فقط. الفائزون ونتائج اللفات محفوظون داخل كل مسابقة بشكل مستقل.
    </div>

    <x-user.names-manager
        class="mt-7"
        add-route="{{ route('user.saved-wheels.names.store', $savedWheel) }}"
        :clear-url="route('user.saved-wheels.show', $savedWheel)"
        description="ابحث ورتّب الأسماء، أو أضف واحذف مباشرة من القائمة."
        destroy-route-name="user.saved-wheels.names.destroy"
        empty-message="{{ $savedWheel->names_count === 0 ? 'لا توجد أسماء في هذه القائمة' : 'لا توجد أسماء تطابق البحث' }}"
        heading="إدارة الأسماء"
        :name-search="$nameSearch"
        :name-sort="$nameSort"
        :names="$names"
        :resource="$savedWheel"
        resource-route-parameter="savedWheel"
        :total-count="$savedWheel->names_count" />
@endsection
