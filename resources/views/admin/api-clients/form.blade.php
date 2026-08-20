@extends('layouts.admin')

@section('title', $apiClient->exists ? 'تعديل عميل API' : 'إضافة عميل API')

@section('content')
    @php
        $allowedIps = old('allowed_ips', $apiClient->allowed_ips ?? []);
        $allowedIpsText = is_array($allowedIps) ? implode(PHP_EOL, $allowedIps) : $allowedIps;
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm font-black text-violet-600">التكاملات الآمنة</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">{{ $apiClient->exists ? 'تعديل الموقع المرتبط' : 'إضافة موقع مرتبط' }}</h1>
            <p class="mt-2 leading-7 text-slate-500">سيكون التوكن محدودًا بصلاحية إنشاء الحسابات فقط وينتهي تلقائيًا.</p>
        </div>
        <a class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-violet-200 bg-white px-4 font-bold text-violet-700 hover:bg-violet-50" href="{{ route('admin.api-clients.index') }}"><i class="fa-solid fa-arrow-right"></i> كل المواقع</a>
    </div>

    <form class="mt-7 grid max-w-4xl gap-6 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7" method="POST" action="{{ $apiClient->exists ? route('admin.api-clients.update', $apiClient) : route('admin.api-clients.store') }}">
        @csrf
        @if($apiClient->exists) @method('PUT') @endif

        <label class="grid gap-2 text-sm font-bold text-slate-700">اسم الموقع أو النظام
            <input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="name" value="{{ old('name', $apiClient->name) }}" maxlength="120" required>
        </label>

        <div class="grid gap-5 sm:grid-cols-2">
            <label class="grid gap-2 text-sm font-bold text-slate-700">مدة صلاحية التوكن بالأيام
                <input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" type="number" name="token_expiration_days" min="1" max="90" value="{{ old('token_expiration_days', $apiClient->token_expiration_days) }}" required>
                <span class="text-xs font-medium leading-5 text-slate-500">بحد أقصى 90 يومًا. التغيير يطبّق عند توليد التوكن التالي.</span>
            </label>
            <label class="flex min-h-12 items-center gap-3 self-start rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 sm:mt-7">
                <input type="hidden" name="is_active" value="0">
                <input class="size-5 accent-violet-700" type="checkbox" name="is_active" value="1" @checked(old('is_active', $apiClient->is_active))>
                السماح لهذا الموقع باستخدام API
            </label>
        </div>

        <label class="grid gap-2 text-sm font-bold text-slate-700">عناوين IP المسموحة — عنوان واحد في كل سطر
            <textarea class="min-h-36 rounded-xl border border-slate-200 px-4 py-3 font-mono text-sm outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="allowed_ips" dir="ltr" placeholder="203.0.113.10&#10;2001:db8::10">{{ $allowedIpsText }}</textarea>
            <span class="text-xs font-medium leading-5 text-slate-500">اتركها فارغة فقط إذا لم يكن للموقع IP ثابت. اسم الدومين أو CORS ليس بديلًا أمنيًا عن IP.</span>
        </label>

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold leading-6 text-amber-900">
            <i class="fa-solid fa-triangle-exclamation ms-2"></i>
            {{ $apiClient->exists ? 'تعطيل العميل يلغي جميع توكناته فورًا. لإعادة الربط فعّله ثم ولّد توكنًا جديدًا.' : 'بعد الحفظ سيظهر التوكن مرة واحدة فقط؛ خزّنه في خادم الموقع وليس في المتصفح.' }}
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row">
            <a class="min-h-12 rounded-xl border border-slate-200 px-6 py-3 text-center font-bold text-slate-600 hover:bg-slate-50" href="{{ route('admin.api-clients.index') }}">إلغاء</a>
            <button class="min-h-12 rounded-xl bg-violet-700 px-7 font-black text-white hover:bg-violet-800" type="submit">حفظ إعدادات الربط</button>
        </div>
    </form>
@endsection
