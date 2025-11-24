<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Recipe;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );

        // Seed recipes only when table is empty
        if (Recipe::count() === 0) {
            $recipes = [
                [
                    'title' => 'Spaghetti Carbonara',
                    'description' => 'Classic Roman pasta with eggs, pecorino, pancetta and black pepper.',
                    'image' => 'https://plus.unsplash.com/premium_photo-1677000666741-17c3c57139a2?q=80&w=1200&auto=format&fit=crop&ixlib=rb-4.1.0',
                    'time' => '25 min',
                    'rating' => 4.7,
                    'tags' => ['Italian', 'Pasta'],
                ],
                [
                    'title' => 'Margherita Pizza',
                    'description' => 'Neapolitan-style pizza topped with tomato, fresh mozzarella and basil.',
                    'image' => 'https://plus.unsplash.com/premium_photo-1667682942148-a0c98d1d70db?q=80&w=1200&auto=format&fit=crop&ixlib=rb-4.1.0',
                    'time' => '1 hr',
                    'rating' => 4.8,
                    'tags' => ['Italian', 'Pizza', 'Vegetarian'],
                ],
                [
                    'title' => 'Chicken Tikka Masala',
                    'description' => 'Tender chicken pieces in a creamy, spiced tomato sauce.',
                    'image' => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?q=80&w=1200&auto=format&fit=crop&ixlib=rb-4.1.0',
                    'time' => '45 min',
                    'rating' => 4.6,
                    'tags' => ['Indian', 'Curry'],
                ],
                [
                    'title' => 'Beef Bourguignon',
                    'description' => 'Slow-braised beef in red wine with mushrooms and pearl onions.',
                    'image' => 'https://images.unsplash.com/photo-1548946526-f69e2424cf45?q=80&w=1200&auto=format&fit=crop&ixlib=rb-4.0.0',
                    'time' => '3 hr',
                    'rating' => 4.9,
                    'tags' => ['French', 'Stew'],
                ],
                [
                    'title' => 'Pad Thai',
                    'description' => 'Stir-fried rice noodles with tamarind, prawns, tofu and peanuts.',
                    'image' => 'https://plus.unsplash.com/premium_photo-1664472637341-3ec829d1f4df?q=80&w=725&auto=format&fit=crop&ixlib=rb-4.1.0',
                    'time' => '30 min',
                    'rating' => 4.5,
                    'tags' => ['Thai', 'Noodles'],
                ],
                [
                    'title' => 'Caesar Salad',
                    'description' => 'Crisp romaine with creamy Caesar dressing, croutons and parmesan.',
                    'image' => 'https://images.unsplash.com/photo-1558642452-9d2a7deb7f62?q=80&w=1200&auto=format&fit=crop&ixlib=rb-4.0.0',
                    'time' => '15 min',
                    'rating' => 4.2,
                    'tags' => ['Salad', 'Healthy'],
                ],
                [
                    'title' => 'Chocolate Brownies',
                    'description' => 'Fudgy, chocolatey brownies with a crackly top.',
                    'image' => 'https://plus.unsplash.com/premium_photo-1671379529629-6480c4953d14?q=80&w=1200&auto=format&fit=crop&ixlib=rb-4.1.0',
                    'time' => '40 min',
                    'rating' => 4.9,
                    'tags' => ['Dessert', 'Baking', 'Chocolate'],
                ],
                [
                    'title' => 'French Onion Soup',
                    'description' => 'Caramelized onion broth topped with toasted bread and melted gruyère.',
                    'image' => 'https://plus.unsplash.com/premium_photo-1727960325953-ef51e51d73f1?q=80&w=1200&auto=format&fit=crop&ixlib=rb-4.1.0',
                    'time' => '1 hr',
                    'rating' => 4.4,
                    'tags' => ['Soup', 'French', 'Starter'],
                ],
                [
                    'title' => 'Sushi Platter',
                    'description' => 'Assorted nigiri and maki rolls with fresh fish and seasoned rice.',
                    'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=1200&auto=format&fit=crop&ixlib=rb-4.0.0',
                    'time' => '1 hr',
                    'rating' => 4.8,
                    'tags' => ['Japanese', 'Seafood'],
                ],
                [
                    'title' => 'Fish and Chips',
                    'description' => 'Crispy beer-battered fish with golden fries and tartar sauce.',
                    'image' => 'https://plus.unsplash.com/premium_photo-1694108747175-889fdc786003?q=80&w=1200&auto=format&fit=crop&ixlib=rb-4.1.0',
                    'time' => '35 min',
                    'rating' => 4.3,
                    'tags' => ['British', 'Seafood'],
                ],
                [
                    'title' => 'Tacos al Pastor',
                    'description' => 'Marinated pork tacos with pineapple, onion and cilantro.',
                    'image' => 'https://plus.unsplash.com/premium_photo-1681406994521-82c20814605d?q=80&w=1200&auto=format&fit=crop&ixlib=rb-4.1.0',
                    'time' => '50 min',
                    'rating' => 4.7,
                    'tags' => ['Mexican', 'Street Food'],
                ],
                [
                    'title' => 'Vegetable Stir Fry',
                    'description' => 'Quick wok-fried seasonal vegetables in a savory soy-ginger sauce.',
                    'image' => 'https://plus.unsplash.com/premium_photo-1664478238082-3df93e48c491?q=80&w=1200&auto=format&fit=crop&ixlib=rb-4.1.0',
                    'time' => '20 min',
                    'rating' => 4.1,
                    'tags' => ['Vegetarian', 'Stir Fry', 'Asian'],
                ],
            ];

            foreach ($recipes as $data) {
                $slug = Str::slug($data['title']).'-'.Str::random(5);
                $data['slug'] = $slug;

                // Do not pass `tags` into the recipe table — store tags in the pivot table only.
                $tagsForAttach = [];
                if (isset($data['tags'])) {
                    $tagsForAttach = $data['tags'];
                    unset($data['tags']);
                }

                $recipe = Recipe::create($data);

                // If created here, attach tags now to ensure relation exists immediately.
                if (!empty($tagsForAttach) && $recipe) {
                    $tagIds = [];
                    foreach ($tagsForAttach as $tagName) {
                        // Find existing tag by name or slug; do NOT create new tags.
                        $tag = Tag::where('name', $tagName)
                            ->orWhere('slug', Str::slug($tagName))
                            ->first();
                        if ($tag) {
                            $tagIds[] = $tag->id;
                        }
                    }
                    if (! empty($tagIds)) {
                        $recipe->tags()->syncWithoutDetaching($tagIds);
                    }
                }
            }
        }

        // Ensure tags exist and attach them to each recipe (runs only when the seed list exists)
        if (isset($recipes) && is_array($recipes)) {
            foreach ($recipes as $data) {
            if (empty($data['tags']) || !is_array($data['tags'])) {
                continue;
            }

            $recipe = Recipe::where('title', $data['title'])->first();
            if (! $recipe) {
                continue;
            }

            $tagIds = [];
            foreach ($data['tags'] as $tagName) {
                // Only link to existing tags; do not create new ones.
                $tag = Tag::where('name', $tagName)
                    ->orWhere('slug', Str::slug($tagName))
                    ->first();
                if ($tag) {
                    $tagIds[] = $tag->id;
                }
            }

            if (! empty($tagIds)) {
                $recipe->tags()->syncWithoutDetaching($tagIds);
            }
            }
        }
    }
}
