@props(['recipes' => null, 'title' => 'Featured Recipes', 'seeAllUrl' => '/recipes'])

@php
    if (is_null($recipes)) {
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
@endphp

<div class="mb-4 d-flex justify-content-between align-items-center">
    <h2 class="h4 mb-0">{{ $title }}</h2>
    <a href="{{ $seeAllUrl }}" class="small">See all</a>
</div>

<div class="row g-4 mb-5">
    @foreach($recipes as $r)
        <div class="col-md-4">
            <x-recipe-card :title="$r['title']" :image="$r['image']" :excerpt="$r['excerpt']" :href="$r['href']" :meta="$r['meta']" :rating="$r['rating']" />
        </div>
    @endforeach
</div>
