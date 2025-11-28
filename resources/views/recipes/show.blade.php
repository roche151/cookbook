<x-app-layout>
    <x-slot name="title">{{ data_get($recipe, 'title') }}</x-slot>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-8">
                <nav aria-label="breadcrumb" class="mb-2 no-print">
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
                    @if($displayTime && $recipe->is_public !== null)
                        <span class="mx-2">•</span>
                    @endif
                    @if($recipe->is_public !== null)
                        <span class="badge {{ $recipe->is_public ? 'bg-success' : 'bg-secondary' }} no-print">
                            <i class="fa-solid fa-{{ $recipe->is_public ? 'globe' : 'lock' }} me-1"></i>
                            {{ $recipe->is_public ? 'Public' : 'Private' }}
                        </span>
                    @endif
                </p>
                
                @if($recipe->image)
                    <div class="mb-4">
                        <img src="{{ Storage::url($recipe->image) }}" alt="{{ $recipe->title }}" class="img-fluid rounded shadow-sm" style="max-height: 400px; width: 100%; object-fit: cover;">
                    </div>
                @endif
                
                <p class="lead">{{ data_get($recipe, 'description') }}</p>

                <!-- Rating Display -->
                <div class="mb-3 no-print">
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

                <!-- Ingredients (shown before Method; visible on screen and print) -->
                <div class="print-ingredients">
                    <h5>Ingredients</h5>
                    @if($recipe->ingredients && $recipe->ingredients->count())
                        <ul class="list-group mb-0 ingredients-list">
                            @foreach($recipe->ingredients as $ingredient)
                                <li class="list-group-item">
                                    @if($ingredient->amount)
                                        <span class="text-muted me-2">{{ e($ingredient->amount) }}</span>
                                    @endif
                                    <span>{{ e($ingredient->name) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No ingredients provided for this recipe.</p>
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
                <div class="d-flex flex-wrap gap-2 align-items-center mb-2 no-print">
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

                <!-- Print Button -->
                <div class="mb-3 no-print">
                    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-print me-1"></i>Print Recipe
                    </button>
                </div>

                <!-- Rating Form (only for logged-in users who don't own the recipe) -->
                @auth
                    @if($recipe->user_id !== auth()->id())
                        <div class="card mb-3 no-print">
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

    <style>
        @media print {
            /* Hide all non-essential elements */
            .no-print,
            nav,
            .btn,
            button,
            form,
            a,
            header,
            footer,
            .sidebar {
                display: none !important;
            }
            /* Ensure ingredients block is displayed and styled in print */
            .print-ingredients { display: block !important; }


            /* Show ingredients card specifically */
            .print-ingredients {
                display: block !important;
                border: none !important;
                box-shadow: none !important;
            }

            .print-ingredients .card-body {
                padding: 0 !important;
            }

            /* Full page layout */
            * {
                margin: 0;
                padding: 0;
            }

            body {
                background: white !important;
                color: black !important;
                font-family: Arial, sans-serif;
                padding: 20px !important;
            }

            .container {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .row {
                display: block !important;
                margin: 0 !important;
            }

            .col-md-8,
            .col-md-4 {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                float: none !important;
            }

            /* Title styling */
            h1 {
                font-size: 28pt !important;
                margin-bottom: 8pt !important;
                font-weight: bold !important;
                color: black !important;
            }

            /* Tags and time */
            .text-muted {
                color: #666 !important;
                font-size: 10pt !important;
                margin-bottom: 12pt !important;
            }

            /* Image optimization */
            img {
                max-width: 100% !important;
                height: auto !important;
                margin: 12pt 0 !important;
                page-break-inside: avoid;
            }

            /* Description */
            .lead {
                font-size: 11pt !important;
                margin-bottom: 16pt !important;
                color: black !important;
            }

            /* Section headings */
            h5 {
                font-size: 14pt !important;
                font-weight: bold !important;
                margin-top: 16pt !important;
                margin-bottom: 8pt !important;
                color: black !important;
                page-break-after: avoid;
            }

            h6 {
                font-size: 12pt !important;
                font-weight: bold !important;
                margin-top: 12pt !important;
                margin-bottom: 6pt !important;
                color: black !important;
            }

            /* Method steps */
            .list-group {
                margin: 0 !important;
                padding: 0 !important;
                list-style: decimal inside !important;
            }

            .list-group-numbered {
                counter-reset: item !important;
                list-style: none !important;
                padding-left: 0 !important;
            }

            .list-group-numbered .list-group-item {
                counter-increment: item !important;
                padding: 6pt 0 !important;
                border: none !important;
                background: transparent !important;
                color: black !important;
                font-size: 11pt !important;
                line-height: 1.6 !important;
                page-break-inside: avoid;
            }

            .list-group-numbered .list-group-item::before {
                content: counter(item) ". " !important;
                font-weight: bold !important;
                margin-right: 8pt !important;
            }

            /* Ingredients styling - show them */
            .col-md-4 {
                display: block !important;
                margin-top: 16pt !important;
            }

            .print-ingredients h6 {
                font-size: 12pt !important;
                font-weight: bold !important;
                margin-bottom: 8pt !important;
                color: black !important;
            }

            .ingredients-list {
                list-style: none !important;
                padding: 0 !important;
                display: block !important;
            }

            .ingredients-list .list-group-item {
                padding: 4pt 0 !important;
                border: none !important;
                background: transparent !important;
                color: black !important;
                font-size: 11pt !important;
                display: block !important;
            }

            .ingredients-list .list-group-item span {
                color: black !important;
            }

            .ingredients-list .list-group-item .text-muted {
                color: #666 !important;
            }

            /* Remove Bootstrap styling */
            hr {
                border: 1px solid #ccc !important;
                margin: 12pt 0 !important;
            }

            /* Ensure readable colors */
            p, span, div, li {
                color: black !important;
            }

            /* Page breaks */
            .page-break-before {
                page-break-before: always;
            }

            .page-break-after {
                page-break-after: always;
            }
        }
    </style>

</x-app-layout>
