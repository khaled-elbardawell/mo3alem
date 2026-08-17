@extends('layouts.admin')

@section('title', $template->exists ? 'تعديل قالب شهادة' : 'قالب شهادة جديد')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm font-black text-violet-600">قوالب الشهادات</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">{{ $template->exists ? 'تعديل القالب' : 'قالب شهادة جديد' }}</h1>
            <p class="mt-2 leading-7 text-slate-500">الأبعاد هنا هي أبعاد مساحة تصميم الشهادة داخل المحرر.</p>
        </div>
        <a class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-violet-200 bg-white px-4 font-bold text-violet-700 hover:bg-violet-50" href="{{ route('admin.certificate-templates.index') }}"><i class="fa-solid fa-arrow-right"></i> كل القوالب</a>
    </div>

    <form class="mt-7 grid max-w-5xl gap-6 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7" method="POST" enctype="multipart/form-data" action="{{ $template->exists ? route('admin.certificate-templates.update', $template) : route('admin.certificate-templates.store') }}">
        @csrf
        @if($template->exists) @method('PUT') @endif

        <section class="grid gap-5 lg:grid-cols-[1fr_320px]">
            <div class="grid content-start gap-5">
                <label class="grid gap-2 text-sm font-bold text-slate-700">اسم القالب<input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="label" value="{{ old('label', $template->label) }}" required></label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-2 text-sm font-bold text-slate-700">العرض<input class="min-h-12 rounded-xl border border-slate-200 px-4" type="number" min="600" max="4000" name="width" value="{{ old('width', $template->width) }}" required></label>
                    <label class="grid gap-2 text-sm font-bold text-slate-700">الارتفاع<input class="min-h-12 rounded-xl border border-slate-200 px-4" type="number" min="400" max="4000" name="height" value="{{ old('height', $template->height) }}" required></label>
                    <label class="grid gap-2 text-sm font-bold text-slate-700">الترتيب<input class="min-h-12 rounded-xl border border-slate-200 px-4" type="number" min="0" max="10000" name="sort_order" value="{{ old('sort_order', $template->sort_order) }}" required></label>
                    <label class="flex min-h-12 items-center gap-3 self-end rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700"><input type="hidden" name="is_active" value="0"><input class="size-5 accent-violet-700" type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active))> إظهار القالب في الموقع</label>
                </div>
            </div>
            <div class="grid content-start gap-3 rounded-2xl border border-dashed border-violet-200 bg-violet-50/50 p-4">
                <label class="grid gap-2 text-sm font-bold text-slate-700">صورة القالب<input class="min-w-0 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm file:ms-3 file:rounded-lg file:border-0 file:bg-violet-100 file:px-3 file:py-2 file:font-bold file:text-violet-800" type="file" name="image" accept=".jpg,.jpeg,.png,.webp" data-preview-target="templateImagePreview" {{ $template->exists ? '' : 'required' }}></label>
                <img class="{{ $template->exists ? '' : 'hidden' }} aspect-[1.414/1] max-h-56 w-full rounded-xl bg-white object-contain" id="templateImagePreview" src="{{ $template->exists ? $template->imageUrl() : '' }}" alt="معاينة القالب">
                <p class="text-xs leading-5 text-slate-500">JPG أو PNG أو WebP، حتى 8MB.</p>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row">
            <a class="min-h-12 rounded-xl border border-slate-200 px-6 py-3 text-center font-bold text-slate-600 hover:bg-slate-50" href="{{ route('admin.certificate-templates.index') }}">إلغاء</a>
            <button class="min-h-12 rounded-xl bg-violet-700 px-7 font-black text-white hover:bg-violet-800" type="submit">حفظ القالب</button>
        </div>
    </form>
@endsection
