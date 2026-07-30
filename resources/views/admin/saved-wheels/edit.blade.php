@extends('layouts.admin')

@section('title', 'تعديل قائمة')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm font-black text-violet-600">القوائم المحفوظة</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">تعديل القائمة</h1>
            <p class="mt-2 text-slate-500">المالك: {{ $savedWheel->user?->name ?? 'حساب محذوف' }} — {{ $savedWheel->user?->email }}</p>
        </div>
        <a class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-violet-200 bg-white px-4 font-bold text-violet-700 hover:bg-violet-50" href="{{ route('admin.saved-wheels.index') }}">
            <i class="fa-solid fa-arrow-right"></i>
            كل القوائم
        </a>
    </div>

    <form class="mt-7 grid max-w-4xl gap-5 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7" method="POST" action="{{ route('admin.saved-wheels.update', $savedWheel) }}">
        @csrf @method('PUT')
        <label class="grid gap-2 text-sm font-bold text-slate-700">اسم القائمة<input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="title" maxlength="120" value="{{ old('title', $savedWheel->title) }}" required></label>
        <label class="grid gap-2 text-sm font-bold text-slate-700">الأسماء — اسم في كل سطر
            <textarea class="min-h-80 resize-y rounded-xl border border-slate-200 px-4 py-3 leading-7 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="names_text">{{ old('names_text', implode("\n", $savedWheel->names)) }}</textarea>
        </label>
        <div class="flex items-start gap-3 rounded-2xl bg-blue-50 p-4 text-sm leading-6 text-blue-900">
            <i class="fa-solid fa-circle-info mt-1"></i>
            <p>يحفظ ترتيب الأسماء كما يظهر هنا. نتائج المسابقات مستقلة عن القوائم المحفوظة.</p>
        </div>
        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row">
            <a class="min-h-12 rounded-xl border border-slate-200 px-6 py-3 text-center font-bold text-slate-600 hover:bg-slate-50" href="{{ route('admin.saved-wheels.index') }}">إلغاء</a>
            <button class="min-h-12 rounded-xl bg-violet-700 px-7 font-black text-white hover:bg-violet-800" type="submit">حفظ التغييرات</button>
        </div>
    </form>
@endsection
