@props(['title', 'image' => null, 'excerpt' => null, 'href' => null, 'meta' => null, 'rating' => null])

<div class="card h-100">
    @if($image)
        <img src="{{ $image }}" class="card-img-top" alt="{{ $title }}">
    @endif
    <div class="card-body d-flex flex-column">
        <h5 class="card-title">{{ $title }}</h5>
        @if($meta)
            <p class="text-muted small">{{ $meta }}</p>
        @endif
        @if($excerpt)
            <p class="card-text flex-grow-1">{{ $excerpt }}</p>
        @endif
        <div class="mt-3 text-end">
            @if($href)
                <a href="{{ $href }}" class="btn btn-sm btn-primary">View</a>
            @endif
        </div>
    </div>
</div>
@props(['title' => '', 'image' => null, 'excerpt' => '', 'href' => '#', 'meta' => null, 'rating' => null])

<div class="card h-100 shadow-sm">
    @if($image)
        <img src="{{ $image }}" class="card-img-top" alt="{{ $title }}" style="height:180px; object-fit:cover;">
    @endif
    <div class="card-body d-flex flex-column">
        <h5 class="card-title mb-1">{{ $title }}</h5>
        @if($meta)
            <p class="text-muted small mb-2">{{ $meta }}</p>
        @endif
        <p class="card-text text-muted mb-3">{{ $excerpt }}</p>
        <div class="mt-auto d-flex justify-content-between align-items-center">
            <a href="{{ $href }}" class="btn btn-sm btn-outline-primary">View</a>
            @if($rating)
                <div class="text-warning small"><i class="fa-solid fa-star"></i> {{ $rating }}</div>
            @endif
        </div>
    </div>
</div>
