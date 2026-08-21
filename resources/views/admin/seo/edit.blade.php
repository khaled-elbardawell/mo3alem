@extends('layouts.admin')

@section('title', 'إدارة SEO')

@section('content')
    @php
        $canonicalPreview = old('canonical_url', $seo->canonical_url) ?: route($selectedPage->routeName());
        $socialImage = $seo->og_image_path ? Storage::disk('public')->url($seo->og_image_path) : null;
    @endphp

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm font-black text-violet-600">
                <span class="grid size-8 place-items-center rounded-xl bg-violet-100"><i class="fa-solid fa-magnifying-glass-chart"></i></span>
                ظهور الموقع
            </div>
            <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">مركز إدارة SEO</h1>
            <p class="mt-2 max-w-3xl leading-7 text-slate-500">اضبط ظهور كل صفحة في Google وعند مشاركة روابطها، وتحكم بالفهرسة وخريطة الموقع من مكان واحد.</p>
        </div>
        <a class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-violet-200 bg-white px-4 text-sm font-black text-violet-700 shadow-sm hover:bg-violet-50"
            href="{{ route($selectedPage->routeName()) }}" target="_blank" rel="noopener">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            فتح الصفحة
        </a>
    </div>

    <nav class="mt-7 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="صفحات إعدادات SEO">
        @foreach ($pages as $item)
            @php
                $page = $item['page'];
                $setting = $item['setting'];
                $isSelected = $page === $selectedPage;
            @endphp
            <a @class([
                'group relative overflow-hidden rounded-2xl border p-4 transition-[border,background,box-shadow,transform]',
                'border-violet-500 bg-violet-700 text-white shadow-[0_16px_35px_rgba(109,40,217,0.2)]' => $isSelected,
                'border-slate-200 bg-white hover:-translate-y-0.5 hover:border-violet-300 hover:shadow-lg' => ! $isSelected,
            ]) href="{{ route('admin.seo.edit', ['page' => $page->value]) }}" @if ($isSelected) aria-current="page" @endif>
                <div class="flex items-start justify-between gap-3">
                    <span @class([
                        'grid size-11 shrink-0 place-items-center rounded-xl text-lg',
                        'bg-white/15 text-white' => $isSelected,
                        'bg-violet-100 text-violet-700' => ! $isSelected,
                    ])><i class="fa-solid {{ $page->icon() }}"></i></span>
                    <span @class([
                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-black',
                        'bg-emerald-400/20 text-emerald-50' => $isSelected && $setting->allow_indexing,
                        'bg-white/15 text-violet-50' => $isSelected && ! $setting->allow_indexing,
                        'bg-emerald-50 text-emerald-700' => ! $isSelected && $setting->allow_indexing,
                        'bg-slate-100 text-slate-500' => ! $isSelected && ! $setting->allow_indexing,
                    ])>
                        <span class="size-1.5 rounded-full bg-current"></span>
                        {{ $setting->allow_indexing ? 'مفهرسة' : 'غير مفهرسة' }}
                    </span>
                </div>
                <h2 class="mt-4 font-black">{{ $page->label() }}</h2>
                <p @class(['mt-1 line-clamp-2 text-xs leading-5', 'text-violet-100' => $isSelected, 'text-slate-500' => ! $isSelected])>{{ $page->description() }}</p>
            </a>
        @endforeach
    </nav>

    <form class="mt-6 grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]" method="POST" enctype="multipart/form-data"
        action="{{ route('admin.seo.update', $selectedPage->value) }}" data-seo-form>
        @csrf
        @method('PUT')

        <div class="grid min-w-0 gap-6">
            <section class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-start gap-3 border-b border-slate-100 p-5 sm:p-6">
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-violet-100 text-violet-700"><i class="fa-brands fa-google"></i></span>
                    <div>
                        <h2 class="text-lg font-black">نتيجة البحث</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-500">العنوان والوصف الأساسيان اللذان قد يظهران في نتائج محركات البحث.</p>
                    </div>
                </div>

                <div class="grid gap-5 p-5 sm:p-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm font-bold text-slate-700" for="site_name">
                            اسم الموقع
                            <input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                                id="site_name" name="site_name" value="{{ old('site_name', $seo->site_name) }}" maxlength="120" required>
                        </label>
                        <label class="grid gap-2 text-sm font-bold text-slate-700" for="title">
                            <span class="flex items-center justify-between gap-3"><span>عنوان SEO</span><span class="text-xs font-medium text-slate-400" data-count-for="title"></span></span>
                            <input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                                id="title" name="title" value="{{ old('title', $seo->title) }}" maxlength="180" required data-seo-title>
                            <span class="text-xs font-medium text-slate-400">يفضل أن يكون واضحاً وفي حدود 50–60 حرفاً.</span>
                        </label>
                    </div>

                    <label class="grid gap-2 text-sm font-bold text-slate-700" for="description">
                        <span class="flex items-center justify-between gap-3"><span>وصف الصفحة</span><span class="text-xs font-medium text-slate-400" data-count-for="description"></span></span>
                        <textarea class="min-h-32 resize-y rounded-xl border border-slate-200 px-4 py-3 leading-7 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                            id="description" name="description" maxlength="500" data-seo-description>{{ old('description', $seo->description) }}</textarea>
                        <span class="text-xs font-medium text-slate-400">وصف جذاب بين 140–160 حرفاً يساعد على رفع معدل النقر.</span>
                    </label>

                    <label class="grid gap-2 text-sm font-bold text-slate-700" for="keywords">
                        الكلمات المفتاحية
                        <input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                            id="keywords" name="keywords" value="{{ old('keywords', $seo->keywords) }}" maxlength="500" placeholder="افصل الكلمات بفاصلة">
                    </label>

                    <label class="grid gap-2 text-sm font-bold text-slate-700" for="canonical_url">
                        <span class="flex items-center gap-2">الرابط الأساسي <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-500">Canonical</span></span>
                        <input class="min-h-12 rounded-xl border border-slate-200 px-4 text-left outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                            dir="ltr" type="url" id="canonical_url" name="canonical_url" value="{{ old('canonical_url', $seo->canonical_url) }}" maxlength="2048" data-seo-url
                            placeholder="{{ route($selectedPage->routeName()) }}">
                        <span class="text-xs font-medium text-slate-400">اتركه فارغاً لاستخدام رابط الصفحة الحالي تلقائياً.</span>
                    </label>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-start gap-3 border-b border-slate-100 p-5 sm:p-6">
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-sky-100 text-sky-700"><i class="fa-solid fa-share-nodes"></i></span>
                    <div>
                        <h2 class="text-lg font-black">المشاركة الاجتماعية</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-500">خصص بطاقة Open Graph وX لهذه الصفحة، أو اترك النصوص فارغة لاستخدام بيانات SEO الأساسية.</p>
                    </div>
                </div>

                <div class="grid gap-5 p-5 sm:p-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm font-bold text-slate-700" for="og_title">
                            عنوان المشاركة
                            <input class="min-h-12 rounded-xl border border-slate-200 px-4 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                                id="og_title" name="og_title" value="{{ old('og_title', $seo->og_title) }}" maxlength="180" data-social-title placeholder="يستخدم عنوان SEO عند تركه فارغاً">
                        </label>
                        <label class="grid gap-2 text-sm font-bold text-slate-700" for="twitter_card">
                            نوع بطاقة X
                            <select class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" id="twitter_card" name="twitter_card">
                                <option value="summary" @selected(old('twitter_card', $seo->twitter_card) === 'summary')>بطاقة مختصرة</option>
                                <option value="summary_large_image" @selected(old('twitter_card', $seo->twitter_card) === 'summary_large_image')>بطاقة بصورة كبيرة</option>
                            </select>
                        </label>
                    </div>

                    <label class="grid gap-2 text-sm font-bold text-slate-700" for="og_description">
                        وصف المشاركة
                        <textarea class="min-h-28 resize-y rounded-xl border border-slate-200 px-4 py-3 leading-7 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                            id="og_description" name="og_description" maxlength="500" data-social-description placeholder="يستخدم وصف SEO عند تركه فارغاً">{{ old('og_description', $seo->og_description) }}</textarea>
                    </label>

                    <div class="grid gap-5 rounded-2xl border border-dashed border-violet-200 bg-violet-50/50 p-4 sm:grid-cols-[minmax(0,1fr)_220px]">
                        <div class="grid content-start gap-4">
                            <label class="grid gap-2 text-sm font-bold text-slate-700" for="og_image">
                                صورة المشاركة
                                <input class="min-w-0 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm file:ms-3 file:rounded-lg file:border-0 file:bg-violet-100 file:px-3 file:py-2 file:font-bold file:text-violet-800"
                                    type="file" id="og_image" name="og_image" accept=".jpg,.jpeg,.png,.webp" data-preview-target="seoImagePreview">
                                <span class="text-xs font-medium text-slate-500">المقاس المقترح 1200×630 بكسل، حتى 5MB.</span>
                            </label>
                            <label class="grid gap-2 text-sm font-bold text-slate-700" for="og_image_alt">
                                النص البديل للصورة
                                <input class="min-h-11 rounded-xl border border-slate-200 bg-white px-4 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100"
                                    id="og_image_alt" name="og_image_alt" value="{{ old('og_image_alt', $seo->og_image_alt) }}" maxlength="180">
                            </label>
                            <input type="hidden" name="remove_og_image" value="0">
                            @if ($socialImage)
                                <label class="flex items-center gap-2 text-sm font-bold text-red-700">
                                    <input class="size-4 accent-red-600" type="checkbox" name="remove_og_image" value="1">
                                    حذف الصورة الحالية عند الحفظ
                                </label>
                            @endif
                        </div>
                        <div class="grid min-h-36 place-items-center overflow-hidden rounded-2xl border border-white bg-white shadow-sm">
                            <img @class(['aspect-[1.91/1] h-full w-full object-cover', 'hidden' => ! $socialImage]) id="seoImagePreview" src="{{ $socialImage }}" alt="معاينة صورة المشاركة">
                            <div @class(['p-5 text-center text-slate-400', 'hidden' => $socialImage]) data-empty-image>
                                <i class="fa-regular fa-image text-3xl"></i>
                                <p class="mt-2 text-xs font-bold">لا توجد صورة مخصصة</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-start gap-3 border-b border-slate-100 p-5 sm:p-6">
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-emerald-100 text-emerald-700"><i class="fa-solid fa-spider"></i></span>
                    <div>
                        <h2 class="text-lg font-black">الزحف وخريطة الموقع</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-500">تحكم بكيفية تعامل محركات البحث مع هذه الصفحة تحديداً.</p>
                    </div>
                </div>

                <div class="grid gap-4 p-5 sm:p-6">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                            <input type="hidden" name="allow_indexing" value="0">
                            <input class="mt-0.5 size-5 shrink-0 accent-violet-700" type="checkbox" name="allow_indexing" value="1" @checked(old('allow_indexing', $seo->allow_indexing))>
                            <span><span class="block font-black text-emerald-950">السماح بالفهرسة</span><span class="mt-1 block text-xs font-medium leading-5 text-emerald-800">يسمح بإظهار الصفحة في نتائج البحث.</span></span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-sky-200 bg-sky-50 p-4">
                            <input type="hidden" name="allow_following" value="0">
                            <input class="mt-0.5 size-5 shrink-0 accent-violet-700" type="checkbox" name="allow_following" value="1" @checked(old('allow_following', $seo->allow_following))>
                            <span><span class="block font-black text-sky-950">السماح بتتبع الروابط</span><span class="mt-1 block text-xs font-medium leading-5 text-sky-800">يسمح للروبوتات بتتبع روابط الصفحة.</span></span>
                        </label>
                    </div>

                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-violet-200 bg-violet-50 p-4">
                        <input type="hidden" name="include_in_sitemap" value="0">
                        <input class="mt-0.5 size-5 shrink-0 accent-violet-700" type="checkbox" name="include_in_sitemap" value="1" @checked(old('include_in_sitemap', $seo->include_in_sitemap))>
                        <span><span class="block font-black text-violet-950">إظهار الصفحة في sitemap.xml</span><span class="mt-1 block text-xs font-medium leading-5 text-violet-800">ستُستبعد تلقائياً أيضاً عندما تكون الفهرسة معطلة.</span></span>
                    </label>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm font-bold text-slate-700" for="sitemap_change_frequency">
                            معدل تحديث الصفحة
                            <select class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" id="sitemap_change_frequency" name="sitemap_change_frequency">
                                @foreach (['always' => 'دائماً', 'hourly' => 'كل ساعة', 'daily' => 'يومياً', 'weekly' => 'أسبوعياً', 'monthly' => 'شهرياً', 'yearly' => 'سنوياً', 'never' => 'نادراً'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('sitemap_change_frequency', $seo->sitemap_change_frequency) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="grid gap-2 text-sm font-bold text-slate-700" for="sitemap_priority">
                            أولوية الصفحة
                            <select class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" id="sitemap_priority" name="sitemap_priority">
                                @foreach ([1.0, 0.9, 0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0.2, 0.1, 0.0] as $priority)
                                    <option value="{{ number_format($priority, 1) }}" @selected((string) old('sitemap_priority', number_format($seo->sitemap_priority, 1)) === number_format($priority, 1))>{{ number_format($priority, 1) }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </section>
        </div>

        <aside class="grid gap-4 xl:sticky xl:top-24">
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    <h2 class="font-black">معاينة Google</h2>
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-700">مباشرة</span>
                </div>
                <div class="p-5" dir="rtl">
                    <div class="flex items-center gap-2">
                        <span class="grid size-7 place-items-center rounded-full bg-slate-100 text-xs text-violet-700"><i class="fa-solid fa-globe"></i></span>
                        <div class="min-w-0 text-xs">
                            <p class="font-bold text-slate-700" data-preview-site>{{ old('site_name', $seo->site_name) }}</p>
                            <p class="truncate text-left text-slate-500" dir="ltr" data-preview-url>{{ $canonicalPreview }}</p>
                        </div>
                    </div>
                    <p class="mt-3 line-clamp-2 text-xl font-medium leading-7 text-[#1a0dab]" data-preview-title>{{ old('title', $seo->title) }}</p>
                    <p class="mt-1 line-clamp-3 text-sm leading-6 text-slate-600" data-preview-description>{{ old('description', $seo->description) ?: 'أضف وصفاً واضحاً ومقنعاً لهذه الصفحة.' }}</p>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-black">معاينة المشاركة</h2>
                </div>
                <div class="overflow-hidden bg-slate-100">
                    <img @class(['aspect-[1.91/1] w-full object-cover', 'hidden' => ! $socialImage]) src="{{ $socialImage }}" alt="صورة بطاقة المشاركة" data-social-image>
                    <div @class(['grid aspect-[1.91/1] place-items-center bg-linear-to-br from-violet-100 to-indigo-50 text-violet-300', 'hidden' => $socialImage]) data-social-placeholder><i class="fa-regular fa-image text-5xl"></i></div>
                </div>
                <div class="border-t border-slate-200 p-4">
                    <p class="truncate text-[11px] font-bold uppercase tracking-wide text-slate-400" data-social-host>{{ parse_url($canonicalPreview, PHP_URL_HOST) }}</p>
                    <p class="mt-1 line-clamp-2 font-black leading-6 text-slate-900" data-preview-social-title>{{ old('og_title', $seo->og_title) ?: old('title', $seo->title) }}</p>
                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500" data-preview-social-description>{{ old('og_description', $seo->og_description) ?: old('description', $seo->description) }}</p>
                </div>
            </section>

            <div class="rounded-3xl bg-slate-950 p-5 text-white shadow-[0_18px_45px_rgba(15,23,42,0.2)]">
                <div class="flex items-start gap-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/10 text-violet-300"><i class="fa-solid fa-floppy-disk"></i></span>
                    <div>
                        <p class="font-black">{{ $selectedPage->label() }}</p>
                        <p class="mt-1 text-xs leading-5 text-slate-300">ستُطبق التغييرات على هذه الصفحة فقط.</p>
                    </div>
                </div>
                <button class="mt-4 min-h-12 w-full rounded-xl bg-violet-600 px-5 font-black text-white transition hover:bg-violet-500 focus:ring-4 focus:ring-violet-400/30" type="submit">
                    حفظ إعدادات الصفحة
                </button>
            </div>
        </aside>
    </form>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('[data-seo-form]');

            if (!form) return;

            const fields = {
                site: form.querySelector('#site_name'),
                title: form.querySelector('[data-seo-title]'),
                description: form.querySelector('[data-seo-description]'),
                url: form.querySelector('[data-seo-url]'),
                socialTitle: form.querySelector('[data-social-title]'),
                socialDescription: form.querySelector('[data-social-description]'),
                image: form.querySelector('#og_image'),
            };
            const fallbackUrl = @json(route($selectedPage->routeName()));

            const setText = (selector, value) => {
                const element = form.querySelector(selector);
                if (element) element.textContent = value;
            };

            const updatePreview = () => {
                const title = fields.title.value.trim();
                const description = fields.description.value.trim();
                const url = fields.url.value.trim() || fallbackUrl;
                const socialTitle = fields.socialTitle.value.trim() || title;
                const socialDescription = fields.socialDescription.value.trim() || description;

                setText('[data-preview-site]', fields.site.value.trim() || 'اسم الموقع');
                setText('[data-preview-title]', title || 'عنوان الصفحة');
                setText('[data-preview-description]', description || 'أضف وصفاً واضحاً ومقنعاً لهذه الصفحة.');
                setText('[data-preview-url]', url);
                setText('[data-preview-social-title]', socialTitle || 'عنوان المشاركة');
                setText('[data-preview-social-description]', socialDescription || 'وصف بطاقة المشاركة');

                try {
                    setText('[data-social-host]', new URL(url).host);
                } catch {
                    setText('[data-social-host]', url);
                }

                ['title', 'description'].forEach((name) => {
                    const counter = form.querySelector(`[data-count-for="${name}"]`);
                    if (counter) counter.textContent = `${fields[name].value.length} حرف`;
                });
            };

            Object.values(fields).filter((field) => field && field !== fields.image).forEach((field) => {
                field.addEventListener('input', updatePreview);
            });

            fields.image?.addEventListener('change', () => {
                const file = fields.image.files?.[0];
                if (!file) return;

                const imageUrl = URL.createObjectURL(file);
                const socialImage = form.querySelector('[data-social-image]');
                const placeholder = form.querySelector('[data-social-placeholder]');
                const emptyImage = form.querySelector('[data-empty-image]');

                if (socialImage) {
                    socialImage.src = imageUrl;
                    socialImage.classList.remove('hidden');
                }
                placeholder?.classList.add('hidden');
                emptyImage?.classList.add('hidden');
            });

            updatePreview();
        })();
    </script>
@endpush
