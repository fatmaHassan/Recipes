<?php

use App\Http\Controllers\AllergyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\MyRecipesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication routes (from Breeze)
require __DIR__.'/auth.php';

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Ingredients
    Route::resource('ingredients', IngredientController::class);
    
    // Allergies
    Route::resource('allergies', AllergyController::class);
    
    // Recipes
    Route::post('/recipes/search', [RecipeController::class, 'search'])->name('recipes.search');
    Route::get('/recipes/{id}', [RecipeController::class, 'show'])->name('recipes.show');
    Route::post('/recipes/save', [RecipeController::class, 'save'])->name('recipes.save');
    Route::post('/recipes/{recipeId}/favorite', [RecipeController::class, 'toggleFavorite'])->name('recipes.favorite');
    
    // My Recipes
    Route::get('/my-recipes', [MyRecipesController::class, 'index'])->name('my-recipes.index');
    
    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
