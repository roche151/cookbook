<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('category_recipe', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'recipe_id']);
        });

        // Seed the fixed categories
        $now = now();
        DB::table('categories')->insert([
            ['name' => 'Breakfast', 'slug' => 'breakfast', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Lunch', 'slug' => 'lunch', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Dinner', 'slug' => 'dinner', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Snack', 'slug' => 'snack', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Dessert', 'slug' => 'dessert', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('category_recipe');
        Schema::dropIfExists('categories');
    }
};
