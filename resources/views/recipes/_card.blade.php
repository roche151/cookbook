<div class="card h-100">
    <div class="card-body d-flex flex-column position-relative">
        @auth
            @php
                $isFavorited = auth()->user()->favoriteRecipes()->where('recipe_id', data_get($recipe, 'id'))->exists();
            @endphp
            <form action="{{ route('recipes.favorite', data_get($recipe, 'slug')) }}" method="POST" class="position-absolute top-0 end-0 m-2">
                @csrf
                <button type="submit" class="btn btn-sm {{ $isFavorited ? 'btn-danger' : 'btn-outline-secondary' }}" title="{{ $isFavorited ? 'Remove from favorites' : 'Add to favorites' }}">
                    <i class="fa-{{ $isFavorited ? 'solid' : 'regular' }} fa-heart"></i>
                </button>
            </form>
        @endauth
        <h5 class="card-title">{{ data_get($recipe, 'title') }}</h5>
        <p class="text-muted mb-2 small">
            @php
                $tags = data_get($recipe, 'tags');
            @endphp
            @if($tags && is_iterable($tags) && count($tags))
                @foreach($tags as $t)
                    <a href="/recipes?tag={{ urlencode($t->name ?? $t['name'] ?? (string)$t) }}" class="text-decoration-none small me-1">{{ $t->name ?? ($t['name'] ?? ucfirst((string)$t)) }}</a> ·
                @endforeach
            @endif
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
            {{ $displayTime }}</p>
        <p class="card-text grow">{{ data_get($recipe, 'description') }}</p>
        
        @php
            $avgRating = is_object($recipe) && method_exists($recipe, 'averageRating') ? $recipe->averageRating() : null;
            $ratingsCount = is_object($recipe) && method_exists($recipe, 'ratingsCount') ? $recipe->ratingsCount() : 0;
        @endphp
        @if($avgRating)
            <div class="mb-2">
                <div class="text-warning">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-{{ $i <= round($avgRating) ? 'solid' : 'regular' }} fa-star" style="font-size: 0.875rem;"></i>
                    @endfor
                    <span class="text-light fw-bold ms-1 small">{{ number_format($avgRating, 1) }}</span>
                    <span class="text-muted small">({{ $ratingsCount }})</span>
                </div>
            </div>
        @endif
        
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
