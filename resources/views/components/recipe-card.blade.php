@props(['title' => null, 'image' => null, 'description' => null, 'href' => null, 'meta' => null, 'rating' => null, 'recipe' => null, 'showCollectionRemove' => false, 'collectionSlug' => null])

@php
    // If recipe object is provided, extract properties from it
    if ($recipe) {
        $title = $title ?? data_get($recipe, 'title');
        $image = $image ?? data_get($recipe, 'image');
        $description = $description ?? data_get($recipe, 'description');
        $href = $href ?? url('/recipes/' . data_get($recipe, 'slug'));
        $rating = $rating ?? ($recipe->averageRating() ?? null);
    }
@endphp

<div class="card h-100 shadow-sm">
    {{-- Image area with fixed height to keep all cards equal height --}}
    @if($image)
        <div style="height:180px; overflow:hidden; position: relative;">
            <img src="{{ $image }}" alt="{{ $title }}" class="w-100 h-100" style="object-fit:cover; display:block;">
            
            @auth
                @if($showCollectionRemove && $collectionSlug && $recipe)
                    {{-- Show remove button when in collection view --}}
                    <form action="{{ route('collections.remove-recipe', [$collectionSlug, data_get($recipe, 'slug')]) }}" method="POST" class="position-absolute top-0 end-0 m-2 js-remove-from-collection-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" data-icon-only="true" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" data-bs-title="Remove from collection">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </form>
                @elseif($recipe)
                    {{-- Show add to collection button --}}
                    <button type="button" data-icon-only="true" class="btn btn-sm btn-outline-primary position-absolute top-0 end-0 m-2 js-open-collection-modal" data-recipe-id="{{ data_get($recipe, 'id') }}" data-recipe-slug="{{ data_get($recipe, 'slug') }}" data-bs-toggle="tooltip" data-bs-title="Add to collection">
                        <i class="fa-solid fa-folder-plus"></i>
                    </button>
                @endif
            @endauth
        </div>
    @else
        <div class="bg-body d-flex align-items-center justify-content-center" style="height:180px;">
            <span class="text-muted">No image</span>
        </div>
    @endif

    <div class="card-body d-flex flex-column">
        <h5 class="card-title mb-2">{{ $title }}</h5>
        @if(isset($meta_slot))
            <p class="small mb-2">{!! $meta_slot !!}</p>
        @elseif($meta)
            <p class="text-muted small mb-2">{{ $meta }}</p>
        @endif

        @if($description)
            <p class="card-text mb-3" style="flex:1 1 auto;">{{ $description }}</p>
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

