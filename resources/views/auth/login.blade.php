@extends('layouts.auth')

@section('title', 'تسجيل الدخول')

@section('content')
    <h1 class="text-2xl font-black">تسجيل الدخول</h1>
    <p class="mt-2 text-sm text-slate-500">ادخل لحفظ أعمالك ومتابعتها من أي جهاز.</p>

    <x-auth.social-authentication />

    <form class="grid gap-4" method="POST" action="{{ route('login') }}">
        @csrf
        <label class="grid gap-2 font-bold">
            البريد الإلكتروني
            <input class="rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-violet-500 focus:ring-4 focus:ring-violet-100" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
        </label>
        <label class="grid gap-2 font-bold">
            كلمة السر
            <input class="rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-violet-500 focus:ring-4 focus:ring-violet-100" type="password" name="password" required autocomplete="current-password">
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input class="size-4 accent-violet-700" type="checkbox" name="remember" value="1">
            تذكرني
        </label>
        <button class="rounded-xl bg-violet-700 px-5 py-3 font-black text-white hover:bg-violet-800" type="submit">دخول</button>
    </form>

    <div class="mt-5 flex items-center justify-between gap-3 text-sm font-bold">
        <a class="text-violet-700 hover:underline" href="{{ route('password.request') }}">نسيت كلمة السر؟</a>
        <a class="text-violet-700 hover:underline" href="{{ route('register') }}">إنشاء حساب</a>
    </div>
@endsection
