<x-app-layout>
    <x-slot name="title">{{ data_get($recipe, 'title') }}</x-slot>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-8">
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/recipes') }}">Recipes</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ data_get($recipe, 'title') }}</li>
                    </ol>
                </nav>
                <h1 class="display-6">{{ data_get($recipe, 'title') }}</h1>
                @php $tags = data_get($recipe, 'tags'); @endphp
                <p class="text-muted">
                    @if($tags && is_iterable($tags) && count($tags))
                        @foreach($tags as $t)
                            <a href="/recipes?tag={{ urlencode($t->name ?? $t['name'] ?? (string)$t) }}" class="text-decoration-none small me-1">{{ $t->name ?? ($t['name'] ?? ucfirst((string)$t)) }}</a>
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
                    {{ $displayTime }}
                </p>
                
                @if($recipe->image)
                    <div class="mb-4">
                        <img src="{{ Storage::url($recipe->image) }}" alt="{{ $recipe->title }}" class="img-fluid rounded shadow-sm" style="max-height: 400px; width: 100%; object-fit: cover;">
                    </div>
                @endif
                
                <p class="lead">{{ data_get($recipe, 'description') }}</p>

                <!-- Rating Display -->
                <div class="mb-3">
                    @php
                        $avgRating = $recipe->averageRating();
                        $ratingsCount = $recipe->ratingsCount();
                    @endphp
                    @if($avgRating)
                        <div class="d-flex align-items-center">
                            <div class="text-warning me-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-{{ $i <= round($avgRating) ? 'solid' : 'regular' }} fa-star"></i>
                                @endfor
                            </div>
                            <span class="fw-bold">{{ number_format($avgRating, 1) }}</span>
                            <span class="text-muted ms-1">({{ $ratingsCount }} {{ Str::plural('rating', $ratingsCount) }})</span>
                        </div>
                    @else
                        <p class="text-muted mb-0">No ratings yet</p>
                    @endif
                </div>

                <hr>
                <h5>Method</h5>
                @if($recipe->directions && $recipe->directions->count())
                    <ol class="list-group list-group-numbered mb-3">
                        @foreach($recipe->directions as $direction)
                            <li class="list-group-item">
                                {!! nl2br(e($direction->body)) !!}
                            </li>
                        @endforeach
                    </ol>
                @else
                    <p class="text-muted">No method provided for this recipe.</p>
                @endif
            </div>
            <div class="col-md-4">
                @auth
                <div class="d-flex gap-2 align-items-center mb-2">
                    @php
                        $isFavorited = auth()->user()->favoriteRecipes()->where('recipe_id', $recipe->id)->exists();
                    @endphp
                    <form action="{{ route('recipes.favorite', $recipe->slug) }}" method="POST" class="mb-0">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $isFavorited ? 'btn-danger' : 'btn-outline-danger' }} px-3">
                            <i class="fa-{{ $isFavorited ? 'solid' : 'regular' }} fa-heart me-1"></i>
                            {{ $isFavorited ? 'Unfavorite' : 'Favorite' }}
                        </button>
                    </form>
                    @if($recipe->user_id === auth()->id())
                    <a href="{{ route('recipes.edit', $recipe->slug) }}" class="btn btn-sm btn-secondary px-3">Edit</a>

                    <form action="{{ route('recipes.destroy', $recipe->slug) }}" method="POST" class="mb-0">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger px-3 js-delete-btn" type="button" data-confirm="Delete this recipe?">Delete</button>
                    </form>
                    @endif
                </div>
                @endauth

                <!-- Rating Form (only for logged-in users who don't own the recipe) -->
                @auth
                    @if($recipe->user_id !== auth()->id())
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Rate this recipe</h6>
                                @php
                                    $userRating = auth()->user()->recipeRatings()->where('recipe_id', $recipe->id)->first();
                                @endphp
                                <form action="{{ route('recipes.rate', $recipe->slug) }}" method="POST">
                                    @csrf
                                    <div class="d-flex gap-2 align-items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <label class="mb-0" style="cursor: pointer;">
                                                <input type="radio" name="rating" value="{{ $i }}" class="d-none rating-input" {{ $userRating && $userRating->rating == $i ? 'checked' : '' }} required>
                                                <i class="fa-star rating-star {{ $userRating && $i <= $userRating->rating ? 'fa-solid text-warning' : 'fa-regular text-muted' }}" style="font-size: 1.5rem;"></i>
                                            </label>
                                        @endfor
                                    </div>
                                    @if($userRating)
                                        <p class="text-muted small mb-2 mt-2">Your current rating: {{ $userRating->rating }} stars</p>
                                    @endif
                                    <button type="submit" class="btn btn-primary btn-sm mt-2">Submit Rating</button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endauth

                <div class="card">
                    <div class="card-body">
                        <h6>Ingredients</h6>
                        @if($recipe->ingredients && $recipe->ingredients->count())
                            <ul class="list-group mb-0">
                                @foreach($recipe->ingredients as $ingredient)
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div>
                                            @if($ingredient->amount)
                                                <span class="text-muted me-2">{{ e($ingredient->amount) }}</span>
                                            @endif
                                            <span>{{ e($ingredient->name) }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">No ingredients provided for this recipe.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @auth
        @if($recipe->user_id !== auth()->id())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const stars = document.querySelectorAll('.rating-star');
                const inputs = document.querySelectorAll('.rating-input');
                
                // Hover effect
                stars.forEach((star, index) => {
                    star.addEventListener('mouseenter', function() {
                        highlightStars(index + 1);
                    });
                    
                    star.parentElement.addEventListener('mouseleave', function() {
                        const checkedInput = document.querySelector('.rating-input:checked');
                        if (checkedInput) {
                            const checkedValue = parseInt(checkedInput.value);
                            highlightStars(checkedValue);
                        } else {
                            highlightStars(0);
                        }
                    });
                    
                    star.parentElement.addEventListener('click', function() {
                        highlightStars(index + 1);
                    });
                });
                
                function highlightStars(count) {
                    stars.forEach((star, index) => {
                        if (index < count) {
                            star.classList.remove('fa-regular', 'text-muted');
                            star.classList.add('fa-solid', 'text-warning');
                        } else {
                            star.classList.remove('fa-solid', 'text-warning');
                            star.classList.add('fa-regular', 'text-muted');
                        }
                    });
                }
            });
        </script>
        @endif
    @endauth

</x-app-layout>
