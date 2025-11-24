@php
    $recipes = collect([
        ['id' => 1, 'title' => 'Chocolate Cake', 'category' => 'dessert', 'excerpt' => 'Rich and moist chocolate cake.'],
        ['id' => 3, 'title' => 'Spaghetti Carbonara', 'category' => 'dinner', 'excerpt' => 'Classic Italian pasta with pancetta.'],
        ['id' => 2, 'title' => 'Avocado Toast', 'category' => 'breakfast', 'excerpt' => 'Simple avocado toast with lemon.'],
    ]);
@endphp

<div class="mb-5">
    <h3 class="h5">Featured recipes</h3>
    <div class="row mt-3">
        @foreach($recipes as $r)
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $r['title'] }}</h5>
                        <p class="text-muted small">{{ ucfirst($r['category']) }}</p>
                        <p class="card-text flex-grow-1">{{ $r['excerpt'] }}</p>
                        <div class="mt-3 text-end">
                            <a href="{{ url('/recipes/'.$r['id']) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@props(['recipes' => null, 'title' => 'Featured Recipes', 'seeAllUrl' => '/recipes'])

@php
    if (is_null($recipes)) {
        // Load latest 3 recipes from DB if available
        try {
            $recipes = \App\Models\Recipe::orderBy('created_at', 'desc')->take(3)->get()->map(function ($r) {
                return [
                    'title' => $r->title,
                    'image' => $r->image,
                    'excerpt' => $r->excerpt,
                    'href' => url('/recipes/'.$r->id),
                    'meta' => ($r->category ? ucfirst($r->category).' • ' : '') . ($r->time ?? ''),
                    'rating' => $r->rating,
                ];
            })->toArray();
        } catch (\Throwable $e) {
            // Fall back to static examples when DB is not ready
            $recipes = [
                [
                    'title' => 'Smashed Avocado Toast',
                    'image' => 'https://images.unsplash.com/photo-1512058564366-c9e3f0a0a6b7?w=800&q=60&auto=format&fit=crop',
                    'excerpt' => 'Creamy smashed avocado with lemon, olive oil and chili flakes — a simple morning favorite.',
                    'href' => '/recipes/smashed-avocado-toast',
                    'meta' => 'Breakfast • 10 mins',
                    'rating' => '4.8',
                ],
                [
                    'title' => 'Creamy Lemon Pasta',
                    'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=60&auto=format&fit=crop',
                    'excerpt' => 'Bright lemon, parmesan and fresh herbs make this a weeknight staple.',
                    'href' => '/recipes/lemon-pasta',
                    'meta' => 'Dinner • 20 mins',
                    'rating' => '4.7',
                ],
                [
                    'title' => 'Decadent Chocolate Cake',
                    'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&q=60&auto=format&fit=crop',
                    'excerpt' => 'Rich chocolate layers with silky frosting — perfect for celebrations.',
                    'href' => '/recipes/chocolate-cake',
                    'meta' => 'Dessert • 1 hr',
                    'rating' => '4.9',
                ],
            ];
        }
    }
@endphp

<div class="mb-4 d-flex justify-content-between align-items-center">
    <h2 class="h4 mb-0">{{ $title }}</h2>
    <a href="{{ $seeAllUrl }}" class="small">See all</a>
</div>

<div class="row g-4 mb-5">
    @foreach($recipes as $r)
        <div class="col-md-4">
            <x-recipe-card
                :title="$r['title']"
                :image="$r['image'] ?? null"
                :excerpt="$r['excerpt'] ?? null"
                :href="$r['href'] ?? (isset($r['id']) ? url('/recipes/'.$r['id']) : null)"
                :meta="$r['meta'] ?? null"
                :rating="$r['rating'] ?? null"
            />
        </div>
    @endforeach
</div>
