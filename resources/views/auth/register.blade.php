@extends('layouts.auth')

@section('title', 'إنشاء حساب')

@section('content')
    <h1 class="text-2xl font-black">إنشاء حساب مجاني</h1>
    <p class="mt-2 text-sm text-slate-500">الحساب مجاني، ويمكنك حفظ أعمالك وفتحها من أي جهاز.</p>

    <form class="mt-6 grid gap-4" method="POST" action="{{ route('register') }}">
        @csrf
        <label class="grid gap-2 font-bold">
            الاسم
            <input class="rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-violet-500 focus:ring-4 focus:ring-violet-100" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        </label>
        <label class="grid gap-2 font-bold">
            البريد الإلكتروني
            <input class="rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-violet-500 focus:ring-4 focus:ring-violet-100" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
        </label>
        <label class="grid gap-2 font-bold">
            كلمة السر
            <input class="rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-violet-500 focus:ring-4 focus:ring-violet-100" type="password" name="password" required autocomplete="new-password">
        </label>
        <label class="grid gap-2 font-bold">
            تأكيد كلمة السر
            <input class="rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-violet-500 focus:ring-4 focus:ring-violet-100" type="password" name="password_confirmation" required autocomplete="new-password">
        </label>
        <button class="rounded-xl bg-violet-700 px-5 py-3 font-black text-white hover:bg-violet-800" type="submit">إنشاء الحساب</button>
    </form>
    <p class="mt-5 text-center text-sm">لديك حساب؟ <a class="font-bold text-violet-700 hover:underline" href="{{ route('login') }}">سجّل الدخول</a></p>
@endsection
