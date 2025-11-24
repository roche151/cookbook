@props(['recipes' => null, 'title' => 'Featured Recipes', 'seeAllUrl' => '/recipes', 'count' => 3])

@php
    // Determine items to render. Support passing arrays/models via the component prop,
    // otherwise load from DB with a static fallback when DB isn't available.
    if (is_null($recipes)) {
        try {
            $items = \App\Models\Recipe::orderBy('created_at', 'desc')->take($count)->get();
        } catch (\Throwable $e) {
            $items = collect([
                (object)[
                    'title' => 'Smashed Avocado Toast',
                    'image' => 'https://images.unsplash.com/photo-1512058564366-c9e3f0a0a6b7?w=800&q=60&auto=format&fit=crop',
                    'excerpt' => 'Creamy smashed avocado with lemon, olive oil and chili flakes — a simple morning favorite.',
                    'id' => null,
                    'category' => 'breakfast',
                    'time' => '10 mins',
                    'rating' => '4.8',
                ],
                (object)[
                    'title' => 'Creamy Lemon Pasta',
                    'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=60&auto=format&fit=crop',
                    'excerpt' => 'Bright lemon, parmesan and fresh herbs make this a weeknight staple.',
                    'id' => null,
                    'category' => 'dinner',
                    'time' => '20 mins',
                    'rating' => '4.7',
                ],
                (object)[
                    'title' => 'Decadent Chocolate Cake',
                    'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&q=60&auto=format&fit=crop',
                    'excerpt' => 'Rich chocolate layers with silky frosting — perfect for celebrations.',
                    'id' => null,
                    'category' => 'dessert',
                    'time' => '1 hr',
                    'rating' => '4.9',
                ],
            ]);
        }
    } else {
        $items = collect($recipes);
    }

    // Determine column class based on count for a consistent grid
    if ($count >= 4) {
        $colClass = 'col-md-3';
    } elseif ($count === 3) {
        $colClass = 'col-md-4';
    } elseif ($count === 2) {
        $colClass = 'col-md-6';
    } else {
        $colClass = 'col-12';
    }
@endphp

<div class="mb-4 d-flex justify-content-between align-items-center">
    <h2 class="h4 mb-0">{{ $title }}</h2>
    <a href="{{ $seeAllUrl }}" class="small">See all</a>
</div>

@if($items->isEmpty())
    <div class="alert alert-secondary">No featured recipes yet.</div>
@else
    <div class="row g-4 mb-5">
        @foreach($items as $r)
            <div class="{{ $colClass }}">
                <x-recipe-card
                    :title="data_get($r, 'title')"
                    :image="data_get($r, 'image')"
                    :excerpt="data_get($r, 'excerpt')"
                    :href="data_get($r, 'href') ?? (data_get($r, 'id') ? url('/recipes/'.data_get($r,'id')) : null)"
                    :meta="data_get($r, 'meta') ?? (data_get($r,'category') ? ucfirst(data_get($r,'category')).' • '.(data_get($r,'time') ?? '') : null)"
                    :rating="data_get($r, 'rating')"
                />
            </div>
        @endforeach
    </div>
@endif
