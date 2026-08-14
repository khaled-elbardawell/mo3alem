<div class="mt-6 grid gap-3">
    <a class="inline-flex min-h-12 items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-800 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-violet-100"
        href="{{ route('social.redirect', ['provider' => 'google']) }}">
        <svg class="size-5 shrink-0" aria-hidden="true" viewBox="0 0 24 24">
            <path fill="#4285f4" d="M21.6 12.23c0-.71-.06-1.4-.18-2.07H12v3.92h5.38a4.6 4.6 0 0 1-2 3.02v2.54h3.24c1.9-1.74 2.98-4.31 2.98-7.41Z" />
            <path fill="#34a853" d="M12 22c2.7 0 4.97-.9 6.62-2.43l-3.24-2.54c-.9.6-2.05.96-3.38.96-2.61 0-4.82-1.76-5.61-4.13H3.04v2.62A10 10 0 0 0 12 22Z" />
            <path fill="#fbbc05" d="M6.39 13.86A6 6 0 0 1 6.08 12c0-.65.11-1.28.31-1.86V7.52H3.04A10 10 0 0 0 2 12c0 1.61.39 3.14 1.04 4.48l3.35-2.62Z" />
            <path fill="#ea4335" d="M12 6.01c1.47 0 2.79.51 3.82 1.5l2.87-2.87A9.63 9.63 0 0 0 12 2a10 10 0 0 0-8.96 5.52l3.35 2.62C7.18 7.77 9.39 6.01 12 6.01Z" />
        </svg>
        المتابعة باستخدام Google
    </a>

    <a class="inline-flex min-h-12 items-center justify-center gap-3 rounded-xl border border-[#1877f2] bg-[#1877f2] px-4 py-3 text-sm font-black text-white transition hover:bg-[#166fe5] focus:outline-none focus:ring-4 focus:ring-blue-100"
        href="{{ route('social.redirect', ['provider' => 'facebook']) }}">
        <svg class="size-5 shrink-0" aria-hidden="true" viewBox="0 0 24 24">
            <path fill="currentColor" d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.03 1.79-4.7 4.53-4.7 1.31 0 2.69.24 2.69.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.26h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07Z" />
            <path fill="#1877f2" d="M16.68 15.56l.53-3.49h-3.33V9.81c0-.96.47-1.89 1.96-1.89h1.51V4.95s-1.38-.24-2.69-.24c-2.74 0-4.53 1.67-4.53 4.7v2.66H7.08v3.49h3.05V24a12.7 12.7 0 0 0 3.75 0v-8.44h2.8Z" />
        </svg>
        المتابعة باستخدام Facebook
    </a>
</div>

<div class="my-6 flex items-center gap-3 text-xs font-bold text-slate-400" aria-hidden="true">
    <span class="h-px grow bg-slate-200"></span>
    <span>أو بالبريد الإلكتروني</span>
    <span class="h-px grow bg-slate-200"></span>
</div>
