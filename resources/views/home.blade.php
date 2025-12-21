<x-app-layout>
    <x-slot name="title">Home</x-slot>

    <!-- Hero Section -->
    <div class="bg-dark bg-opacity-10 border-bottom">
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-3">{{ config('app.name', 'Cookbook') }}</h1>
                    <p class="fs-5 text-muted mb-4">Organize recipes, discover new dishes, and keep favorites at your
                        fingertips.</p>

                    <form action="/recipes" method="GET" class="mb-4">
                        <div class="input-group input-group-lg shadow-sm">
                            <input name="q" type="search" class="form-control"
                                placeholder="Search recipes, ingredients..." aria-label="Search recipes">
                            <button class="btn btn-primary px-4" type="submit" aria-label="Search">
                                <i class="fa-solid fa-magnifying-glass me-2"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="fa-solid fa-lightbulb me-1"></i>Try: "pie", "chocolate"
                        </small>
                    </form>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="/recipes/create" class="btn btn-primary btn-lg">
                            <i class="fa-solid fa-plus me-2"></i>Create Recipe
                        </a>
                        <a href="/recipes" class="btn btn-outline-secondary btn-lg">
                            <i class="fa-solid fa-book me-2"></i>Browse All
                        </a>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="position-relative">
                        <img src="{{ asset('images/culina-hero.png') }}" alt="Culina hero illustration"
                            class="img-fluid rounded-4 shadow-lg"
                            style="max-height: 450px; width: 100%; object-fit: cover;">
                        <div class="position-absolute bottom-0 start-0 m-3 badge bg-dark bg-opacity-75 px-3 py-2">
                            <i class="fa-solid fa-fire me-1"></i> Trending Now
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <!-- Browse by Tag -->
        <div class="mb-5">
            <div class="d-flex align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1">Browse by Category</h2>
                    <p class="text-muted mb-0">Find recipes by your favorite meal types</p>
                </div>
            </div>

            @if (isset($tags) && $tags->count())
                <div class="row g-3">
                    @foreach ($tags as $tag)
                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="/recipes?tag={{ urlencode($tag->name) }}"
                                class="card h-100 text-decoration-none border category-card">
                                <div class="card-body text-center p-4">
                                    @if (!empty($tag->icon))
                                        <div class="mb-3 category-icon">
                                            <i class="{{ $tag->icon }} fa-2x text-primary" aria-hidden="true"></i>
                                        </div>
                                    @endif
                                    <h5 class="card-title mb-0 fw-semibold">{{ $tag->name }}</h5>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fa-solid fa-info-circle me-2"></i>No categories available yet.
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 bg-primary bg-opacity-10 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                                    style="width: 48px; height: 48px;">
                                    <i class="fa-solid fa-heart text-white"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="card-title mb-2">Save Favorites</h5>
                                <p class="card-text text-muted mb-3">Keep track of recipes you love</p>
                                <a href="/recipes" class="btn btn-sm btn-primary">View Recipes</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 bg-success bg-opacity-10 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded-circle bg-success d-flex align-items-center justify-content-center"
                                    style="width: 48px; height: 48px;">
                                    <i class="fa-solid fa-utensils text-white"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="card-title mb-2">My Recipes</h5>
                                <p class="card-text text-muted mb-3">Manage your personal collection</p>
                                <a href="/my-recipes" class="btn btn-sm btn-success">My Collection</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 bg-warning bg-opacity-10 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center"
                                    style="width: 48px; height: 48px;">
                                    <i class="fa-solid fa-star text-white"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="card-title mb-2">Top Rated</h5>
                                <p class="card-text text-muted mb-3">Explore community favorites</p>
                                <a href="/recipes?rating_min=4&sort=rating_desc" class="btn btn-sm btn-warning">Discover</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .category-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .category-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
            border-color: var(--bs-primary) !important;
        }

        .category-card .category-icon {
            transition: transform 0.3s ease;
        }

        .category-card:hover .category-icon {
            transform: scale(1.15);
        }
    </style>

</x-app-layout>
