@extends('layouts.auth')

@section('title', 'استعادة كلمة السر')

@section('content')
    <h1 class="text-2xl font-black">استعادة كلمة السر</h1>
    <p class="mt-2 text-sm text-slate-500">سنرسل رابط الاستعادة إلى بريدك الإلكتروني.</p>
    <form class="mt-6 grid gap-4" method="POST" action="{{ route('password.email') }}">
        @csrf
        <label class="grid gap-2 font-bold">
            البريد الإلكتروني
            <input class="rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-violet-500 focus:ring-4 focus:ring-violet-100" type="email" name="email" value="{{ old('email') }}" required autofocus>
        </label>
        <button class="rounded-xl bg-violet-700 px-5 py-3 font-black text-white" type="submit">إرسال رابط الاستعادة</button>
    </form>
    <a class="mt-5 block text-center text-sm font-bold text-violet-700" href="{{ route('login') }}">العودة للدخول</a>
@endsection
