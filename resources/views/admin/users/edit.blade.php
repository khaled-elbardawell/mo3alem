@extends('layouts.admin')

@section('title', 'تعديل مستخدم')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm font-black text-violet-600">إدارة الحسابات</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">تعديل المستخدم</h1>
            <p class="mt-2 text-slate-500">{{ $user->email }}</p>
        </div>
        <a class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-violet-200 bg-white px-4 font-bold text-violet-700 hover:bg-violet-50" href="{{ route('admin.users.index') }}">
            <i class="fa-solid fa-arrow-right"></i>
            كل المستخدمين
        </a>
    </div>

    <form class="mt-7 grid max-w-3xl gap-5 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7" method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf @method('PUT')
        <div class="grid gap-5 sm:grid-cols-2">
            <label class="grid gap-2 text-sm font-bold text-slate-700">الاسم<input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="name" value="{{ old('name', $user->name) }}" required></label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">البريد<input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" type="email" name="email" value="{{ old('email', $user->email) }}" required></label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">الصلاحية<select class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="role"><option value="user" @selected(old('role', $user->role->value) === 'user')>مستخدم</option><option value="admin" @selected(old('role', $user->role->value) === 'admin')>أدمن</option></select></label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">الحالة<select class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="status"><option value="active" @selected(old('status', $user->status->value) === 'active')>نشط</option><option value="suspended" @selected(old('status', $user->status->value) === 'suspended')>معلّق</option></select></label>
        </div>
        <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">
            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
            <p>تغيير البريد يلغي التفعيل ويرسل رابط تفعيل جديد. تعليق الحساب ينهي كل جلسات المستخدم فورًا.</p>
        </div>
        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row">
            <a class="min-h-12 rounded-xl border border-slate-200 px-6 py-3 text-center font-bold text-slate-600 hover:bg-slate-50" href="{{ route('admin.users.index') }}">إلغاء</a>
            <button class="min-h-12 rounded-xl bg-violet-700 px-7 font-black text-white hover:bg-violet-800" type="submit">حفظ التغييرات</button>
        </div>
    </form>
@endsection
