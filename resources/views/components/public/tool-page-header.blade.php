@props([
    'title',
    'description',
    'current',
    'icon',
])

<section {{ $attributes->class(['border-b border-violet-100/80 bg-[#fbfaff]']) }} data-tool-page-header>
    <div class="mx-auto grid w-[min(calc(100%_-_2rem),1760px)] grid-cols-[minmax(0,1fr)_auto] items-center gap-4 py-4 sm:gap-8">
        <div class="min-w-0">
            <nav aria-label="مسار التنقل">
                <ol class="flex flex-wrap items-center gap-2 text-xs font-bold sm:text-sm">
                    <li>
                        <a class="text-slate-400 transition-colors hover:text-violet-700" href="{{ route('home') }}">الرئيسية</a>
                    </li>
                    <li class="text-slate-300" aria-hidden="true">
                        <i class="fa-solid fa-chevron-left text-[9px]"></i>
                    </li>
                    <li>
                        <a class="text-slate-400 transition-colors hover:text-violet-700" href="{{ route('home') }}#tools">الأدوات</a>
                    </li>
                    <li class="text-slate-300" aria-hidden="true">
                        <i class="fa-solid fa-chevron-left text-[9px]"></i>
                    </li>
                    <li class="text-violet-700" aria-current="page">{{ $current }}</li>
                </ol>
            </nav>

            <h1 class="mt-2 text-[clamp(1.65rem,2.4vw,2.35rem)] leading-tight font-black tracking-tight text-[#111a35]">
                {{ $title }}
            </h1>
            <p class="mt-1 max-w-4xl text-sm leading-6 font-medium text-slate-500 sm:text-base">
                {{ $description }}
            </p>
        </div>

        <span class="grid size-13 shrink-0 place-items-center rounded-2xl bg-violet-100 text-xl text-violet-700 sm:size-18 sm:rounded-3xl sm:text-2xl"
            aria-hidden="true">
            <i class="fa-solid {{ $icon }}"></i>
        </span>
    </div>
</section>
