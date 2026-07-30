@extends('layouts.app')

@section('title', 'الملف الشخصي')

@section('content')
    <h1 class="text-3xl font-black">الملف الشخصي</h1>
    <div class="mt-7 grid gap-6 lg:grid-cols-2">
        <section class="rounded-3xl border border-slate-200 bg-white p-6">
            <h2 class="text-xl font-black">بيانات الحساب</h2>
            <form class="mt-5 grid gap-4" method="POST" action="{{ route('user-profile-information.update') }}">
                @csrf
                @method('PUT')
                @if($errors->updateProfileInformation->any())
                    <ul class="list-inside list-disc rounded-xl bg-red-50 p-3 text-sm text-red-800" role="alert">
                        @foreach($errors->updateProfileInformation->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
                <label class="grid gap-2 font-bold">
                    الاسم
                    <input class="rounded-xl border border-slate-200 px-4 py-3" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                </label>
                <label class="grid gap-2 font-bold">
                    البريد الإلكتروني
                    <input class="rounded-xl border border-slate-200 px-4 py-3" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                </label>
                @if(auth()->user()->hasVerifiedEmail())
                    <p class="text-sm font-bold text-emerald-700">البريد مفعّل</p>
                @else
                    <p class="text-sm font-bold text-amber-700">البريد غير مفعّل</p>
                @endif
                <button class="rounded-xl bg-violet-700 px-5 py-3 font-black text-white" type="submit">حفظ البيانات</button>
            </form>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6">
            <h2 class="text-xl font-black">تغيير كلمة السر</h2>
            <form class="mt-5 grid gap-4" method="POST" action="{{ route('user-password.update') }}">
                @csrf
                @method('PUT')
                @if($errors->updatePassword->any())
                    <ul class="list-inside list-disc rounded-xl bg-red-50 p-3 text-sm text-red-800" role="alert">
                        @foreach($errors->updatePassword->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
                <label class="grid gap-2 font-bold">
                    كلمة السر الحالية
                    <input class="rounded-xl border border-slate-200 px-4 py-3" type="password" name="current_password" required autocomplete="current-password">
                </label>
                <label class="grid gap-2 font-bold">
                    كلمة السر الجديدة
                    <input class="rounded-xl border border-slate-200 px-4 py-3" type="password" name="password" required autocomplete="new-password">
                </label>
                <label class="grid gap-2 font-bold">
                    تأكيد كلمة السر
                    <input class="rounded-xl border border-slate-200 px-4 py-3" type="password" name="password_confirmation" required autocomplete="new-password">
                </label>
                <button class="rounded-xl bg-slate-900 px-5 py-3 font-black text-white" type="submit">تغيير كلمة السر</button>
            </form>
        </section>
    </div>
@endsection
