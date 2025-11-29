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
            <span class="badge bg-primary bg-opacity-75 backdrop-blur" data-bs-toggle="tooltip" data-bs-title="Your recipe">
                <i class="fa-solid fa-user"></i>
            </span>
        </div>
    @endif
    
    @if(data_get($recipe, 'is_public') !== null)
        <div class="position-absolute top-0 end-0 m-2" style="z-index: 10;">
            <span class="badge {{ data_get($recipe, 'is_public') ? 'bg-success' : 'bg-secondary' }} bg-opacity-75 backdrop-blur" data-bs-toggle="tooltip" data-bs-title="{{ data_get($recipe, 'is_public') ? 'Public recipe - visible to everyone' : 'Private recipe - only visible to you' }}">
                <i class="fa-solid fa-{{ data_get($recipe, 'is_public') ? 'globe' : 'lock' }}"></i>
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
                <button type="submit" data-icon-only="true" class="btn btn-sm {{ $isFavorited ? 'btn-danger' : 'btn-outline-danger' }}" data-bs-toggle="tooltip" data-bs-title="{{ $isFavorited ? 'Remove from favorites' : 'Add to favorites' }}">
                    <i class="fa-{{ $isFavorited ? 'solid' : 'regular' }} fa-heart"></i>
                </button>
            </form>
        @endauth
        <h5 class="card-title mb-2" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding-right: 2.5rem;">{{ data_get($recipe, 'title') }}</h5>
        
        <p class="text-muted mb-2 small" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            @php
                $tags = data_get($recipe, 'tags');
                $tagCount = $tags && is_iterable($tags) ? count($tags) : 0;
                
                $displayTime = '';
                if (is_numeric($recipe->time) && (int)$recipe->time > 0) {
                    $total = (int)$recipe->time;
                    $h = intdiv($total, 60);
                    $m = $total % 60;
                    $parts = [];
                    if ($h > 0) $parts[] = $h . 'h';
                    if ($m > 0) $parts[] = $m . 'm';
                    $displayTime = $parts ? implode(' ', $parts) : '';
                } else {
                    $displayTime = data_get($recipe, 'time') ?? '';
                }
            @endphp
            @if($tagCount > 0)
                @foreach($tags as $t)
                    @if($loop->index < 2)
                        <a href="/recipes?tag={{ urlencode($t->name ?? $t['name'] ?? (string)$t) }}" class="text-decoration-none">{{ $t->name ?? ($t['name'] ?? ucfirst((string)$t)) }}</a>@if(!$loop->last && $loop->index < 1 && $tagCount > 1) · @endif
                    @endif
                @endforeach
                @if($tagCount > 2)
                    <span class="text-muted"> +{{ $tagCount - 2 }}</span>
                @endif
                @if($displayTime || $recipe->difficulty)
                    <span class="mx-1">·</span>
                @endif
            @endif
            @if($displayTime)
                <i class="fa-regular fa-clock" style="font-size: 0.75rem;"></i> {{ $displayTime }}
            @endif
            @if($recipe->difficulty)
                <span class="mx-1">·</span>
                <span style="letter-spacing: 1px;">
                    @if($recipe->difficulty === 'easy')
                        <span style="color: #28a745;" data-bs-toggle="tooltip" data-bs-title="Easy difficulty">
                            <i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i><i class="fa-regular fa-circle" style="font-size: 0.45rem;"></i><i class="fa-regular fa-circle" style="font-size: 0.45rem;"></i>
                        </span>
                    @elseif($recipe->difficulty === 'medium')
                        <span style="color: #ffc107;" data-bs-toggle="tooltip" data-bs-title="Medium difficulty">
                            <i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i><i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i><i class="fa-regular fa-circle" style="font-size: 0.45rem;"></i>
                        </span>
                    @else
                        <span style="color: #dc3545;" data-bs-toggle="tooltip" data-bs-title="Hard difficulty">
                            <i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i><i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i><i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i>
                        </span>
                    @endif
                </span>
            @endif
        </p>

        <p class="card-text mb-2" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ data_get($recipe, 'description') }}</p>
        
        @php
            $avgRating = is_object($recipe) && method_exists($recipe, 'averageRating') ? $recipe->averageRating() : null;
            $ratingsCount = is_object($recipe) && method_exists($recipe, 'ratingsCount') ? $recipe->ratingsCount() : 0;
        @endphp
        <div class="mb-2">
            @if($avgRating)
                <div class="text-warning small">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-{{ $i <= round($avgRating) ? 'solid' : 'regular' }} fa-star" style="font-size: 0.75rem;"></i>
                    @endfor
                    <span class="text-light fw-bold ms-1">{{ number_format($avgRating, 1) }}</span>
                    <span class="text-muted">({{ $ratingsCount }})</span>
                </div>
            @else
                <div class="text-muted small">
                    No ratings yet
                </div>
            @endif
        </div>
        
        <div class="mt-auto d-flex justify-content-end gap-2">
            <a href="{{ data_get($recipe, 'href') ?? url('/recipes/'.data_get($recipe, 'slug')) }}" class="btn btn-sm btn-primary">View</a>
            @auth
                @if(data_get($recipe, 'user_id') === auth()->id())
                    <a href="{{ route('recipes.edit', data_get($recipe, 'slug')) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                @endif
            @endauth
        </div>
    </div>
</div>
