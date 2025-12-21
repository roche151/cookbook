<x-app-layout>
    <x-slot name="title">{{ $title ?? 'Recipes' }}</x-slot>

    <div class="container py-5">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $title ?? 'Recipes' }}</li>
            </ol>
        </nav>

        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fa-solid fa-book-open text-primary"></i>
                <h1 class="h4 mb-0">{{ $title ?? 'All Recipes' }}</h1>
            </div>
            <p class="text-muted mb-0">{{ $subtitle ?? 'Discover and explore delicious recipes' }}</p>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-body-secondary border-0">
                <button class="btn btn-link text-decoration-none w-100 text-start p-0 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="{{ ($q || !empty($selectedTags) || ($sort ?? 'date_desc') !== 'date_desc' || ($ratingMin ?? 0) > 0) ? 'true' : 'false' }}" aria-controls="filterCollapse">
                    <span class="fw-semibold fs-6">Filters</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
            </div>
            <div id="filterCollapse" class="collapse {{ ($q || !empty($selectedTags) || ($sort ?? 'date_desc') !== 'date_desc' || ($ratingMin ?? 0) > 0) ? 'show' : '' }}">
                <div class="card-body p-4">
                        <form method="GET">
                            <div class="row g-3">
                                <!-- Search Box -->
                                <div class="col-md-12 col-lg-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fa-solid fa-magnifying-glass me-1 text-primary"></i>Search
                                    </label>
                                    <input type="text" name="q" class="form-control" placeholder="Search recipes, ingredients..." value="{{ $q ?? '' }}">
                                </div>

                                <!-- Sort Dropdown -->
                                <div class="col-md-6 col-lg-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fa-solid fa-arrow-down-short-wide me-1 text-primary"></i>Sort By
                                    </label>
                                    <select name="sort" class="form-select">
                                        <option value="date_desc" {{ ($sort ?? 'date_desc') === 'date_desc' ? 'selected' : '' }}>Newest First</option>
                                        <option value="date_asc" {{ ($sort ?? '') === 'date_asc' ? 'selected' : '' }}>Oldest First</option>
                                        <option value="rating_desc" {{ ($sort ?? '') === 'rating_desc' ? 'selected' : '' }}>Highest Rated</option>
                                        <option value="time_asc" {{ ($sort ?? '') === 'time_asc' ? 'selected' : '' }}>Quickest First</option>
                                        <option value="time_desc" {{ ($sort ?? '') === 'time_desc' ? 'selected' : '' }}>Longest First</option>
                                        <option value="title_asc" {{ ($sort ?? '') === 'title_asc' ? 'selected' : '' }}>Title A-Z</option>
                                        <option value="title_desc" {{ ($sort ?? '') === 'title_desc' ? 'selected' : '' }}>Title Z-A</option>
                                    </select>
                                </div>

                                <!-- Rating Filter -->
                                <div class="col-md-6 col-lg-3">
                                    <label class="form-label fw-semibold d-block">
                                        <i class="fa-solid fa-star me-1 text-primary"></i>Minimum Rating
                                    </label>
                                    @php $ratingMinValue = (int) ($ratingMin ?? 0); @endphp
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rating-filter-stars d-flex align-items-center gap-2" role="radiogroup" aria-label="Minimum rating">
                                            <input type="radio" name="rating_min" value="0" class="d-none rating-filter-input rating-reset" {{ $ratingMinValue === 0 ? 'checked' : '' }}>
                                            @for($i = 1; $i <= 5; $i++)
                                                <label class="mb-0 d-flex align-items-center" style="cursor: pointer; line-height: 1;">
                                                    <input type="radio" name="rating_min" value="{{ $i }}" class="d-none rating-filter-input" {{ $ratingMinValue === $i ? 'checked' : '' }}>
                                                    <i class="fa-star rating-filter-star {{ $ratingMinValue >= $i ? 'fa-solid' : 'fa-regular' }}"></i>
                                                </label>
                                            @endfor
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rating-clear" {{ $ratingMinValue === 0 ? 'disabled' : '' }}>Any rating</button>
                                    </div>
                                </div>

                                <!-- Tag Filters -->
                                @if(isset($allTags) && $allTags->count())
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">
                                            <i class="fa-solid fa-tags me-1 text-primary"></i>Tags
                                        </label>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($allTags as $tag)
                                                <div class="form-check mb-0 ps-0">
                                                    <input 
                                                    hidden
                                                        class="form-check-input" 
                                                        type="checkbox" 
                                                        name="tags[]" 
                                                        value="{{ $tag->id }}" 
                                                        id="tag-{{ $tag->id }}"
                                                        {{ in_array($tag->id, $selectedTags ?? []) ? 'checked' : '' }}
                                                    >
                                                    <label class="form-check-label badge bg-opacity-25 border border-secondary py-2 px-3 {{ in_array($tag->id, $selectedTags ?? []) ? 'bg-primary text-white' : 'bg-secondary border-secondary-subtle' }}" for="tag-{{ $tag->id }}" style="cursor: pointer; font-weight: 400;">
                                                        @if($tag->icon)
                                                            <i class="{{ $tag->icon }} me-1"></i>
                                                        @endif
                                                        {{ $tag->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Buttons -->
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary px-4" type="submit">
                                            <i class="fa-solid fa-search me-2"></i>Apply Filters
                                        </button>
                                        @if($q || !empty($selectedTags) || ($sort ?? 'date_desc') !== 'date_desc' || ($ratingMin ?? 0) > 0)
                                            <a href="{{ url()->current() }}" class="btn btn-outline-secondary px-4">
                                                <i class="fa-solid fa-times me-2"></i>Clear All
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        </div>

        @if($recipes->count())
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                @foreach($recipes as $recipe)
                    <div class="col">
                        @include('recipes._card', ['recipe' => $recipe])
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $recipes->links() }}
            </div>
        @else
            <div class="alert alert-info border-0 shadow-sm">
                <i class="fa-solid fa-info-circle me-2"></i>{{ $emptyMessage ?? 'No recipes found.' }}
            </div>
        @endif
    </div>

    <style>
        /* Prevent horizontal jump when scrollbar appears */
        html {
            overflow-y: scroll;
        }
        
        .rating-filter-star {
            font-size: 1.35rem;
            color: #f28c38;
            transition: transform 0.1s ease-in-out;
            display: inline-block;
            vertical-align: middle;
        }

        .rating-filter-star:hover {
            transform: scale(1.05);
        }

        /* Smooth collapse animation */
        #filterCollapse {
            transition: height 0.3s ease;
        }
        
        [data-bs-toggle="collapse"] .fa-chevron-down {
            transition: transform 0.3s ease;
        }
        [data-bs-toggle="collapse"][aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }
    </style>

    <script type="module">
        document.addEventListener('DOMContentLoaded', function() {
            // Check if Bootstrap is loaded
            if (typeof window.bootstrap !== 'undefined') {
                console.log('Bootstrap is loaded');
            } else {
                console.error('Bootstrap is NOT loaded');
            }
            
            // Try to manually initialize the collapse
            const collapseElement = document.getElementById('filterCollapse');
            if (collapseElement) {
                // Remove any existing collapse instance
                const existingInstance = window.bootstrap?.Collapse?.getInstance(collapseElement);
                if (existingInstance) {
                    existingInstance.dispose();
                }
                
                // Create new instance
                if (window.bootstrap?.Collapse) {
                    new window.bootstrap.Collapse(collapseElement, {
                        toggle: false
                    });
                    console.log('Collapse initialized');
                }
            }

            // Rating filter interaction (single row of stars + clear)
            const ratingInputs = document.querySelectorAll('.rating-filter-input');
            const clearButton = document.querySelector('.rating-clear');

            function syncStars(value) {
                ratingInputs.forEach((input) => {
                    const star = input.nextElementSibling;
                    if (!star) return;
                    const v = parseInt(input.value, 10);
                    if (value >= v) {
                        star.classList.remove('fa-regular');
                        star.classList.add('fa-solid');
                    } else {
                        star.classList.remove('fa-solid');
                        star.classList.add('fa-regular');
                    }
                });
                if (clearButton) {
                    clearButton.disabled = value === 0;
                }
            }

            ratingInputs.forEach((input) => {
                input.addEventListener('change', () => {
                    syncStars(parseInt(input.value, 10));
                });

                // Hover feedback to preview selection
                const star = input.nextElementSibling;
                if (star) {
                    star.addEventListener('mouseenter', () => syncStars(parseInt(input.value, 10)));
                    star.addEventListener('mouseleave', () => {
                        const checked = document.querySelector('.rating-filter-input:checked');
                        syncStars(checked ? parseInt(checked.value, 10) : 0);
                    });
                }
            });

            if (clearButton) {
                clearButton.addEventListener('click', () => {
                    const checked = document.querySelector('.rating-filter-input:checked');
                    if (checked) {
                        checked.checked = false;
                    }
                    const hiddenReset = document.querySelector('input[name="rating_min"][value="0"]');
                    if (hiddenReset) {
                        hiddenReset.checked = true;
                    }
                    syncStars(0);
                });
            }

            // Ensure initial state matches current selection
            const initialChecked = document.querySelector('.rating-filter-input:checked');
            syncStars(initialChecked ? parseInt(initialChecked.value, 10) : 0);
        });
    </script>

</x-app-layout>