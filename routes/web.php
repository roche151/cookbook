<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecipesController;
use Illuminate\Support\Facades\Route;
use App\Models\Tag;

Route::get('/', function () {
    $tags = Tag::orderBy('sort_order')->orderBy('name')->get();
    return view('home', ['tags' => $tags]);
});

// Public recipe routes (anyone can view)
Route::get('/recipes', [RecipesController::class, 'index'])->name('recipes.index');

// Protected recipe routes (must be logged in) - define /create BEFORE /{recipe}
Route::middleware('auth')->group(function () {
    Route::get('/my-recipes', [RecipesController::class, 'myRecipes'])->name('recipes.my');
    Route::get('/my-favorites', [RecipesController::class, 'myFavorites'])->name('recipes.favorites');
    Route::post('/recipes/{recipe}/favorite', [RecipesController::class, 'toggleFavorite'])->name('recipes.favorite');
    Route::get('/recipes/create', [RecipesController::class, 'create'])->name('recipes.create');
    Route::post('/recipes', [RecipesController::class, 'store'])->name('recipes.store');
    Route::get('/recipes/{recipe}/edit', [RecipesController::class, 'edit'])->name('recipes.edit');
    Route::patch('/recipes/{recipe}', [RecipesController::class, 'update'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [RecipesController::class, 'destroy'])->name('recipes.destroy');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public recipe show route - define AFTER /create to avoid conflicts
Route::get('/recipes/{recipe}', [RecipesController::class, 'show'])->name('recipes.show');

require __DIR__.'/auth.php';
