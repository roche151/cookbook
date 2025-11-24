<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecipesController;

Route::get('/', function () {
    return view('home');
});

// Recipes listing, search and creation routes
Route::get('/recipes', [RecipesController::class, 'index'])->name('recipes.index');
Route::get('/recipes/create', [RecipesController::class, 'create'])->name('recipes.create');
Route::post('/recipes', [RecipesController::class, 'store'])->name('recipes.store');
Route::get('/recipes/{id}', [RecipesController::class, 'show'])->name('recipes.show');
