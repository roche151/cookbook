<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecipesController;
use App\Http\Controllers\ShoppingListController;
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
    Route::get('/dashboard', function () {
        return redirect()->route('recipes.my');
    })->name('dashboard');
    
    Route::get('/my-recipes', [RecipesController::class, 'myRecipes'])->name('recipes.my');
    Route::get('/my-favorites', [RecipesController::class, 'myFavorites'])->name('recipes.favorites');
    Route::post('/recipes/{recipe}/favorite', [RecipesController::class, 'toggleFavorite'])->name('recipes.favorite');
    Route::post('/recipes/{recipe}/rate', [RecipesController::class, 'storeRating'])->name('recipes.rate');
    Route::get('/recipes/create', [RecipesController::class, 'create'])->name('recipes.create');
    Route::post('/recipes', [RecipesController::class, 'store'])->name('recipes.store');
    Route::get('/recipes/{recipe}/edit', [RecipesController::class, 'edit'])->name('recipes.edit');
    Route::patch('/recipes/{recipe}', [RecipesController::class, 'update'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [RecipesController::class, 'destroy'])->name('recipes.destroy');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Shopping list
    Route::get('/shopping-list', [ShoppingListController::class, 'index'])->name('shopping-list.index');
    Route::post('/shopping-list/items', [ShoppingListController::class, 'storeItem'])->name('shopping-list.items.store');
    Route::patch('/shopping-list/items/{item}/toggle', [ShoppingListController::class, 'toggleItem'])->name('shopping-list.items.toggle');
    Route::patch('/shopping-list/items/{item}', [ShoppingListController::class, 'updateItem'])->name('shopping-list.items.update');
    Route::delete('/shopping-list/items/{item}', [ShoppingListController::class, 'deleteItem'])->name('shopping-list.items.delete');
    Route::delete('/shopping-list/clear-checked', [ShoppingListController::class, 'clearChecked'])->name('shopping-list.items.clear-checked');
    Route::post('/shopping-list/add-recipe/{recipe}', [ShoppingListController::class, 'addFromRecipe'])->name('shopping-list.add-from-recipe');
});

// Public recipe show route - define AFTER /create to avoid conflicts
Route::get('/recipes/{recipe}', [RecipesController::class, 'show'])->name('recipes.show');

require __DIR__.'/auth.php';
