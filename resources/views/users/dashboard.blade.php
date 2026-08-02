@extends('layouts.app')

@section('title', 'مسابقاتي وقوائمي')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black">مسابقاتي وقوائمي</h1>
            <p class="mt-2 text-slate-500">أدر مسابقاتك وقوائم الأسماء وتصاميم QR المحفوظة من مكان واحد.</p>
        </div>
        <a class="rounded-xl bg-violet-700 px-5 py-3 font-black text-white hover:bg-violet-800"
            href="{{ $section === 'qr' ? route('tools.qr') : route('tools.wheel') }}">
            {{ $section === 'qr' ? 'إنشاء QR جديد' : 'مسابقة جديدة' }}
        </a>
    </div>

    @unless(auth()->user()->hasVerifiedEmail())
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
            يمكنك عرض بياناتك، لكن الإنشاء والتعديل والحذف متاح بعد تفعيل البريد.
            <a class="font-black underline" href="{{ route('verification.notice') }}">تفعيل البريد</a>
        </div>
    @endunless

    <nav class="mt-7 grid grid-cols-3 gap-2 rounded-2xl border border-violet-100 bg-violet-50 p-2"
        aria-label="أقسام حسابي">
        @foreach([
            ['section' => 'competitions', 'label' => 'مسابقاتي', 'icon' => 'fa-trophy'],
            ['section' => 'lists', 'label' => 'قوائم الأسماء', 'icon' => 'fa-list'],
            ['section' => 'qr', 'label' => 'رموز QR', 'icon' => 'fa-qrcode'],
        ] as $tab)
            <a @class([
                'inline-flex min-h-12 items-center justify-center gap-2 rounded-xl px-2 text-center text-sm font-black transition sm:px-4 sm:text-base',
                'bg-white text-violet-700 shadow-sm' => $section === $tab['section'],
                'text-slate-600 hover:bg-white/70' => $section !== $tab['section'],
            ]) href="{{ route('dashboard', ['section' => $tab['section']]) }}">
                <i class="fa-solid {{ $tab['icon'] }}" aria-hidden="true"></i>
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <form class="mt-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_220px_auto]" method="GET">
        <input type="hidden" name="section" value="{{ $section }}">
        <input class="rounded-xl border border-slate-200 px-4 py-3" type="search" name="search" value="{{ $search }}"
            placeholder="{{ $section === 'competitions' ? 'ابحث باسم المسابقة' : ($section === 'lists' ? 'ابحث باسم القائمة' : 'ابحث باسم تصميم QR') }}">
        <select class="rounded-xl border border-slate-200 px-4 py-3" name="sort">
            <option value="">آخر تعديل</option>
            <option value="title" @selected($sort === 'title')>الاسم</option>
            @if($section !== 'qr')
                <option value="names" @selected($sort === 'names')>عدد الأسماء</option>
            @endif
            @if($section === 'competitions')
                <option value="results" @selected($sort === 'results')>عدد اللفات</option>
            @endif
            <option value="oldest" @selected($sort === 'oldest')>الأقدم تعديلًا</option>
        </select>
        <button class="rounded-xl bg-slate-900 px-5 py-3 font-bold text-white" type="submit">تطبيق</button>
    </form>

    @if($items->isEmpty())
        <div class="mt-7 rounded-3xl border border-dashed border-violet-200 bg-white p-10 text-center">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-violet-100 text-xl text-violet-700">
                <i class="fa-solid {{ $section === 'qr' ? 'fa-qrcode' : ($section === 'lists' ? 'fa-list' : 'fa-trophy') }}" aria-hidden="true"></i>
            </span>
            <p class="mt-4 text-xl font-black">
                {{ $section === 'competitions' ? 'لا توجد مسابقات بعد' : ($section === 'lists' ? 'لا توجد قوائم أسماء بعد' : 'لم تحفظ أي رمز QR بعد') }}
            </p>
            <p class="mt-2 text-slate-500">
                {{ $section === 'qr' ? 'أنشئ رمزك الأول ثم اضغط حفظ لإبقائه داخل حسابك.' : 'ابدأ من أدوات معلّم وأنشئ أول عنصر لك.' }}
            </p>
        </div>
    @else
        <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($items as $item)
                @php
                    $primaryUrl = match ($section) {
                        'competitions' => route('user.competitions.show', $item),
                        'lists' => route('user.saved-wheels.show', $item),
                        default => route('tools.qr', ['qr' => $item]),
                    };
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-violet-200 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="min-w-0 truncate text-lg font-black">
                            <a class="hover:text-violet-700" href="{{ $primaryUrl }}">{{ $item->title }}</a>
                        </h2>
                        @if($section === 'competitions')
                            <span @class([
                                'shrink-0 rounded-full px-2.5 py-1 text-xs font-black',
                                'bg-emerald-50 text-emerald-700' => $item->status === 'active',
                                'bg-amber-50 text-amber-700' => $item->status !== 'active',
                            ])>{{ $item->status === 'active' ? 'بدأت' : 'مسودة' }}</span>
                        @elseif($section === 'qr')
                            <span class="shrink-0 rounded-full bg-violet-50 px-2.5 py-1 text-xs font-black text-violet-700">
                                {{ ['url' => 'رابط', 'text' => 'نص', 'wifi' => 'Wi-Fi'][$item->content_type] ?? 'QR' }}
                            </span>
                        @endif
                    </div>
                    <div class="mt-3 flex flex-wrap justify-between gap-3 text-sm text-slate-500">
                        @if($section !== 'qr')
                            <span>{{ number_format($item->names_count) }} اسم</span>
                        @else
                            <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-palette text-violet-500" aria-hidden="true"></i> {{ ['classic' => 'كلاسيكي', 'dots' => 'نقاط', 'rounded' => 'مستديرة'][$item->design['style']] ?? 'مخصص' }}</span>
                        @endif
                        @if($section === 'competitions')
                            <span>{{ number_format($item->results_count) }} لفة</span>
                        @endif
                        <time datetime="{{ $item->updated_at->toAtomString() }}">{{ $item->updated_at->diffForHumans() }}</time>
                    </div>
                    <div class="mt-5 grid gap-2">
                        <a class="rounded-xl bg-violet-700 px-4 py-2.5 text-center font-bold text-white hover:bg-violet-800" href="{{ $primaryUrl }}">
                            {{ $section === 'qr' ? 'فتح وتعديل التصميم' : 'عرض التفاصيل' }}
                        </a>
                        @if($section !== 'qr')
                            <a class="rounded-xl border border-violet-200 px-4 py-2.5 text-center font-bold text-violet-700 hover:bg-violet-50"
                                href="{{ $section === 'competitions' ? route('tools.wheel', ['competition' => $item]) : route('tools.wheel', ['wheel' => $item]) }}">
                                {{ $section === 'competitions' ? 'متابعة المسابقة' : 'استخدام القائمة' }}
                            </a>
                        @endif
                        @if(auth()->user()->hasVerifiedEmail())
                            <form method="POST" action="{{ match ($section) {
                                'competitions' => route('competitions.destroy', $item),
                                'lists' => route('saved-wheels.destroy', $item),
                                default => route('qr-codes.destroy', $item),
                            } }}" data-confirm="هل تريد حذف هذا العنصر؟">
                                @csrf
                                @method('DELETE')
                                <button class="w-full rounded-xl px-4 py-2.5 font-bold text-red-700 hover:bg-red-50" type="submit">حذف</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-7">{{ $items->links() }}</div>
    @endif
@endsection
