@props(['campaign', 'placement' => 'top'])

@if($campaign)
    <aside {{ $attributes->class(['relative overflow-hidden rounded-2xl border border-violet-100 bg-white p-1 shadow-[0_16px_45px_rgba(49,46,129,0.08)]']) }} aria-label="إعلان">
        <span class="absolute top-2 right-2 z-10 rounded-full bg-slate-950/70 px-2 py-1 text-[10px] font-bold text-white">إعلان</span>
        <a class="ad-link block h-full overflow-hidden rounded-[14px]" href="{{ route('ads.click', $campaign) }}" data-ad-impression-url="{{ route('ads.impression', $campaign) }}" target="_blank" rel="noopener noreferrer sponsored" aria-label="{{ $campaign->alt_text }}">
            <img @class(['block h-full w-full', 'object-cover' => $placement === 'side', 'object-contain' => $placement !== 'side']) src="{{ Storage::disk('public')->url($campaign->image_path) }}" alt="{{ $campaign->alt_text }}" loading="lazy">
        </a>
    </aside>
@endif
