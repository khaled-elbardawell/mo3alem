@extends('layouts.auth')

@section('title', 'تعيين كلمة سر جديدة')

@section('content')
    <h1 class="text-2xl font-black">كلمة سر جديدة</h1>
    <form class="mt-6 grid gap-4" method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label class="grid gap-2 font-bold">
            البريد الإلكتروني
            <input class="rounded-xl border border-slate-200 px-4 py-3" type="email" name="email" value="{{ old('email', $email) }}" required>
        </label>
        <label class="grid gap-2 font-bold">
            كلمة السر الجديدة
            <input class="rounded-xl border border-slate-200 px-4 py-3" type="password" name="password" required>
        </label>
        <label class="grid gap-2 font-bold">
            تأكيد كلمة السر
            <input class="rounded-xl border border-slate-200 px-4 py-3" type="password" name="password_confirmation" required>
        </label>
        <button class="rounded-xl bg-violet-700 px-5 py-3 font-black text-white" type="submit">حفظ كلمة السر</button>
    </form>
@endsection
