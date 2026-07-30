@extends('layouts.auth')

@section('title', 'تأكيد كلمة السر')

@section('content')
    <h1 class="text-2xl font-black">تأكيد كلمة السر</h1>
    <p class="mt-2 text-sm leading-6 text-slate-500">هذه منطقة محمية. أكّد كلمة السر للمتابعة.</p>

    <form class="mt-6 grid gap-4" method="POST" action="{{ route('password.confirm.store') }}">
        @csrf
        <label class="grid gap-2 font-bold">
            كلمة السر
            <input class="rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-violet-500 focus:ring-4 focus:ring-violet-100" type="password" name="password" required autofocus autocomplete="current-password">
        </label>
        <button class="rounded-xl bg-violet-700 px-5 py-3 font-black text-white hover:bg-violet-800" type="submit">تأكيد</button>
    </form>
@endsection
