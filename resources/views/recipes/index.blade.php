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
            <h1 class="h3 mb-2">
                <i class="fa-solid fa-book-open me-2 text-primary"></i>{{ $title ?? 'All Recipes' }}
            </h1>
            <p class="text-muted mb-0">{{ $subtitle ?? 'Discover and explore delicious recipes' }}</p>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-body-secondary border-0">
                <button class="btn btn-link text-decoration-none w-100 text-start p-0 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="{{ ($q || !empty($selectedTags) || ($sort ?? 'date_desc') !== 'date_desc') ? 'true' : 'false' }}" aria-controls="filterCollapse">
                    <span class="fw-semibold fs-6">
                        <i class="fa-solid fa-sliders me-2 text-primary"></i>Filters & Search
                    </span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
            </div>
            <div id="filterCollapse" class="collapse {{ ($q || !empty($selectedTags) || ($sort ?? 'date_desc') !== 'date_desc') ? 'show' : '' }}">
                <div class="card-body p-4">
                        <form method="GET">
                            <div class="row g-3">
                                <!-- Search Box -->
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">
                                        <i class="fa-solid fa-magnifying-glass me-1 text-primary"></i>Search
                                    </label>
                                    <input type="text" name="q" class="form-control" placeholder="Search recipes, ingredients..." value="{{ $q ?? '' }}">
                                </div>

                                <!-- Sort Dropdown -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fa-solid fa-arrow-down-short-wide me-1 text-primary"></i>Sort By
                                    </label>
                                    <select name="sort" class="form-select">
                                        <option value="date_desc" {{ ($sort ?? 'date_desc') === 'date_desc' ? 'selected' : '' }}>Newest First</option>
                                        <option value="date_asc" {{ ($sort ?? '') === 'date_asc' ? 'selected' : '' }}>Oldest First</option>
                                        <option value="time_asc" {{ ($sort ?? '') === 'time_asc' ? 'selected' : '' }}>Quickest First</option>
                                        <option value="time_desc" {{ ($sort ?? '') === 'time_desc' ? 'selected' : '' }}>Longest First</option>
                                        <option value="title_asc" {{ ($sort ?? '') === 'title_asc' ? 'selected' : '' }}>Title A-Z</option>
                                        <option value="title_desc" {{ ($sort ?? '') === 'title_desc' ? 'selected' : '' }}>Title Z-A</option>
                                    </select>
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
                                        @if($q || !empty($selectedTags) || ($sort ?? 'date_desc') !== 'date_desc')
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
        });
    </script>

</x-app-layout>