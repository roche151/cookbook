<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            // Dietary tags
            ['name' => 'Vegan', 'slug' => 'vegan', 'sort_order' => 10, 'icon' => 'fa-solid fa-carrot'],
            ['name' => 'Vegetarian', 'slug' => 'vegetarian', 'sort_order' => 11, 'icon' => 'fa-solid fa-leaf'],
            ['name' => 'Gluten Free', 'slug' => 'gluten-free', 'sort_order' => 12, 'icon' => 'fa-solid fa-bread-slice'],
            ['name' => 'Dairy Free', 'slug' => 'dairy-free', 'sort_order' => 13, 'icon' => 'fa-solid fa-droplet-slash'],
            ['name' => 'Nut Free', 'slug' => 'nut-free', 'sort_order' => 14, 'icon' => 'fa-solid fa-shield-halved'],
            ['name' => 'Low Carb', 'slug' => 'low-carb', 'sort_order' => 15, 'icon' => 'fa-solid fa-drumstick-bite'],
            ['name' => 'Keto', 'slug' => 'keto', 'sort_order' => 16, 'icon' => 'fa-solid fa-egg'],
            ['name' => 'Paleo', 'slug' => 'paleo', 'sort_order' => 17, 'icon' => 'fa-solid fa-bone'],
            
            // Category/characteristic tags
            ['name' => 'Quick & Easy', 'slug' => 'quick-easy', 'sort_order' => 20, 'icon' => 'fa-solid fa-bolt'],
            ['name' => 'Comfort Food', 'slug' => 'comfort-food', 'sort_order' => 21, 'icon' => 'fa-solid fa-heart'],
            ['name' => 'Healthy', 'slug' => 'healthy', 'sort_order' => 22, 'icon' => 'fa-solid fa-heart-pulse'],
            ['name' => 'Spicy', 'slug' => 'spicy', 'sort_order' => 23, 'icon' => 'fa-solid fa-pepper-hot'],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(
                ['slug' => $tag['slug']], 
                $tag
            );
        }
    }
}
