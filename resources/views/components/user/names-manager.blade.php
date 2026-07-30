@props([
    'addRoute',
    'clearUrl',
    'description',
    'destroyRouteName',
    'emptyMessage',
    'heading',
    'nameSearch' => '',
    'nameSort' => 'original',
    'names',
    'otherQuery' => [],
    'resource',
    'resourceRouteParameter',
    'totalCount',
])

<section {{ $attributes->merge(['class' => 'rounded-3xl border border-slate-200 bg-white p-5 shadow-sm']) }}>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-xl font-black">{{ $heading }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($nameSearch !== '')
                <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-black text-sky-700">
                    {{ number_format($names->total()) }} مطابق
                </span>
            @endif
            <span class="rounded-full bg-violet-50 px-3 py-1 text-sm font-black text-violet-700">
                {{ number_format($totalCount) }} اسم
            </span>
        </div>
    </div>

    @can('update', $resource)
        <form class="mt-5 grid gap-2 rounded-2xl border border-violet-100 bg-violet-50 p-3 sm:grid-cols-[minmax(0,1fr)_auto]"
            method="POST" action="{{ $addRoute }}">
            @csrf
            <input type="hidden" name="version" value="{{ $resource->version }}">
            <label class="sr-only" for="new-name-{{ $resourceRouteParameter }}">الاسم الجديد</label>
            <input
                class="min-h-11 rounded-xl border border-violet-200 bg-white px-4 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                id="new-name-{{ $resourceRouteParameter }}"
                type="text"
                name="name"
                maxlength="120"
                autocomplete="off"
                placeholder="اكتب الاسم ثم اضغط Enter"
                required>
            <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-violet-700 px-5 font-black text-white hover:bg-violet-800"
                type="submit">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                إضافة اسم
            </button>
        </form>
    @endcan

    <form class="mt-4 grid gap-2 sm:grid-cols-[minmax(0,1fr)_170px_auto_auto]" method="GET">
        @foreach($otherQuery as $key => $value)
            @if(filled($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <label class="relative block">
            <span class="sr-only">البحث في الأسماء</span>
            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-slate-400"
                aria-hidden="true"></i>
            <input
                class="min-h-11 w-full rounded-xl border border-slate-200 bg-white pr-11 pl-4 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                type="search"
                name="names_search"
                value="{{ $nameSearch }}"
                placeholder="ابحث عن اسم…">
        </label>
        <label>
            <span class="sr-only">ترتيب الأسماء</span>
            <select class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3 outline-none focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                name="names_sort">
                <option value="original">الترتيب الأصلي</option>
                <option value="ascending" @selected($nameSort === 'ascending')>أبجدي تصاعدي</option>
                <option value="descending" @selected($nameSort === 'descending')>أبجدي تنازلي</option>
            </select>
        </label>
        <button class="min-h-11 rounded-xl bg-slate-900 px-4 font-black text-white hover:bg-slate-800"
            type="submit">تصفية</button>
        @if($nameSearch !== '' || $nameSort !== 'original')
            <a class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 px-4 font-bold text-slate-600 hover:bg-slate-50"
                href="{{ $clearUrl }}">مسح</a>
        @endif
    </form>

    <div class="mt-4 h-[32rem] overflow-auto overscroll-contain rounded-2xl border border-slate-200">
        <table class="w-full min-w-[34rem] border-separate border-spacing-0 text-right">
            <thead class="sticky top-0 z-10 bg-slate-50 text-xs font-black text-slate-500">
                <tr>
                    <th class="w-20 border-b border-slate-200 px-4 py-3" scope="col">#</th>
                    <th class="border-b border-slate-200 px-4 py-3" scope="col">الاسم</th>
                    @can('update', $resource)
                        <th class="w-24 border-b border-slate-200 px-4 py-3 text-center" scope="col">إجراء</th>
                    @endcan
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($names as $entry)
                    <tr class="group bg-white transition hover:bg-violet-50/60">
                        <td class="border-b border-slate-100 px-4 py-3">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-100 text-xs font-black text-violet-700">
                                {{ number_format($entry['index'] + 1) }}
                            </span>
                        </td>
                        <td class="border-b border-slate-100 px-4 py-3 font-bold">{{ $entry['name'] }}</td>
                        @can('update', $resource)
                            <td class="border-b border-slate-100 px-4 py-3 text-center">
                                <form method="POST"
                                    action="{{ route($destroyRouteName, [
                                        $resourceRouteParameter => $resource,
                                        'nameIndex' => $entry['index'],
                                    ]) }}"
                                    data-confirm="حذف الاسم «{{ $entry['name'] }}»؟">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="version" value="{{ $resource->version }}">
                                    <button class="inline-grid h-9 w-9 place-items-center rounded-lg text-red-600 transition hover:bg-red-50"
                                        type="submit"
                                        aria-label="حذف {{ $entry['name'] }}">
                                        <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td class="h-[26rem] px-6 text-center text-slate-500"
                            colspan="{{ auth()->user()->can('update', $resource) ? 3 : 2 }}">
                            <i class="fa-solid fa-magnifying-glass text-3xl text-slate-300" aria-hidden="true"></i>
                            <p class="mt-3 font-black text-slate-700">{{ $emptyMessage }}</p>
                            @if($nameSearch !== '')
                                <p class="mt-1 text-sm">جرّب كلمة بحث أخرى أو امسح الفلتر.</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-500">
        <p>
            عرض {{ number_format($names->firstItem() ?? 0) }}–{{ number_format($names->lastItem() ?? 0) }}
            من {{ number_format($names->total()) }}
        </p>
        @if($names->hasPages())
            <div>{{ $names->links() }}</div>
        @endif
    </div>
</section>
