@extends('layouts.app')

@section('title', $competition->title)

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a class="inline-flex items-center gap-2 font-bold text-violet-700 hover:text-violet-900"
                href="{{ route('dashboard', ['section' => 'competitions']) }}">
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                العودة إلى مسابقاتي
            </a>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-black">{{ $competition->title }}</h1>
                <span @class([
                    'rounded-full px-3 py-1 text-sm font-black',
                    'bg-emerald-50 text-emerald-700' => $competition->status === 'active',
                    'bg-amber-50 text-amber-700' => $competition->status !== 'active',
                ])>
                    {{ $competition->status === 'active' ? 'مسابقة بدأت' : 'مسودة' }}
                </span>
            </div>
            <p class="mt-2 text-slate-500">تفاصيل المشاركين وسجل الفائزين مرتبًا حسب اللفة.</p>
        </div>
        <a class="inline-flex min-h-12 items-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white hover:bg-violet-800"
            href="{{ route('tools.wheel', ['competition' => $competition]) }}">
            <i class="fa-solid fa-play" aria-hidden="true"></i>
            متابعة المسابقة
        </a>
    </div>

    <section class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" aria-label="ملخص المسابقة">
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-sm font-bold text-slate-500">الأسماء الحالية</p>
            <p class="mt-2 text-2xl font-black">{{ number_format($competition->names_count) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-sm font-bold text-slate-500">اللفات المسجلة</p>
            <p class="mt-2 text-2xl font-black">{{ number_format($competition->results_count) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-sm font-bold text-slate-500">إجمالي المشاركات</p>
            <p class="mt-2 text-2xl font-black">{{ number_format($competition->names_count + $competition->results_count) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-sm font-bold text-slate-500">آخر تعديل</p>
            <time class="mt-2 block font-black" datetime="{{ $competition->updated_at->toAtomString() }}">
                {{ $competition->updated_at->locale('ar')->translatedFormat('j F Y، g:i A') }}
            </time>
        </div>
    </section>

    @if($competition->savedWheel)
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-violet-100 bg-violet-50 p-4">
            <div>
                <p class="text-sm font-bold text-violet-600">قائمة الأسماء المصدر</p>
                <p class="mt-1 font-black">{{ $competition->savedWheel->title }}</p>
            </div>
            <a class="rounded-xl bg-white px-4 py-2 font-black text-violet-700 shadow-sm hover:bg-violet-100"
                href="{{ route('user.saved-wheels.show', $competition->savedWheel) }}">
                عرض القائمة
            </a>
        </div>
    @endif

    <div class="mt-7 grid items-start gap-6 xl:grid-cols-2">
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black">سجل الفائزين حسب اللفة</h2>
                    <p class="mt-1 text-sm text-slate-500">ابحث باسم الفائز أو انتقل مباشرة إلى رقم لفة محدد.</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($resultSearch !== '' || $resultRound)
                        <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-black text-sky-700">
                            {{ number_format($results->total()) }} مطابق
                        </span>
                    @endif
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-sm font-black text-amber-700">
                        {{ number_format($competition->results_count) }} نتيجة
                    </span>
                </div>
            </div>

            <form class="mt-5 grid gap-2 sm:grid-cols-[minmax(0,1fr)_130px_auto_auto]" method="GET">
                @if($nameSearch !== '')
                    <input type="hidden" name="names_search" value="{{ $nameSearch }}">
                @endif
                @if($nameSort !== 'original')
                    <input type="hidden" name="names_sort" value="{{ $nameSort }}">
                @endif
                <label class="relative block">
                    <span class="sr-only">البحث باسم الفائز</span>
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-slate-400"
                        aria-hidden="true"></i>
                    <input
                        class="min-h-11 w-full rounded-xl border border-slate-200 pr-11 pl-4 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                        type="search"
                        name="results_search"
                        value="{{ $resultSearch }}"
                        placeholder="اسم الفائز…">
                </label>
                <label>
                    <span class="sr-only">رقم اللفة</span>
                    <input
                        class="min-h-11 w-full rounded-xl border border-slate-200 px-3 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                        type="number"
                        name="results_round"
                        value="{{ $resultRound }}"
                        min="1"
                        placeholder="رقم اللفة">
                </label>
                <button class="min-h-11 rounded-xl bg-slate-900 px-4 font-black text-white hover:bg-slate-800"
                    type="submit">تصفية</button>
                @if($resultSearch !== '' || $resultRound)
                    <a class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 px-4 font-bold text-slate-600 hover:bg-slate-50"
                        href="{{ route('user.competitions.show', [
                            'competition' => $competition,
                            'names_search' => $nameSearch ?: null,
                            'names_sort' => $nameSort !== 'original' ? $nameSort : null,
                        ]) }}">مسح</a>
                @endif
            </form>

            <div class="mt-4 h-[32rem] overflow-auto overscroll-contain rounded-2xl border border-slate-200">
                <table class="w-full min-w-[42rem] border-separate border-spacing-0 text-right">
                    <thead class="sticky top-0 z-10 bg-slate-50 text-xs font-black text-slate-500">
                        <tr>
                            <th class="w-24 border-b border-slate-200 px-4 py-3" scope="col">اللفة</th>
                            <th class="border-b border-slate-200 px-4 py-3" scope="col">الفائز</th>
                            <th class="w-32 border-b border-slate-200 px-4 py-3" scope="col">ترتيب الاسم</th>
                            <th class="w-52 border-b border-slate-200 px-4 py-3" scope="col">وقت النتيجة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                            <tr class="bg-white transition hover:bg-amber-50/60">
                                <td class="border-b border-slate-100 px-4 py-3">
                                    <span class="inline-grid h-9 min-w-9 place-items-center rounded-lg bg-amber-50 px-2 font-black text-amber-700">
                                        {{ number_format($result['round']) }}
                                    </span>
                                </td>
                                <td class="border-b border-slate-100 px-4 py-3 font-black">{{ $result['name'] }}</td>
                                <td class="border-b border-slate-100 px-4 py-3 text-slate-600">
                                    {{ $result['position'] ? number_format($result['position']) : '—' }}
                                </td>
                                <td class="border-b border-slate-100 px-4 py-3 text-sm text-slate-500">
                                    <time datetime="{{ $result['won_at']->toAtomString() }}">
                                        {{ $result['won_at']->locale('ar')->translatedFormat('j F Y، g:i A') }}
                                    </time>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="h-[26rem] px-6 text-center" colspan="4">
                                    <i class="fa-solid fa-trophy text-3xl text-slate-300" aria-hidden="true"></i>
                                    <p class="mt-3 font-black text-slate-700">
                                        {{ $competition->results_count === 0 ? 'لا توجد نتائج مسجلة بعد' : 'لا توجد نتائج تطابق الفلاتر' }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $competition->results_count === 0
                                            ? 'ستظهر هنا النتائج بعد بدء المسابقة.'
                                            : 'جرّب اسمًا آخر أو غيّر رقم اللفة.' }}
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-500">
                <p>
                    عرض {{ number_format($results->firstItem() ?? 0) }}–{{ number_format($results->lastItem() ?? 0) }}
                    من {{ number_format($results->total()) }}
                </p>
                @if($results->hasPages())
                    <div>{{ $results->links() }}</div>
                @endif
            </div>
        </section>

        <x-user.names-manager
            add-route="{{ route('user.competitions.names.store', $competition) }}"
            :clear-url="route('user.competitions.show', [
                'competition' => $competition,
                'results_search' => $resultSearch ?: null,
                'results_round' => $resultRound,
            ])"
            description="الأسماء المتبقية والمتاحة لللفات القادمة."
            destroy-route-name="user.competitions.names.destroy"
            empty-message="{{ $competition->names_count === 0 ? 'لا توجد أسماء حالية في المسابقة' : 'لا توجد أسماء تطابق البحث' }}"
            heading="الأسماء الحالية"
            :name-search="$nameSearch"
            :name-sort="$nameSort"
            :names="$names"
            :other-query="[
                'results_search' => $resultSearch,
                'results_round' => $resultRound,
            ]"
            :resource="$competition"
            resource-route-parameter="competition"
            :total-count="$competition->names_count" />
    </div>
@endsection
