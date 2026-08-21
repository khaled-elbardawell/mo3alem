@extends('layouts.admin')

@section('title', 'إعدادات الموقع')

@section('content')
    @php
        $links = old('footer_links', $footerLinks);
    @endphp

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm font-black text-violet-600">
                <span class="grid size-8 place-items-center rounded-xl bg-violet-100"><i class="fa-solid fa-sliders"></i></span>
                مركز التحكم بالموقع
            </div>
            <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">إعدادات الموقع</h1>
            <p class="mt-2 max-w-3xl leading-7 text-slate-500">أدِر العناصر العامة التي تظهر لزوار الموقع من مكان واحد، مع معاينة فورية قبل الحفظ.</p>
        </div>
        <a class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-violet-200 bg-white px-4 text-sm font-black text-violet-700 shadow-sm hover:bg-violet-50"
            href="{{ route('home') }}#footer" target="_blank" rel="noopener noreferrer">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            فتح الموقع
        </a>
    </div>

    <div class="mt-7 flex items-center gap-3 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
        <span class="inline-flex min-h-11 shrink-0 items-center gap-2 rounded-xl bg-violet-700 px-4 text-sm font-black text-white">
            <i class="fa-solid fa-share-nodes"></i>
            روابط الفوتر
        </span>
        <span class="shrink-0 px-2 text-xs font-bold text-slate-400">المزيد من أقسام الإعدادات ستظهر هنا عند إضافتها.</span>
    </div>

    <form class="mt-6 grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]" method="POST"
        action="{{ route('admin.settings.footer-links.update') }}" data-footer-links-editor data-max-items="12">
        @csrf
        @method('PUT')

        <section class="min-w-0 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div class="flex items-start gap-3">
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-violet-100 text-violet-700"><i class="fa-solid fa-icons"></i></span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-black">روابط التواصل والفوتر</h2>
                            <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-black text-violet-700">القسم الأول</span>
                        </div>
                        <p class="mt-1 text-sm leading-6 text-slate-500">اختر الأيقونة، أضف الرابط، ثم رتّب العناصر كما تريد أن تظهر للزائر.</p>
                    </div>
                </div>
                <button class="inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-xl bg-violet-700 px-4 text-sm font-black text-white shadow-sm hover:bg-violet-800 disabled:cursor-not-allowed disabled:opacity-50"
                    type="button" data-footer-link-action="add">
                    <i class="fa-solid fa-plus"></i>
                    إضافة رابط
                </button>
            </div>

            <div class="grid gap-4 bg-slate-50/60 p-4 sm:p-6" data-footer-links-rows>
                @foreach ($links as $index => $link)
                    <x-admin.footer-link-row :$link :$index :$platforms />
                @endforeach
            </div>

            <div @class(['p-8 text-center', 'hidden' => count($links) > 0]) data-footer-links-empty>
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 text-xl text-slate-400"><i class="fa-solid fa-link-slash"></i></span>
                <h3 class="mt-3 font-black text-slate-700">لا توجد روابط ظاهرة</h3>
                <p class="mt-1 text-sm text-slate-500">أضف أول رابط ليظهر ضمن أيقونات الفوتر.</p>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <p class="text-sm font-bold text-slate-500"><span data-footer-links-count>{{ count($links) }}</span> من 12 رابطًا</p>
                <button class="inline-flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-xl bg-violet-700 px-6 font-black text-white shadow-[0_12px_25px_rgba(109,40,217,0.2)] hover:bg-violet-800"
                    type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    حفظ التغييرات
                </button>
            </div>
        </section>

        <aside class="grid gap-5 xl:sticky xl:top-24">
            <section class="overflow-hidden rounded-3xl bg-[#111a35] text-white shadow-[0_20px_50px_rgba(15,23,42,0.2)]">
                <div class="border-b border-white/10 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black text-violet-300">معاينة مباشرة</p>
                            <h2 class="mt-1 font-black">أيقونات الفوتر</h2>
                        </div>
                        <span class="rounded-full bg-white/10 px-2.5 py-1 text-xs font-bold"><span data-footer-links-visible-count>0</span> ظاهر</span>
                    </div>
                </div>
                <div class="min-h-44 bg-[radial-gradient(circle_at_top_right,rgba(124,58,237,0.28),transparent_45%)] p-6">
                    <p class="text-sm font-medium leading-6 text-slate-300">ستظهر الروابط الفعّالة بهذا الترتيب في أسفل الموقع.</p>
                    <div class="mt-5 flex flex-wrap gap-2" data-footer-links-preview></div>
                    <p class="mt-5 hidden text-xs font-bold text-slate-400" data-footer-links-preview-empty>فعّل رابطًا واحدًا على الأقل لعرضه هنا.</p>
                </div>
            </section>

            <section class="rounded-3xl border border-amber-200 bg-amber-50 p-5 text-amber-950 shadow-sm">
                <div class="flex items-center gap-2 font-black"><i class="fa-regular fa-lightbulb text-amber-600"></i> نصائح سريعة</div>
                <ul class="mt-3 grid gap-2 text-sm font-medium leading-6 text-amber-900/80">
                    <li>• استخدم اسمًا واضحًا لأنه يُقرأ بواسطة تقنيات الوصول.</li>
                    <li>• افتح روابط الشبكات الخارجية في نافذة جديدة.</li>
                    <li>• يمكنك إخفاء أي رابط مؤقتًا دون حذفه.</li>
                </ul>
            </section>
        </aside>

        <template data-footer-link-template>
            <x-admin.footer-link-row :link="[
                'platform' => 'website',
                'label' => '',
                'url' => '',
                'is_active' => true,
                'open_in_new_tab' => true,
            ]" index="__INDEX__" :$platforms />
        </template>
    </form>
@endsection
