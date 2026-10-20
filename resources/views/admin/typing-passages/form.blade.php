@extends('layouts.admin')

@section('title', $passage->exists ? 'تعديل فقرة تدريب' : 'فقرة تدريب جديدة')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm font-black text-violet-600">مدرب الطباعة</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">{{ $passage->exists ? 'تعديل فقرة التدريب' : 'إضافة فقرة تدريب' }}</h1>
            <p class="mt-2 leading-7 text-slate-500">أدخل نصا قصيرا وواضحا وحدد زمنه أو دع النظام يحسبه تلقائيا.</p>
        </div>
        <a class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-violet-200 bg-white px-4 font-bold text-violet-700 hover:bg-violet-50" href="{{ route('admin.typing-passages.index') }}">
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            كل الفقرات
        </a>
    </div>

    <form class="mt-7 grid max-w-5xl gap-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" method="POST" action="{{ $passage->exists ? route('admin.typing-passages.update', $passage) : route('admin.typing-passages.store') }}" data-typing-passage-form>
        @csrf
        @if($passage->exists) @method('PUT') @endif

        <label class="grid gap-2 text-sm font-bold text-slate-700">اللغة
            <select class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="language" required>
                @foreach($languages as $language)
                    <option value="{{ $language->value }}" @selected(old('language', $passage->language?->value ?? 'ar') === $language->value)>{{ $language->label() }}</option>
                @endforeach
            </select>
        </label>

        <label class="grid gap-2 text-sm font-bold text-slate-700">نص الفقرة
            <textarea class="min-h-44 rounded-xl border border-slate-200 px-4 py-3 text-lg leading-8 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="content" minlength="20" maxlength="800" required data-typing-passage-content>{{ old('content', $passage->content) }}</textarea>
            <span class="text-xs font-medium text-slate-500">من 20 إلى 800 محرف. تتم إزالة المسافات الزائدة تلقائيا.</span>
        </label>

        <div class="grid gap-4 rounded-2xl border border-violet-100 bg-violet-50/50 p-4 sm:grid-cols-2 sm:items-end">
            <label class="flex min-h-12 items-center gap-3 rounded-xl bg-white px-4 font-bold text-slate-700">
                <input class="size-5 accent-violet-700" type="checkbox" name="auto_calculate_time" value="1" @checked(old('auto_calculate_time', ! $passage->exists)) data-auto-calculate-time>
                احتساب الزمن تلقائيا
            </label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">الزمن المتوقع بالثواني
                <input class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100 disabled:bg-slate-100" type="number" min="10" max="600" name="expected_seconds" value="{{ old('expected_seconds', $passage->expected_seconds) }}" data-expected-seconds>
            </label>
            <p class="text-xs font-medium leading-6 text-violet-700 sm:col-span-2" data-time-estimate>يعتمد الحساب على 30 كلمة قياسية في الدقيقة، وكل 5 محارف تساوي كلمة قياسية.</p>
        </div>

        <label class="flex min-h-12 items-center gap-3 rounded-xl border border-slate-200 px-4 font-bold text-slate-700">
            <input type="hidden" name="is_active" value="0">
            <input class="size-5 accent-emerald-600" type="checkbox" name="is_active" value="1" @checked(old('is_active', $passage->is_active ?? true))>
            الفقرة مفعلة ومتاحة في التدريب
        </label>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row">
            <a class="min-h-12 rounded-xl border border-slate-200 px-6 py-3 text-center font-bold text-slate-600 hover:bg-slate-50" href="{{ route('admin.typing-passages.index') }}">إلغاء</a>
            <button class="min-h-12 rounded-xl bg-violet-700 px-7 font-black text-white hover:bg-violet-800" type="submit">حفظ الفقرة</button>
        </div>
    </form>
@endsection
