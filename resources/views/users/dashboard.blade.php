@extends('layouts.app')

@section('title', 'مسابقاتي وقوائمي')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black">مسابقاتي وقوائمي</h1>
            <p class="mt-2 text-slate-500">تابع تفاصيل المسابقات ونتائج اللفات، وأدر قوائم الأسماء القابلة لإعادة الاستخدام.</p>
        </div>
        <a class="rounded-xl bg-violet-700 px-5 py-3 font-black text-white hover:bg-violet-800"
            href="{{ route('tools.wheel') }}">مسابقة جديدة</a>
    </div>

    @unless(auth()->user()->hasVerifiedEmail())
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
            يمكنك عرض بياناتك، لكن الإنشاء والتعديل والحذف متاح بعد تفعيل البريد.
            <a class="font-black underline" href="{{ route('verification.notice') }}">تفعيل البريد</a>
        </div>
    @endunless

    <nav class="mt-7 grid grid-cols-2 gap-2 rounded-2xl border border-violet-100 bg-violet-50 p-2"
        aria-label="أقسام حسابي">
        <a @class([
            'inline-flex min-h-12 items-center justify-center gap-2 rounded-xl px-4 font-black transition',
            'bg-white text-violet-700 shadow-sm' => $section === 'competitions',
            'text-slate-600 hover:bg-white/70' => $section !== 'competitions',
        ]) href="{{ route('dashboard', ['section' => 'competitions']) }}">
            <i class="fa-solid fa-trophy" aria-hidden="true"></i>
            مسابقاتي
        </a>
        <a @class([
            'inline-flex min-h-12 items-center justify-center gap-2 rounded-xl px-4 font-black transition',
            'bg-white text-violet-700 shadow-sm' => $section === 'lists',
            'text-slate-600 hover:bg-white/70' => $section !== 'lists',
        ]) href="{{ route('dashboard', ['section' => 'lists']) }}">
            <i class="fa-solid fa-list" aria-hidden="true"></i>
            قوائم الأسماء
        </a>
    </nav>

    <form class="mt-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_220px_auto]"
        method="GET">
        <input type="hidden" name="section" value="{{ $section }}">
        <input class="rounded-xl border border-slate-200 px-4 py-3" type="search" name="search"
            value="{{ $search }}"
            placeholder="{{ $section === 'competitions' ? 'ابحث باسم المسابقة' : 'ابحث باسم القائمة' }}">
        <select class="rounded-xl border border-slate-200 px-4 py-3" name="sort">
            <option value="">آخر تعديل</option>
            <option value="title" @selected($sort === 'title')>الاسم</option>
            <option value="names" @selected($sort === 'names')>عدد الأسماء</option>
            @if($section === 'competitions')
                <option value="results" @selected($sort === 'results')>عدد اللفات</option>
            @endif
            <option value="oldest" @selected($sort === 'oldest')>الأقدم تعديلًا</option>
        </select>
        <button class="rounded-xl bg-slate-900 px-5 py-3 font-bold text-white" type="submit">تطبيق</button>
    </form>

    @if($items->isEmpty())
        <div class="mt-7 rounded-3xl border border-dashed border-violet-200 bg-white p-10 text-center">
            <p class="text-xl font-black">
                {{ $section === 'competitions' ? 'لا توجد مسابقات بعد' : 'لا توجد قوائم أسماء بعد' }}
            </p>
            <p class="mt-2 text-slate-500">
                {{ $section === 'competitions'
                    ? 'ابدأ مسابقة جديدة، ثم اختر قائمة المشاركين.'
                    : 'أنشئ قائمة من الصفحة الرئيسية أو أثناء إنشاء مسابقة.' }}
            </p>
        </div>
    @else
        <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($items as $item)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-violet-200 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="min-w-0 truncate text-lg font-black">
                            <a class="hover:text-violet-700" href="{{ $section === 'competitions'
                                ? route('user.competitions.show', $item)
                                : route('user.saved-wheels.show', $item) }}">
                                {{ $item->title }}
                            </a>
                        </h2>
                        @if($section === 'competitions')
                            <span @class([
                                'shrink-0 rounded-full px-2.5 py-1 text-xs font-black',
                                'bg-emerald-50 text-emerald-700' => $item->status === 'active',
                                'bg-amber-50 text-amber-700' => $item->status !== 'active',
                            ])>
                                {{ $item->status === 'active' ? 'بدأت' : 'مسودة' }}
                            </span>
                        @endif
                    </div>
                    <div class="mt-3 flex flex-wrap justify-between gap-3 text-sm text-slate-500">
                        <span>{{ number_format($item->names_count) }} اسم</span>
                        @if($section === 'competitions')
                            <span>{{ number_format($item->results_count) }} لفة</span>
                        @endif
                        <time datetime="{{ $item->updated_at->toAtomString() }}">{{ $item->updated_at->diffForHumans() }}</time>
                    </div>
                    <div class="mt-5 grid gap-2">
                        <a class="rounded-xl bg-violet-700 px-4 py-2.5 text-center font-bold text-white hover:bg-violet-800"
                            href="{{ $section === 'competitions'
                                ? route('user.competitions.show', $item)
                                : route('user.saved-wheels.show', $item) }}">
                            عرض التفاصيل
                        </a>
                        <a class="rounded-xl border border-violet-200 px-4 py-2.5 text-center font-bold text-violet-700 hover:bg-violet-50"
                            href="{{ $section === 'competitions'
                                ? route('tools.wheel', ['competition' => $item])
                                : route('tools.wheel', ['wheel' => $item]) }}">
                            {{ $section === 'competitions' ? 'متابعة المسابقة' : 'استخدام القائمة' }}
                        </a>
                        @if(auth()->user()->hasVerifiedEmail())
                            <form method="POST"
                                action="{{ $section === 'competitions'
                                    ? route('competitions.destroy', $item)
                                    : route('saved-wheels.destroy', $item) }}"
                                data-confirm="{{ $section === 'competitions'
                                    ? 'حذف هذه المسابقة وسجل نتائجها؟'
                                    : 'حذف هذه القائمة؟' }}">
                                @csrf
                                @method('DELETE')
                                <button class="w-full rounded-xl px-4 py-2.5 font-bold text-red-700 hover:bg-red-50"
                                    type="submit">حذف</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-7">{{ $items->links() }}</div>
    @endif
@endsection
