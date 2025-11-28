<div class="card h-100">
    @php
        $image = data_get($recipe, 'image');
        $isOwnedByUser = auth()->check() && data_get($recipe, 'user_id') === auth()->id();
    @endphp
    @if($image)
        <img src="{{ Storage::url($image) }}" class="card-img-top" alt="{{ data_get($recipe, 'title') }}" style="height: 200px; object-fit: cover;">
    @else
        <div class="card-img-top d-flex align-items-center justify-content-center bg-secondary bg-opacity-25" style="height: 200px;">
            <i class="fa-regular fa-image text-muted" style="font-size: 3rem;"></i>
        </div>
    @endif
    
    @if($isOwnedByUser && !request()->routeIs('recipes.my'))
        <div class="position-absolute top-0 start-0 m-2" style="z-index: 10;">
            <span class="badge bg-primary bg-opacity-75 backdrop-blur">
                <i class="fa-solid fa-user"></i>
            </span>
        </div>
    @endif
    
    <div class="card-body d-flex flex-column position-relative">
        @auth
            @php
                $isFavorited = auth()->user()->favoriteRecipes()->where('recipe_id', data_get($recipe, 'id'))->exists();
            @endphp
            <form action="{{ route('recipes.favorite', data_get($recipe, 'slug')) }}" method="POST" class="position-absolute top-0 end-0 m-2 js-favorite-form" data-recipe-id="{{ data_get($recipe, 'id') }}">
                @csrf
                <button type="submit" data-icon-only="true" class="btn btn-sm {{ $isFavorited ? 'btn-danger' : 'btn-outline-danger' }}" title="{{ $isFavorited ? 'Remove from favorites' : 'Add to favorites' }}">
                    <i class="fa-{{ $isFavorited ? 'solid' : 'regular' }} fa-heart"></i>
                </button>
            </form>
        @endauth
        <h5 class="card-title" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding-right: 2.5rem;">{{ data_get($recipe, 'title') }}</h5>
        <p class="text-muted mb-1 small" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            @php
                $tags = data_get($recipe, 'tags');
                $tagCount = $tags && is_iterable($tags) ? count($tags) : 0;
            @endphp
            @if($tagCount > 0)
                @foreach($tags as $t)
                    @if($loop->index < 3)
                        <a href="/recipes?tag={{ urlencode($t->name ?? $t['name'] ?? (string)$t) }}" class="text-decoration-none small">{{ $t->name ?? ($t['name'] ?? ucfirst((string)$t)) }}</a>@if($loop->index < 2 && $loop->index < $tagCount - 1) · @endif
                    @endif
                @endforeach
                @if($tagCount > 3)
                    <span class="text-muted">· +{{ $tagCount - 3 }} more</span>
                @endif
            @endif
        </p>
        <p class="text-muted mb-2 small">
            @php
                $displayTime = '';
                if (is_numeric($recipe->time) && (int)$recipe->time > 0) {
                    $total = (int)$recipe->time;
                    $h = intdiv($total, 60);
                    $m = $total % 60;
                    $parts = [];
                    if ($h > 0) $parts[] = $h . ' hour' . ($h === 1 ? '' : 's');
                    if ($m > 0) $parts[] = $m . ' minute' . ($m === 1 ? '' : 's');
                    $displayTime = $parts ? implode(' ', $parts) : '';
                } else {
                    $displayTime = data_get($recipe, 'time') ?? '';
                }
            @endphp
            {{ $displayTime }}
        </p>
        @if(data_get($recipe, 'is_public') !== null)
            <p class="mb-2">
                <span class="badge {{ data_get($recipe, 'is_public') ? 'bg-success' : 'bg-secondary' }}">
                    <i class="fa-solid fa-{{ data_get($recipe, 'is_public') ? 'globe' : 'lock' }} me-1"></i>
                    {{ data_get($recipe, 'is_public') ? 'Public' : 'Private' }}
                </span>
            </p>
        @endif
        <p class="card-text" style="overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; min-height: 3em; line-height: 1.5em;">{{ data_get($recipe, 'description') }}</p>
        
        @php
            $avgRating = is_object($recipe) && method_exists($recipe, 'averageRating') ? $recipe->averageRating() : null;
            $ratingsCount = is_object($recipe) && method_exists($recipe, 'ratingsCount') ? $recipe->ratingsCount() : 0;
        @endphp
        <div class="mb-2" style="min-height: 1.5rem;">
            @if($avgRating)
                <div class="text-warning">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-{{ $i <= round($avgRating) ? 'solid' : 'regular' }} fa-star" style="font-size: 0.875rem;"></i>
                    @endfor
                    <span class="text-light fw-bold ms-1 small">{{ number_format($avgRating, 1) }}</span>
                    <span class="text-muted small">({{ $ratingsCount }})</span>
                </div>
            @else
                <div class="text-muted small">
                    <i class="fa-regular fa-star-half-stroke me-1"></i>No ratings yet
                </div>
            @endif
        </div>
        
        <div class="mt-3 text-end d-flex justify-content-end gap-2">
            <a href="{{ data_get($recipe, 'href') ?? url('/recipes/'.data_get($recipe, 'slug')) }}" class="btn btn-sm btn-primary">View</a>
            @auth
                @if(data_get($recipe, 'user_id') === auth()->id())
                    <a href="{{ route('recipes.edit', data_get($recipe, 'slug')) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                @endif
            @endauth
        </div>
    </div>
</div>
