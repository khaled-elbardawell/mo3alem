@props(['campaign', 'placement' => 'top'])

@if($campaign)
    <aside {{ $attributes->class(['relative overflow-hidden rounded-2xl border border-violet-100 bg-white p-1 shadow-[0_16px_45px_rgba(49,46,129,0.08)]']) }}>
        <a class="media-card-link block h-full overflow-hidden rounded-[14px]" href="{{ $campaign->safeTargetUrl() }}" data-open-endpoint="{{ route('ads.click', $campaign) }}" data-view-endpoint="{{ route('ads.impression', $campaign) }}" target="_blank" rel="noopener noreferrer sponsored">
            <img @class(['block h-full w-full', 'object-cover' => $placement === 'side', 'object-contain' => $placement !== 'side']) src="{{ Storage::disk('public')->url($campaign->image_path) }}" alt="{{ $campaign->alt_text }}" loading="lazy">
        </a>
    </aside>
@endif
