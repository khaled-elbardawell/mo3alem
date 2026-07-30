@extends('layouts.auth')

@section('title', 'تفعيل البريد')

@section('content')
    <h1 class="text-2xl font-black">فعّل بريدك الإلكتروني</h1>
    <p class="mt-3 leading-7 text-slate-600">أرسلنا رابط التفعيل إلى بريدك. يمكنك متابعة استخدام العجلة، وستبقى مسودتك المحلية محفوظة حتى التفعيل.</p>
    @if(session('status') === 'verification-link-sent')
        <p class="mt-4 rounded-xl bg-emerald-50 p-3 font-bold text-emerald-800">أُرسل رابط تفعيل جديد.</p>
    @endif
    <form class="mt-6" method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="w-full rounded-xl bg-violet-700 px-5 py-3 font-black text-white" type="submit">إعادة إرسال رابط التفعيل</button>
    </form>
    <div class="mt-4 grid grid-cols-2 gap-3">
        <a class="rounded-xl border border-violet-200 px-4 py-3 text-center font-bold text-violet-700" href="{{ route('home') }}">متابعة للعجلة</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full rounded-xl border border-slate-200 px-4 py-3 font-bold" type="submit">تسجيل الخروج</button>
        </form>
    </div>
@endsection
