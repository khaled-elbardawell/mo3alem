@extends('layouts.admin')

@section('title', 'ربط API')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-black text-violet-600">التكاملات الآمنة</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">عملاء API</h1>
            <p class="mt-2 leading-7 text-slate-500">توكن مستقل لكل موقع، بصلاحية إنشاء المستخدمين فقط وإمكانية الإلغاء الفوري.</p>
        </div>
        <a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white shadow-[0_10px_24px_rgba(109,40,217,0.2)] hover:bg-violet-800" href="{{ route('admin.api-clients.create') }}">
            <i class="fa-solid fa-plus"></i> إضافة موقع
        </a>
    </div>

    @if(session('plain_api_token'))
        <section class="mt-6 rounded-3xl border border-amber-300 bg-amber-50 p-5 shadow-sm" role="alert">
            <div class="flex items-start gap-3">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-amber-200 text-amber-800"><i class="fa-solid fa-key"></i></span>
                <div class="min-w-0 flex-1">
                    <h2 class="font-black text-amber-950">توكن {{ session('api_client_name') }} — يظهر مرة واحدة فقط</h2>
                    <p class="mt-1 text-sm font-bold leading-6 text-amber-800">انسخه الآن واحفظه في Secret Manager أو ملف البيئة على خادم الموقع المرتبط. لا تضعه داخل JavaScript.</p>
                    <input class="mt-3 min-h-12 w-full rounded-xl border border-amber-300 bg-white px-4 font-mono text-sm text-slate-900 outline-none" dir="ltr" readonly value="{{ session('plain_api_token') }}" aria-label="توكن API الجديد">
                </div>
            </div>
        </section>
    @endif

    <section class="mt-6 rounded-3xl border border-violet-100 bg-violet-50/60 p-5">
        <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <h2 class="font-black text-violet-950">مسار إنشاء الحسابات</h2>
                <p class="mt-1 text-sm font-bold leading-6 text-violet-700">أرسل الطلب من خادم الموقع فقط مع Authorization: Bearer. عند إنشاء الحساب تُعاد temporary_password مرة واحدة، ولا تُرسل أي رسالة بريد.</p>
            </div>
            <code class="rounded-xl bg-slate-950 px-4 py-3 text-left text-sm font-bold text-emerald-300" dir="ltr">POST {{ url('/api/v1/users') }}</code>
        </div>
    </section>

    <div class="mt-6 grid gap-4">
        @forelse($apiClients as $apiClient)
            @php($latestToken = $apiClient->latestToken)
            <article class="grid gap-4 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm xl:grid-cols-[1fr_auto] xl:items-center">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-black">{{ $apiClient->name }}</h2>
                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $apiClient->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $apiClient->is_active ? 'فعّال' : 'معطّل' }}</span>
                        <span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-bold text-violet-700">users:create</span>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2 text-sm font-bold text-slate-600">
                        <span class="rounded-lg bg-slate-100 px-3 py-1.5">{{ $apiClient->external_user_links_count }} حساب</span>
                        <span class="rounded-lg bg-slate-100 px-3 py-1.5">{{ $apiClient->tokens_count }} توكن فعّال</span>
                        <span class="rounded-lg bg-slate-100 px-3 py-1.5">صلاحية {{ $apiClient->token_expiration_days }} يومًا</span>
                        <span class="rounded-lg bg-slate-100 px-3 py-1.5">IP: {{ empty($apiClient->allowed_ips) ? 'غير مقيّد' : implode('، ', $apiClient->allowed_ips) }}</span>
                    </div>
                    <p class="mt-3 text-xs font-bold text-slate-500">
                        @if($latestToken)
                            آخر استخدام: {{ $latestToken->last_used_at?->diffForHumans() ?? 'لم يُستخدم' }} · ينتهي: {{ $latestToken->expires_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                        @else
                            لا يوجد توكن فعال لهذا الموقع.
                        @endif
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:flex">
                    <a class="inline-flex min-h-11 items-center justify-center rounded-xl bg-violet-50 px-4 font-bold text-violet-700 hover:bg-violet-100" href="{{ route('admin.api-clients.edit', $apiClient) }}">إعدادات</a>
                    <form method="POST" action="{{ route('admin.api-clients.token.rotate', $apiClient) }}" data-confirm="سيتم إلغاء أي توكن سابق فورًا. هل تريد المتابعة؟">@csrf<button class="min-h-11 w-full rounded-xl bg-emerald-600 px-4 font-bold text-white hover:bg-emerald-700" type="submit">{{ $apiClient->tokens_count ? 'تدوير التوكن' : 'توليد توكن' }}</button></form>
                    @if($apiClient->tokens_count)
                        <form class="col-span-2" method="POST" action="{{ route('admin.api-clients.tokens.revoke', $apiClient) }}" data-confirm="إلغاء جميع توكنات هذا الموقع الآن؟">@csrf @method('DELETE')<button class="min-h-11 w-full rounded-xl bg-red-50 px-4 font-bold text-red-700 hover:bg-red-100" type="submit">إلغاء التوكن</button></form>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-violet-200 bg-white p-10 text-center text-slate-500">
                <i class="fa-solid fa-shield-halved text-4xl text-violet-300"></i>
                <p class="mt-3 font-bold">لا توجد مواقع مرتبطة بعد.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $apiClients->links() }}</div>
@endsection
