<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecipesController;
use App\Http\Controllers\CollectionsController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\RecipeModerationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
Route::get('/', [HomeController::class, 'index']);
// Dynamic sitemap.xml
Route::get('/sitemap.xml', [SitemapController::class, 'index']);

// Dev-only quick login endpoint to impersonate any user
if (!app()->environment('production')) {
    Route::post('/dev/quick-login', function (Request $request) {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        Auth::loginUsingId($data['user_id']);
        $request->session()->regenerate();

        return redirect()->intended('/');
    })->name('dev.quick-login');
}

// Public recipe routes (anyone can view)
Route::get('/recipes', [RecipesController::class, 'index'])->name('recipes.index');
Route::get('/recipes/{recipe}/pdf', [RecipesController::class, 'downloadPdf'])->name('recipes.pdf');

// Protected recipe routes (must be logged in and verified email) - define /create BEFORE /{recipe}
use App\Http\Controllers\FeedbackController;
Route::middleware(['auth', 'verified'])->group(function () {
        // Feedback creation route
        Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/dashboard', function () {
        return redirect()->route('recipes.my');
    })->name('dashboard');
    
    Route::get('/my-recipes', [RecipesController::class, 'myRecipes'])->name('recipes.my');
    
    // Collections
    Route::get('/my-collections', [CollectionsController::class, 'index'])->name('collections.index');
    Route::post('/collections', [CollectionsController::class, 'store'])->name('collections.store');
    Route::get('/my-collections/{collection}', [CollectionsController::class, 'show'])->name('collections.show');
    Route::patch('/collections/{collection}', [CollectionsController::class, 'update'])->name('collections.update');
    Route::delete('/collections/{collection}', [CollectionsController::class, 'destroy'])->name('collections.destroy');
    Route::post('/recipes/{recipe}/add-to-collection', [CollectionsController::class, 'addRecipe'])->name('recipes.add-to-collection');
    Route::delete('/collections/{collection}/recipes/{recipe}', [CollectionsController::class, 'removeRecipe'])->name('collections.remove-recipe');
    Route::get('/recipes/{recipe}/collections', [CollectionsController::class, 'forRecipe'])->name('recipes.collections');
    
    Route::post('/recipes/{recipe}/rate', [RecipesController::class, 'storeRating'])->name('recipes.rate');
    Route::get('/recipes/create', [RecipesController::class, 'create'])->name('recipes.create');
    Route::post('/recipes/import', [RecipesController::class, 'importFromUrl'])->name('recipes.import');
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
    Route::patch('/shopping-list/mark-all-checked', [ShoppingListController::class, 'markAllChecked'])->name('shopping-list.items.mark-all-checked');
    Route::post('/shopping-list/add-recipe/{recipe}', [ShoppingListController::class, 'addFromRecipe'])->name('shopping-list.add-from-recipe');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationsController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationsController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationsController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationsController::class, 'destroy'])->name('notifications.destroy');

    // Admin-only area
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        // Admin view all feedback
        Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
        Route::get('/', [AdminController::class, 'index'])->name('index');
        // Recipe moderation
        Route::get('/moderation/recipes', [RecipeModerationController::class, 'index'])->name('moderation.recipes.index');
        Route::get('/moderation/recipes/{revision}', [RecipeModerationController::class, 'show'])->name('moderation.recipes.show');
        Route::post('/moderation/recipes/{revision}/approve', [RecipeModerationController::class, 'approve'])->name('moderation.recipes.approve');
        Route::post('/moderation/recipes/{revision}/reject', [RecipeModerationController::class, 'reject'])->name('moderation.recipes.reject');
        // User management
        Route::get('/users', [AdminController::class, 'listUsers'])->name('users.index');
        Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
        Route::patch('/users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('users.toggle-admin');
        Route::patch('/users/{user}/toggle-verified', [AdminController::class, 'toggleVerified'])->name('users.toggle-verified');
        // Feedback
        Route::get('/feedback', [AdminController::class, 'viewFeedback'])->name('feedback.index');
    });
});

// Public recipe show route - define AFTER /create to avoid conflicts
Route::get('/recipes/{recipe}', [RecipesController::class, 'show'])->name('recipes.show');

require __DIR__.'/auth.php';
