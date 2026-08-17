@extends('layouts.admin')

@section('title', $campaign->exists ? 'تعديل حملة' : 'حملة جديدة')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm font-black text-violet-600">الحملات الإعلانية</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">{{ $campaign->exists ? 'تعديل الحملة' : 'حملة جديدة' }}</h1>
            <p class="mt-2 leading-7 text-slate-500">أدخل محتوى الإعلان وحدد مكان ظهوره والفترة المناسبة للنشر.</p>
        </div>
        <a class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-violet-200 bg-white px-4 font-bold text-violet-700 hover:bg-violet-50" href="{{ route('admin.ad-campaigns.index') }}">
            <i class="fa-solid fa-arrow-right"></i>
            كل الحملات
        </a>
    </div>

    <form class="mt-7 grid max-w-5xl gap-6 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7" method="POST" enctype="multipart/form-data" action="{{ $campaign->exists ? route('admin.ad-campaigns.update', $campaign) : route('admin.ad-campaigns.store') }}">
        @csrf
        @if($campaign->exists) @method('PUT') @endif

        <section class="grid gap-5 lg:grid-cols-[1fr_320px]">
            <div class="grid gap-5">
                <label class="grid gap-2 text-sm font-bold text-slate-700">عنوان الحملة<input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="title" value="{{ old('title', $campaign->title) }}" required></label>
                <label class="grid gap-2 text-sm font-bold text-slate-700">رابط الإعلان<input class="min-h-12 rounded-xl border border-slate-200 px-4 text-left outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" dir="ltr" type="url" name="target_url" value="{{ old('target_url', $campaign->target_url) }}" required></label>
                <label class="grid gap-2 text-sm font-bold text-slate-700">النص البديل<input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="alt_text" value="{{ old('alt_text', $campaign->alt_text) }}" required></label>
            </div>
            <div class="grid content-start gap-3 rounded-2xl border border-dashed border-violet-200 bg-violet-50/50 p-4">
                <label class="grid gap-2 text-sm font-bold text-slate-700">صورة الإعلان
                    <input class="min-w-0 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm file:ms-3 file:rounded-lg file:border-0 file:bg-violet-100 file:px-3 file:py-2 file:font-bold file:text-violet-800" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,.gif" data-preview-target="adImagePreview" {{ $campaign->exists ? '' : 'required' }}>
                </label>
                <img class="{{ $campaign->exists ? '' : 'hidden' }} aspect-video max-h-48 w-full rounded-xl bg-white object-contain" id="adImagePreview" src="{{ $campaign->exists ? Storage::disk('public')->url($campaign->image_path) : '' }}" alt="معاينة صورة الإعلان">
                <p class="text-xs leading-5 text-slate-500">JPG أو PNG أو WebP أو GIF، وبحجم لا يتجاوز 5MB.</p>
            </div>
        </section>

        <section class="grid gap-4 border-t border-slate-100 pt-6 sm:grid-cols-3">
            <label class="grid gap-2 text-sm font-bold text-slate-700">الموضع<select class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="placement">@foreach($placements as $placement)<option value="{{ $placement->value }}" @selected(old('placement', $campaign->placement?->value) === $placement->value)>{{ ['top' => 'علوي', 'side' => 'جانبي', 'bottom' => 'سفلي'][$placement->value] }}</option>@endforeach</select></label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">الحالة<select class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="status">@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $campaign->status?->value) === $status->value)>{{ ['draft' => 'مسودة', 'active' => 'نشطة', 'paused' => 'متوقفة'][$status->value] }}</option>@endforeach</select></label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">الوزن<input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" type="number" min="1" max="1000" name="weight" value="{{ old('weight', $campaign->weight ?? 1) }}" required></label>
        </section>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2 text-sm font-bold text-slate-700">تبدأ في<input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" type="datetime-local" name="starts_at" value="{{ old('starts_at', $campaign->starts_at?->timezone(config('app.timezone'))->format('Y-m-d\TH:i')) }}"></label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">تنتهي في<input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" type="datetime-local" name="ends_at" value="{{ old('ends_at', $campaign->ends_at?->timezone(config('app.timezone'))->format('Y-m-d\TH:i')) }}"></label>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row">
            <a class="min-h-12 rounded-xl border border-slate-200 px-6 py-3 text-center font-bold text-slate-600 hover:bg-slate-50" href="{{ route('admin.ad-campaigns.index') }}">إلغاء</a>
            <button class="min-h-12 rounded-xl bg-violet-700 px-7 font-black text-white hover:bg-violet-800" type="submit">حفظ الحملة</button>
        </div>
    </form>
@endsection
