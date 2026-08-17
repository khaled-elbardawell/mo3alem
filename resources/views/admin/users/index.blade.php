@extends('layouts.admin')

@section('title', 'المستخدمون')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm font-black text-violet-600">إدارة الحسابات</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">المستخدمون</h1>
            <p class="mt-2 leading-7 text-slate-500">ابحث عن الحسابات، راجع حالتها، وعدّل الصلاحيات بطريقة واضحة وآمنة.</p>
        </div>
        <a class="inline-flex min-h-12 items-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white shadow-[0_10px_24px_rgba(109,40,217,0.2)] transition hover:bg-violet-800" href="{{ route('admin.users.create') }}">
            <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
            إنشاء حساب
        </a>
    </div>

    <form class="mt-7 grid gap-4 rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm sm:grid-cols-2 xl:grid-cols-[2fr_1fr_auto] xl:items-end" method="GET">
        <label class="grid gap-2 text-sm font-bold text-slate-700">
            البحث
            <input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" type="search" name="search" value="{{ request('search') }}" placeholder="الاسم أو البريد">
        </label>
        <label class="grid gap-2 text-sm font-bold text-slate-700">
            حالة الحساب
            <select class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100" name="status">
                <option value="">كل الحالات</option>
                <option value="active" @selected(request('status') === 'active')>نشط</option>
                <option value="suspended" @selected(request('status') === 'suspended')>معلّق</option>
            </select>
        </label>
        <button class="min-h-12 rounded-xl bg-violet-700 px-7 font-black text-white hover:bg-violet-800" type="submit">بحث</button>
    </form>

    <section class="mt-6 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full min-w-3xl text-right text-sm">
                <thead class="bg-slate-50 text-slate-500"><tr><th class="p-4">المستخدم</th><th class="p-4">النوع</th><th class="p-4">الحالة</th><th class="p-4">القوائم</th><th class="p-4">الإجراءات</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="{{ $user->trashed() ? 'bg-red-50/50' : 'hover:bg-violet-50/40' }}">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-violet-100 font-black text-violet-700">{{ mb_substr($user->name, 0, 1) }}</span>
                                    <span class="min-w-0"><strong class="block truncate">{{ $user->name }}</strong><span class="block truncate text-slate-500">{{ $user->email }}</span></span>
                                </div>
                            </td>
                            <td class="p-4"><span class="rounded-full bg-violet-50 px-3 py-1 font-bold text-violet-700">{{ $user->role->value === 'admin' ? 'أدمن' : 'مستخدم' }}</span></td>
                            <td class="p-4">
                                <span @class([
                                    'rounded-full px-3 py-1 font-bold',
                                    'bg-red-100 text-red-700' => $user->trashed(),
                                    'bg-emerald-100 text-emerald-700' => ! $user->trashed() && $user->status->value === 'active',
                                    'bg-amber-100 text-amber-700' => ! $user->trashed() && $user->status->value === 'suspended',
                                ])>{{ $user->trashed() ? 'محذوف' : ($user->status->value === 'active' ? 'نشط' : 'معلّق') }}</span>
                            </td>
                            <td class="p-4 font-black">{{ number_format($user->saved_wheels_count) }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    @if($user->trashed())
                                        <form method="POST" action="{{ route('admin.users.restore', $user) }}">@csrf @method('PATCH')<button class="rounded-lg bg-emerald-50 px-3 py-2 font-bold text-emerald-700 hover:bg-emerald-100">استعادة</button></form>
                                    @else
                                        <a class="rounded-lg bg-violet-50 px-3 py-2 font-bold text-violet-700 hover:bg-violet-100" href="{{ route('admin.users.edit', $user) }}">تعديل</a>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="حذف هذا المستخدم؟">@csrf @method('DELETE')<button class="rounded-lg bg-red-50 px-3 py-2 font-bold text-red-700 hover:bg-red-100">حذف</button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="p-10 text-center text-slate-500" colspan="5">لا يوجد مستخدمون مطابقون للبحث.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="grid gap-3 p-3 md:hidden">
            @forelse($users as $user)
                <article class="{{ $user->trashed() ? 'border-red-200 bg-red-50/40' : 'border-slate-200 bg-white' }} rounded-2xl border p-4">
                    <div class="flex items-start gap-3">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-violet-100 font-black text-violet-700">{{ mb_substr($user->name, 0, 1) }}</span>
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate font-black">{{ $user->name }}</h2>
                            <p class="truncate text-sm text-slate-500">{{ $user->email }}</p>
                        </div>
                        <span @class([
                            'shrink-0 rounded-full px-2.5 py-1 text-xs font-bold',
                            'bg-red-100 text-red-700' => $user->trashed(),
                            'bg-emerald-100 text-emerald-700' => ! $user->trashed() && $user->status->value === 'active',
                            'bg-amber-100 text-amber-700' => ! $user->trashed() && $user->status->value === 'suspended',
                        ])>{{ $user->trashed() ? 'محذوف' : ($user->status->value === 'active' ? 'نشط' : 'معلّق') }}</span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3 text-sm">
                        <p><span class="block text-xs text-slate-500">الصلاحية</span><strong>{{ $user->role->value === 'admin' ? 'أدمن' : 'مستخدم' }}</strong></p>
                        <p><span class="block text-xs text-slate-500">القوائم</span><strong>{{ number_format($user->saved_wheels_count) }}</strong></p>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        @if($user->trashed())
                            <form class="col-span-2" method="POST" action="{{ route('admin.users.restore', $user) }}">@csrf @method('PATCH')<button class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 font-bold text-white">استعادة الحساب</button></form>
                        @else
                            <a class="rounded-xl bg-violet-700 px-4 py-2.5 text-center font-bold text-white" href="{{ route('admin.users.edit', $user) }}">تعديل</a>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="حذف هذا المستخدم؟">@csrf @method('DELETE')<button class="w-full rounded-xl bg-red-50 px-4 py-2.5 font-bold text-red-700">حذف</button></form>
                        @endif
                    </div>
                </article>
            @empty
                <p class="p-8 text-center text-slate-500">لا يوجد مستخدمون مطابقون للبحث.</p>
            @endforelse
        </div>
    </section>
    <div class="mt-6">{{ $users->links() }}</div>
@endsection
