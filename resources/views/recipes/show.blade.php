<x-app-layout>
    <x-slot name="title">{{ data_get($recipe, 'title') }}</x-slot>

    <div class="container py-4">
        <nav aria-label="breadcrumb" class="mb-3 no-print">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/recipes') }}">Recipes</a></li>
                <li class="breadcrumb-item active" aria-current="page" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ data_get($recipe, 'title') }}</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8">
                <!-- Header Section -->
                <div class="mb-4">
                    <h1 class="display-6 fw-bold mb-3">{{ data_get($recipe, 'title') }}</h1>
                    
                    <!-- Metadata Row -->
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                        @php 
                            $tags = data_get($recipe, 'tags');
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
                        
                        @if($tags && is_iterable($tags) && count($tags))
                            @foreach($tags as $t)
                                <a href="/recipes?tag={{ urlencode($t->name ?? $t['name'] ?? (string)$t) }}" class="badge bg-secondary text-decoration-none">
                                    {{ $t->name ?? ($t['name'] ?? ucfirst((string)$t)) }}
                                </a>
                            @endforeach
                        @endif
                        
                        @if($displayTime)
                            <span class="text-muted">
                                <i class="fa-regular fa-clock"></i> {{ $displayTime }}
                            </span>
                        @endif
                        
                        @if($recipe->difficulty)
                            <span style="letter-spacing: 1px;">
                                @if($recipe->difficulty === 'easy')
                                    <span style="color: #28a745;" data-bs-toggle="tooltip" data-bs-title="Easy difficulty">
                                        <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i><i class="fa-regular fa-circle" style="font-size: 0.5rem;"></i><i class="fa-regular fa-circle" style="font-size: 0.5rem;"></i>
                                    </span>
                                @elseif($recipe->difficulty === 'medium')
                                    <span style="color: #ffc107;" data-bs-toggle="tooltip" data-bs-title="Medium difficulty">
                                        <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i><i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i><i class="fa-regular fa-circle" style="font-size: 0.5rem;"></i>
                                    </span>
                                @else
                                    <span style="color: #dc3545;" data-bs-toggle="tooltip" data-bs-title="Hard difficulty">
                                        <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i><i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i><i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i>
                                    </span>
                                @endif
                            </span>
                        @endif
                        
                        @if($recipe->is_public !== null)
                            <span class="badge {{ $recipe->is_public ? 'bg-success' : 'bg-secondary' }} no-print" data-bs-toggle="tooltip" data-bs-title="{{ $recipe->is_public ? 'Public recipe - visible to everyone' : 'Private recipe - only visible to you' }}">
                                <i class="fa-solid fa-{{ $recipe->is_public ? 'globe' : 'lock' }}"></i>
                            </span>
                        @endif
                    </div>

                    <!-- Rating -->
                    @php
                        $avgRating = $recipe->averageRating();
                        $ratingsCount = $recipe->ratingsCount();
                    @endphp
                    @if($avgRating)
                        <div class="d-flex align-items-center mb-3 no-print">
                            <div class="text-warning me-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-{{ $i <= round($avgRating) ? 'solid' : 'regular' }} fa-star"></i>
                                @endfor
                            </div>
                            <span class="fw-bold">{{ number_format($avgRating, 1) }}</span>
                            <span class="text-muted ms-1">({{ $ratingsCount }})</span>
                        </div>
                    @endif

                    @if($recipe->description)
                        <p class="lead text-muted mb-0">{!! nl2br(e(data_get($recipe, 'description'))) !!}</p>
                    @endif
                </div>

                @if($recipe->image)
                    <div class="mb-4">
                        <img src="{{ Storage::url($recipe->image) }}" alt="{{ $recipe->title }}" class="img-fluid rounded-3 shadow" style="max-height: 500px; width: 100%; object-fit: cover;">
                    </div>
                @endif

                <!-- Ingredients & Method -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4 fw-semibold">
                                    <i class="fa-solid fa-list-check me-2 text-primary"></i>Ingredients
                                </h5>
                                @if($recipe->ingredients && $recipe->ingredients->count())
                                    <ul class="list-unstyled mb-0">
                                        @foreach($recipe->ingredients as $ingredient)
                                            <li class="mb-2 d-flex">
                                                <i class="fa-solid fa-check text-success me-2 mt-1" style="font-size: 0.875rem;"></i>
                                                <span>
                                                    @if($ingredient->amount)
                                                        <strong>{{ e($ingredient->amount) }}</strong>
                                                    @endif
                                                    {{ e($ingredient->name) }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted mb-0">No ingredients provided</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4 fw-semibold">
                                    <i class="fa-solid fa-list-ol me-2 text-primary"></i>Method
                                </h5>
                                @if($recipe->directions && $recipe->directions->count())
                                    <ol class="mb-0 ps-3">
                                        @foreach($recipe->directions as $direction)
                                            <li class="mb-3">
                                                {!! nl2br(e($direction->body)) !!}
                                            </li>
                                        @endforeach
                                    </ol>
                                @else
                                    <p class="text-muted mb-0">No method provided</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Action Buttons -->
                <div class="card mb-4 shadow-sm border-0 no-print">
                    <div class="card-body p-4">
                        <h6 class="card-title mb-3 fw-semibold" hidden>
                            <i class="fa-solid fa-ellipsis-vertical me-2 text-primary"></i>Actions
                        </h6>
                        <div class="d-grid gap-2">
                            @auth
                                @php
                                    $isFavorited = auth()->user()->favoriteRecipes()->where('recipe_id', $recipe->id)->exists();
                                @endphp
                                <form action="{{ route('recipes.favorite', $recipe->slug) }}" method="POST" class="js-favorite-form" data-recipe-id="{{ $recipe->id }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-1">
                                        <i class="fa-{{ $isFavorited ? 'solid' : 'regular' }} fa-heart"></i>
                                        <span>{{ $isFavorited ? 'Unfavorite' : 'Favorite' }}</span>
                                    </button>
                                </form>

                                <form action="{{ route('shopping-list.add-from-recipe', $recipe->slug) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-1">
                                        <i class="fa-solid fa-cart-shopping"></i>
                                        <span>Add ingredients to Shopping List</span>
                                    </button>
                                </form>
                                
                                @if($recipe->user_id === auth()->id())
                                    <a href="{{ route('recipes.edit', $recipe->slug) }}" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-edit me-1"></i>Edit
                                    </a>
                                    <form action="{{ route('recipes.destroy', $recipe->slug) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-secondary w-100 js-delete-btn" type="button" data-confirm="Delete this recipe?">
                                            <i class="fa-solid fa-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                @endif
                            @endauth
                            
                            <button onclick="window.print()" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-print me-1"></i>Print
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Rating Form -->
                @auth
                    @if($recipe->user_id !== auth()->id())
                        <div class="card shadow-sm border-0 no-print">
                            <div class="card-body p-4">
                                <h6 class="card-title mb-3 fw-semibold">
                                    <i class="fa-solid fa-star me-2 text-primary"></i>Rate this recipe
                                </h6>
                                @php
                                    $userRating = auth()->user()->recipeRatings()->where('recipe_id', $recipe->id)->first();
                                @endphp
                                <form action="{{ route('recipes.rate', $recipe->slug) }}" method="POST">
                                    @csrf
                                    <div class="d-flex gap-2 justify-content-center mb-3">
                                        @for($i = 1; $i <= 5; $i++)
                                            <label class="mb-0" style="cursor: pointer;">
                                                <input type="radio" name="rating" value="{{ $i }}" class="d-none rating-input" {{ $userRating && $userRating->rating == $i ? 'checked' : '' }} required>
                                                <i class="fa-star rating-star {{ $userRating && $i <= $userRating->rating ? 'fa-solid text-warning' : 'fa-regular text-muted' }}" style="font-size: 1.75rem;"></i>
                                            </label>
                                        @endfor
                                    </div>
                                    @if($userRating)
                                        <p class="text-muted small text-center mb-2">Your rating: {{ $userRating->rating }} stars</p>
                                    @endif
                                    <button type="submit" class="btn btn-primary w-100">Submit Rating</button>
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

        /* Refined ingredient layout (screen) */
        .ingredients-list { --amount-width: 110px; }
        .ingredients-list .ingredient-row { display:flex; align-items:baseline; gap:.75rem; padding:.5rem .75rem; }
        .ingredients-list .ingredient-row + .ingredient-row { border-top:1px solid rgba(255,255,255,.05); }
        .ingredients-list .ingredient-amount { flex:0 0 var(--amount-width); font-variant-numeric: tabular-nums; font-weight:500; color:var(--bs-secondary-color); white-space:nowrap; }
        .ingredients-list .ingredient-amount-empty { opacity:.35; }
        .ingredients-list .ingredient-name { flex:1 1 auto; min-width:0; }
        @media (max-width: 576px) { .ingredients-list { --amount-width: 80px; } }
        @media (prefers-color-scheme: light) { .ingredients-list .ingredient-row + .ingredient-row { border-color: rgba(0,0,0,.08); } }
    </style>

</x-app-layout>
