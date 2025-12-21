<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all users who have favorites
        $userIds = DB::table('favorite_recipe')
            ->distinct()
            ->pluck('user_id');
        
        // Create a "Favorites" collection for each user and migrate their favorites
        foreach ($userIds as $userId) {
            // Create default collection
            $collectionId = DB::table('collections')->insertGetId([
                'user_id' => $userId,
                'name' => 'Favorites',
                'description' => 'Your favorite recipes',
                'slug' => 'favorites-' . $userId . '-' . \Illuminate\Support\Str::random(5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Get all favorites for this user
            $favorites = DB::table('favorite_recipe')
                ->where('user_id', $userId)
                ->get();
            
            // Move to collection_recipe
            foreach ($favorites as $favorite) {
                DB::table('collection_recipe')->insert([
                    'collection_id' => $collectionId,
                    'recipe_id' => $favorite->recipe_id,
                    'created_at' => $favorite->created_at,
                    'updated_at' => $favorite->updated_at,
                ]);
            }
        }
        
        // Drop the old favorite_recipe table
        Schema::dropIfExists('favorite_recipe');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate favorite_recipe table
        Schema::create('favorite_recipe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['user_id', 'recipe_id']);
        });
        
        // Migrate first collection back to favorites for each user
        $collections = DB::table('collections')
            ->orderBy('created_at')
            ->get();
        
        foreach ($collections as $collection) {
            $recipes = DB::table('collection_recipe')
                ->where('collection_id', $collection->id)
                ->get();
            
            foreach ($recipes as $recipe) {
                DB::table('favorite_recipe')->insert([
                    'user_id' => $collection->user_id,
                    'recipe_id' => $recipe->recipe_id,
                    'created_at' => $recipe->created_at,
                    'updated_at' => $recipe->updated_at,
                ]);
            }
        }
    }
};
