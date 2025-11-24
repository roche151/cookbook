<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecipesController;

use App\Models\Tag;

Route::get('/', function () {
    $tags = Tag::orderBy('sort_order')->orderBy('name')->get();
    return view('home', ['tags' => $tags]);
});

// Recipes listing, search and creation routes
Route::get('/recipes', [RecipesController::class, 'index'])->name('recipes.index');
Route::get('/recipes/create', [RecipesController::class, 'create'])->name('recipes.create');
Route::post('/recipes', [RecipesController::class, 'store'])->name('recipes.store');
Route::get('/recipes/{id}', [RecipesController::class, 'show'])->name('recipes.show');
