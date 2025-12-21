<x-app-layout>
    <x-slot name="title">{{ data_get($recipe, 'title') }}</x-slot>
    
    <!-- Open Graph Meta Tags for Social Sharing -->
    <x-slot name="head">
        <meta property="og:type" content="article">
        <meta property="og:title" content="{{ $recipe->title }}">
        <meta property="og:description" content="{{ Str::limit($recipe->description ?? 'Check out this delicious recipe!', 200) }}">
        <meta property="og:url" content="{{ route('recipes.show', $recipe) }}">
        @if($recipe->image)
            <meta property="og:image" content="{{ url(Storage::url($recipe->image)) }}">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
        @endif
        <meta property="og:site_name" content="{{ config('app.name') }}">
        
        <!-- Twitter Card Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $recipe->title }}">
        <meta name="twitter:description" content="{{ Str::limit($recipe->description ?? 'Check out this delicious recipe!', 200) }}">
        @if($recipe->image)
            <meta name="twitter:image" content="{{ url(Storage::url($recipe->image)) }}">
        @endif
        
        <!-- WhatsApp/Generic -->
        <meta property="og:image:alt" content="{{ $recipe->title }}">
    </x-slot>

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
                            $sourceHost = $recipe->source_url ? parse_url($recipe->source_url, PHP_URL_HOST) : null;
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
                                
                                @auth
                                    @if($recipe->ingredients && $recipe->ingredients->count())
                                        <form action="{{ route('shopping-list.add-from-recipe', $recipe->slug) }}" method="POST" class="mt-4 pt-3 border-top">
                                            @csrf
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fa-solid fa-cart-plus me-2"></i>Add to Shopping List
                                            </button>
                                        </form>
                                    @endif
                                @endauth
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
                @auth
                    <!-- Quick Actions -->
                    <div class="card mb-4 shadow-sm border-0 no-print">
                        <div class="card-body p-4">
                            <div class="d-grid gap-2">
                                @php
                                    $isFavorited = auth()->user()->favoriteRecipes()->where('recipe_id', $recipe->id)->exists();
                                @endphp
                                <form action="{{ route('recipes.favorite', $recipe->slug) }}" method="POST" class="js-favorite-form" data-recipe-id="{{ $recipe->id }}">
                                    @csrf
                                    <button type="submit" class="btn btn-{{ $isFavorited ? 'danger' : 'outline-secondary' }} w-100">
                                        <i class="fa-{{ $isFavorited ? 'solid' : 'regular' }} fa-heart me-2"></i>{{ $isFavorited ? 'Unfavorite' : 'Favorite' }}
                                    </button>
                                </form>
                                
                                @if($recipe->user_id === auth()->id())
                                    <a href="{{ route('recipes.edit', $recipe->slug) }}" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-edit me-2"></i>Edit
                                    </a>
                                    <form action="{{ route('recipes.destroy', $recipe->slug) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger w-100 js-delete-btn" type="button" data-confirm="Delete this recipe?">
                                            <i class="fa-solid fa-trash me-2"></i>Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endauth
                
                <!-- Share & Export -->
                <div class="card mb-4 shadow-sm border-0 no-print">
                    <div class="card-body p-4">
                        <h6 class="card-title mb-3 fw-semibold" hidden>
                            <i class="fa-solid fa-share-nodes me-2 text-primary"></i>Share & Export
                        </h6>
                        
                        <!-- Export PDF -->
                        <a href="{{ route('recipes.pdf', $recipe) }}" class="btn btn-outline-secondary w-100 mb-2" target="_blank">
                            <i class="fa-solid fa-file-pdf me-2"></i>PDF
                        </a>
                        
                        <!-- Print PDF -->
                        <button onclick="printRecipe()" class="btn btn-outline-secondary w-100 mb-2">
                            <i class="fa-solid fa-print me-2"></i>Print
                        </button>
                        
                        <!-- Share Button -->
                        <button onclick="shareRecipe()" class="btn btn-outline-secondary w-100 mb-2">
                            <i class="fa-solid fa-share-nodes me-2"></i>Share
                        </button>
                        
                        <!-- Cook Mode -->
                        <button onclick="enterCookMode()" class="btn btn-primary w-100">
                            <i class="fa-solid fa-utensils me-2"></i>Cook Mode
                        </button>
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

    <script>
        function printRecipe() {
            const pdfUrl = '{{ route('recipes.pdf', $recipe) }}';
            const printWindow = window.open(pdfUrl, '_blank');
            
            if (printWindow) {
                printWindow.onload = function() {
                    printWindow.print();
                };
            }
        }

        function shareRecipe() {
            const shareData = {
                title: '{{ $recipe->title }}',
                text: '{{ Str::limit($recipe->description ?? "Check out this recipe!", 100) }}',
                url: window.location.href
            };

            if (navigator.share) {
                navigator.share(shareData)
                    .then(() => console.log('Shared successfully'))
                    .catch((error) => {
                        console.log('Error sharing:', error);
                        // Fall back to copy link if share fails
                        fallbackCopyLink();
                    });
            } else {
                // Fallback: copy link to clipboard
                fallbackCopyLink();
            }
        }

        function fallbackCopyLink() {
            const url = window.location.href;
            
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(() => {
                    alert('Link copied to clipboard! You can now paste it anywhere to share.');
                }).catch(() => {
                    prompt('Copy this link to share:', url);
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = url;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    alert('Link copied to clipboard! You can now paste it anywhere to share.');
                } catch {
                    prompt('Copy this link to share:', url);
                }
                
                document.body.removeChild(textArea);
            }
        }

        // ========== COOK MODE ==========
        let wakeLock = null;
        const allIngredients = {};
        let currentStepIndex = null;

        // Build ingredient map
        @forelse($recipe->ingredients as $ing)
            (function() {
                const ingName = '{{ strtolower($ing->name) }}'.toLowerCase();
                allIngredients[ingName] = {
                    name: '{{ $ing->name }}',
                    amount: '{{ $ing->amount }}',
                    id: {{ $ing->id }}
                };
            })();
        @empty
        @endforelse

        async function enterCookMode() {
            document.getElementById('cook-mode-modal').style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Auto-select first step
            selectCookStep(0);

            // Request wake lock
            if (navigator.wakeLock) {
                try {
                    wakeLock = await navigator.wakeLock.request('screen');
                    wakeLock.addEventListener('release', () => console.log('Wake lock released'));
                } catch (err) {
                    console.log('Wake lock request failed:', err);
                }
            }
        }

        function exitCookMode() {
            document.getElementById('cook-mode-modal').style.display = 'none';
            document.body.style.overflow = '';
            currentStepIndex = null;

            // Release wake lock
            if (wakeLock) {
                wakeLock.release();
                wakeLock = null;
            }
        }

        function selectCookStep(stepIndex) {
            currentStepIndex = stepIndex;
            
            // Highlight selected step
            const stepElements = document.querySelectorAll('.cook-step-item');
            stepElements.forEach((el, idx) => {
                if (idx === stepIndex) {
                    el.classList.add('cook-step-active');
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    el.classList.remove('cook-step-active');
                }
            });

            // Update button states
            const prevBtn = document.getElementById('cook-prev-btn');
            const nextBtn = document.getElementById('cook-next-btn');
            if (prevBtn) prevBtn.disabled = stepIndex === 0;
            if (nextBtn) nextBtn.disabled = stepIndex === stepElements.length - 1;

            // Update ingredients for this step
            const stepElement = document.querySelector(`[data-step-index="${stepIndex}"]`);
            if (stepElement) {
                const stepText = stepElement.getAttribute('data-step-text');
                updateIngredientsForStep(stepText);
            }
        }

        function previousStep() {
            if (currentStepIndex > 0) {
                selectCookStep(currentStepIndex - 1);
            }
        }

        function nextStep() {
            const stepCount = document.querySelectorAll('.cook-step-item').length;
            if (currentStepIndex < stepCount - 1) {
                selectCookStep(currentStepIndex + 1);
            }
        }

        function updateIngredientsForStep(stepText) {
            const ingredientsList = document.getElementById('cook-current-ingredients');
            const matchedIngredients = [];
            const mentionOrder = {};

            // Common words to ignore
            const stopWords = ['and', 'the', 'with', 'for', 'from', 'into', 'over', 'until', 'onto', 'through', 'about', 'etc'];

            // Split step text into words (lowercase, remove punctuation)
            const stepWords = stepText.toLowerCase().split(/\W+/).filter(w => w.length > 2);

            // Find ingredients mentioned in this step
            Object.entries(allIngredients).forEach(([ingName, ingData]) => {
                // Split ingredient name into words, exclude stop words
                const ingWords = ingName.split(/\W+/).filter(w => w.length > 2 && !stopWords.includes(w));
                
                // Check if any word from the step matches any word from the ingredient
                let earliestIndex = -1;
                for (const ingWord of ingWords) {
                    const index = stepText.indexOf(ingWord);
                    if (index !== -1) {
                        if (earliestIndex === -1 || index < earliestIndex) {
                            earliestIndex = index;
                        }
                    }
                }
                
                if (earliestIndex !== -1) {
                    mentionOrder[ingName] = earliestIndex;
                    matchedIngredients.push(ingName);
                }
            });

            // Sort by mention order in step
            matchedIngredients.sort((a, b) => mentionOrder[a] - mentionOrder[b]);

            // Render matched ingredients
            if (matchedIngredients.length === 0) {
                ingredientsList.innerHTML = '<p class="text-muted small">No specific ingredients for this step</p>';
            } else {
                ingredientsList.innerHTML = matchedIngredients.map(ingName => {
                    const ingData = allIngredients[ingName];
                    return `
                        <div class="cook-ingredient-item">
                            <div class="cook-ingredient-check-icon">
                                <i class="fa-solid fa-check text-success"></i>
                            </div>
                            <div class="cook-ingredient-content">
                                ${ingData.amount ? `<strong>${ingData.amount}</strong> ` : ''}${ingData.name}
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        function toggleCookDarkMode() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme-preference', newTheme);
        }
    </script>

    <style>
        /* Cook Mode Styles */
        .cook-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--bs-body-bg, white);
            color: var(--bs-body-color, #000);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .cook-header {
            background: var(--bs-secondary-bg, #f8f9fa);
            color: var(--bs-body-color, #000);
            padding: 1rem 1.5rem;
            border-bottom: 2px solid var(--bs-border-color, #dee2e6);
            flex-shrink: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .cook-header h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            word-break: break-word;
            flex: 1;
        }

        .cook-header-buttons {
            display: flex;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .cook-body {
            flex: 1;
            display: flex;
            overflow: hidden;
            gap: 0;
        }

        .cook-footer {
            background: var(--bs-secondary-bg, #f8f9fa);
            border-top: 2px solid var(--bs-border-color, #dee2e6);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-shrink: 0;
        }

        .cook-footer .btn {
            flex: 1;
            max-width: 200px;
        }

        .cook-ingredients-panel,
        .cook-steps-panel {
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }

        .cook-ingredients-panel {
            flex: 0 0 35%;
            border-right: 2px solid var(--bs-border-color, #dee2e6);
            background: var(--bs-secondary-bg, #f8f9fa);
        }

        .cook-steps-panel {
            flex: 1;
            background: var(--bs-body-bg, white);
        }

        .cook-panel-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            margin-top: 0;
        }

        .cook-ingredients-list {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .cook-ingredient-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
            cursor: default;
            line-height: 1.5;
        }

        .cook-ingredient-item:hover {
            background: var(--bs-tertiary-bg, rgba(0, 0, 0, 0.05));
        }

        .cook-ingredient-check-icon {
            flex-shrink: 0;
            width: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 0.1rem;
        }

        .cook-ingredient-content {
            flex: 1;
            font-size: 1.05rem;
        }

        .cook-ingredient-content strong {
            font-weight: 600;
        }

        .cook-steps-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .cook-step-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 1rem;
            border-radius: 0.5rem;
            border-left: 4px solid transparent;
            transition: all 0.2s;
            cursor: pointer;
            background: var(--bs-secondary-bg, #f8f9fa);
            margin-bottom: 0.75rem;
            scroll-margin-top: 1rem;
        }

        .cook-step-item:hover {
            background: var(--bs-tertiary-bg, #e9ecef);
        }

        .cook-step-item.cook-step-active {
            background: var(--bs-primary-bg-subtle, rgba(13, 110, 253, 0.1));
            border-left-color: var(--bs-primary, #0d6efd);
        }

        [data-bs-theme="dark"] .cook-step-item.cook-step-active {
            background: rgba(13, 110, 253, 0.15);
            border-left-color: #0d6efd;
        }

        .cook-step-number {
            display: block;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--bs-primary, #0d6efd);
        }

        .cook-step-content {
            display: block;
        }

        /* Responsive: Mobile bottom sheet */
        @media (max-width: 768px) {
            .cook-body {
                flex-direction: column;
                gap: 0;
            }

            .cook-ingredients-panel {
                flex: 0 0 auto;
                border-right: none;
                border-bottom: 2px solid var(--bs-border-color, #dee2e6);
                max-height: 30vh;
                order: -1;
                overflow-y: auto;
                padding: 0.5rem 1rem;
            }

            .cook-panel-title {
                display: none;
            }

            .cook-steps-panel {
                flex: 1;
                overflow-y: auto;
            }

            .cook-header {
                padding: 0.75rem 1rem;
                gap: 0.5rem;
            }

            .cook-header h3 {
                font-size: 1.1rem;
                line-height: 1.3;
            }

            .cook-header-buttons .btn {
                padding: 0.4rem 0.6rem;
                font-size: 0.9rem;
            }

            .cook-footer {
                padding: 0.75rem 1rem;
                gap: 0.5rem;
            }

            .cook-footer .btn {
                flex: 1;
                max-width: none;
                font-size: 0.9rem;
                padding: 0.5rem 0.75rem;
            }

            .cook-ingredient-item {
                padding: 0.35rem 0.25rem;
                font-size: 0.95rem;
            }

            .cook-ingredient-check-icon {
                width: 1rem;
            }

            .cook-ingredient-check-icon i {
                font-size: 0.85rem;
            }

            .cook-step-item {
                padding: 0.85rem;
                font-size: 1rem;
                margin-bottom: 0.5rem;
            }

            .cook-step-number {
                font-size: 0.9rem;
                min-width: 2rem;
            }

            .cook-steps-list {
                gap: 1rem;
            }
        }
    </style>

    @if($recipe->source_url)
        <div class="container py-3">
            <div class="border-top pt-3 text-muted" style="font-size: 0.95rem;">
                <i class="fa-solid fa-link me-2"></i>
                <span>Source:</span>
                <a href="{{ $recipe->source_url }}" target="_blank" rel="noopener noreferrer" class="text-muted text-decoration-none">{{ parse_url($recipe->source_url, PHP_URL_HOST) ?: $recipe->source_url }}</a>
            </div>
        </div>
    @endif

    <!-- Cook Mode Modal -->
    <div id="cook-mode-modal" class="cook-modal" style="display: none;">
        <div class="cook-header">
            <h3 class="mb-0">{{ $recipe->title }}</h3>
            <div class="cook-header-buttons">
                <button onclick="toggleCookDarkMode()" class="btn btn-sm btn-outline-secondary" title="Toggle Dark Mode">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <button onclick="exitCookMode()" class="btn btn-sm btn-outline-danger">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="cook-body">
            <!-- Ingredients Panel -->
            <div class="cook-ingredients-panel">
                <h5 class="cook-panel-title">Ingredients for This Step</h5>
                <div class="cook-ingredients-list" id="cook-current-ingredients">
                    <p class="text-muted small">Select a step to see required ingredients</p>
                </div>
            </div>
            
            <!-- Steps Panel -->
            <div class="cook-steps-panel">
                <h5 class="cook-panel-title">Method</h5>
                <div class="cook-steps-list" id="cook-steps-list">
                    @forelse($recipe->directions as $idx => $dir)
                        <div class="cook-step-item" data-step-index="{{ $idx }}" data-step-text="{{ strtolower($dir->body) }}" onclick="selectCookStep({{ $idx }})">
                            <div class="cook-step-number">{{ $idx + 1 }}</div>
                            <div class="cook-step-content">
                                <label class="cook-step-label">
                                    {!! nl2br(e($dir->body)) !!}
                                </label>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No steps</p>
                    @endforelse
                </div>
            </div>
        </div>
        
        <div class="cook-footer">
            <button onclick="previousStep()" id="cook-prev-btn" class="btn btn-primary" title="Previous Step">
                <i class="fa-solid fa-chevron-left me-1"></i>Previous
            </button>
            <button onclick="nextStep()" id="cook-next-btn" class="btn btn-primary" title="Next Step">
                Next<i class="fa-solid fa-chevron-right ms-1"></i>
            </button>
        </div>
    </div>

</x-app-layout>
