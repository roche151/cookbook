@props(['title', 'image' => null, 'excerpt' => null, 'href' => null, 'meta' => null, 'rating' => null])

<div class="card h-100 shadow-sm">
    {{-- Image area with fixed height to keep all cards equal height --}}
    @if($image)
        <div style="height:180px; overflow:hidden;">
            <img src="{{ $image }}" alt="{{ $title }}" class="w-100 h-100" style="object-fit:cover; display:block;">
        </div>
    @else
        <div class="bg-body d-flex align-items-center justify-content-center" style="height:180px;">
            <span class="text-muted">No image</span>
        </div>
    @endif

    <div class="card-body d-flex flex-column">
        <h5 class="card-title mb-2">{{ $title }}</h5>
        @if($meta)
            <p class="text-muted small mb-2">{{ $meta }}</p>
        @endif

        @if($excerpt)
            <p class="card-text mb-3" style="flex:1 1 auto;">{{ $excerpt }}</p>
        @else
            <div style="flex:1 1 auto;"></div>
        @endif

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                @if($rating)
                    <small class="text-warning">⭐ {{ number_format((float)$rating, 1) }}</small>
                @endif
            </div>
            <div>
                @if($href)
                    <a href="{{ $href }}" class="btn btn-sm btn-primary">View</a>
                @endif
            </div>
        </div>
    </div>
</div>

