@extends('layouts.admin')

@section('title', 'إعدادات SEO')

@section('content')
    <div>
        <p class="text-sm font-black text-violet-600">ظهور الموقع</p>
        <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">إعدادات SEO</h1>
        <p class="mt-2 max-w-3xl leading-7 text-slate-500">تحكم بالمعلومات التي تظهر لمحركات البحث وعند مشاركة رابط الموقع على الشبكات الاجتماعية.</p>
    </div>

    <form class="mt-7 grid max-w-5xl gap-6 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7" method="POST" enctype="multipart/form-data" action="{{ route('admin.seo.update') }}">
        @csrf @method('PUT')

        <section class="grid gap-5">
            <div>
                <h2 class="text-lg font-black">بيانات الصفحة الرئيسية</h2>
                <p class="mt-1 text-sm text-slate-500">العنوان والوصف الأساسيان اللذان تعتمد عليهما محركات البحث.</p>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-bold text-slate-700">اسم الموقع<input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="site_name" value="{{ old('site_name', $seo->site_name) }}" required></label>
                <label class="grid gap-2 text-sm font-bold text-slate-700">عنوان الصفحة<input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="title" value="{{ old('title', $seo->title) }}" required></label>
                <label class="grid gap-2 text-sm font-bold text-slate-700 sm:col-span-2">الوصف<textarea class="min-h-32 resize-y rounded-xl border border-slate-200 px-4 py-3 leading-7 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="description">{{ old('description', $seo->description) }}</textarea></label>
                <label class="grid gap-2 text-sm font-bold text-slate-700">الكلمات المفتاحية<input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="keywords" value="{{ old('keywords', $seo->keywords) }}" placeholder="عجلة، أسماء، اختيار عشوائي"></label>
                <label class="grid gap-2 text-sm font-bold text-slate-700">Canonical URL<input class="min-h-12 rounded-xl border border-slate-200 px-4 text-left outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" dir="ltr" type="url" name="canonical_url" value="{{ old('canonical_url', $seo->canonical_url) }}"></label>
            </div>
        </section>

        <section class="grid gap-5 border-t border-slate-100 pt-6 lg:grid-cols-[1fr_320px]">
            <div class="grid content-start gap-5">
                <div>
                    <h2 class="text-lg font-black">المشاركة الاجتماعية</h2>
                    <p class="mt-1 text-sm text-slate-500">الصورة ونوع البطاقة عند مشاركة رابط الموقع.</p>
                </div>
                <label class="grid gap-2 text-sm font-bold text-slate-700">بطاقة Twitter<select class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="twitter_card"><option value="summary" @selected(old('twitter_card', $seo->twitter_card) === 'summary')>بطاقة مختصرة</option><option value="summary_large_image" @selected(old('twitter_card', $seo->twitter_card) === 'summary_large_image')>بطاقة بصورة كبيرة</option></select></label>
            </div>
            <div class="grid content-start gap-3 rounded-2xl border border-dashed border-violet-200 bg-violet-50/50 p-4">
                <label class="grid gap-2 text-sm font-bold text-slate-700">صورة المشاركة<input class="min-w-0 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm file:ms-3 file:rounded-lg file:border-0 file:bg-violet-100 file:px-3 file:py-2 file:font-bold file:text-violet-800" type="file" name="og_image" accept=".jpg,.jpeg,.png,.webp" data-preview-target="seoImagePreview"></label>
                <img class="{{ $seo->og_image_path ? '' : 'hidden' }} aspect-video max-h-48 w-full rounded-xl bg-white object-contain" id="seoImagePreview" src="{{ $seo->og_image_path ? Storage::disk('public')->url($seo->og_image_path) : '' }}" alt="معاينة صورة المشاركة">
            </div>
        </section>

        <label class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 font-bold text-emerald-900">
            <input type="hidden" name="allow_indexing" value="0">
            <input class="mt-0.5 size-5 shrink-0 accent-violet-700" type="checkbox" name="allow_indexing" value="1" @checked(old('allow_indexing', $seo->allow_indexing))>
            <span>السماح لمحركات البحث بالفهرسة<span class="mt-1 block text-sm font-normal leading-6 text-emerald-800">عند إيقافه سيطلب الموقع من محركات البحث عدم فهرسة الصفحة.</span></span>
        </label>

        <div class="border-t border-slate-100 pt-5">
            <button class="min-h-12 w-full rounded-xl bg-violet-700 px-7 font-black text-white hover:bg-violet-800 sm:w-auto" type="submit">حفظ الإعدادات</button>
        </div>
    </form>
@endsection
