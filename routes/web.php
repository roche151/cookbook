<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecipesController;

use App\Models\Tag;

Route::get('/', function () {
    $tags = Tag::orderBy('sort_order')->orderBy('name')->get();
    return view('home', ['tags' => $tags]);
});

// Recipes resourceful routes (uses route-model binding)
Route::resource('recipes', RecipesController::class);
