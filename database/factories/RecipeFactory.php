<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    public function definition()
    {
        $title = $this->faker->sentence(3);
        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'description' => $this->faker->paragraph(),
            'image' => 'https://source.unsplash.com/featured/?food,'.rand(1,1000),
            // Store time as integer minutes
            'time' => $this->faker->randomElement([10,20,30,45,60]),
            'rating' => $this->faker->randomFloat(1, 3.5, 5.0),
        ];
    }
}
