<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AllergyController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\MyRecipesController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RecipeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes (no authentication required)
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::get('/recipes/random', [RecipeController::class, 'random'])->name('api.recipes.random');

// Protected API routes (authentication required)
Route::middleware(['auth:sanctum'])->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');
    
    // Ingredients
    Route::get('/ingredients', [IngredientController::class, 'index'])->name('api.ingredients.index');
    Route::post('/ingredients', [IngredientController::class, 'store'])->name('api.ingredients.store');
    Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy'])->name('api.ingredients.destroy');
    Route::get('/ingredients/search', [IngredientController::class, 'search'])->name('api.ingredients.search');
    Route::post('/ingredients/check', [IngredientController::class, 'check'])->name('api.ingredients.check');
    
    // Allergies
    Route::get('/allergies', [AllergyController::class, 'index'])->name('api.allergies.index');
    Route::post('/allergies', [AllergyController::class, 'store'])->name('api.allergies.store');
    Route::delete('/allergies/{allergy}', [AllergyController::class, 'destroy'])->name('api.allergies.destroy');
    
    // My Recipes (must be before /recipes routes to avoid conflicts)
    Route::get('/my-recipes', [MyRecipesController::class, 'index'])->name('api.my-recipes.index');
    
    // Recipes
    // Note: Only POST is used for search since it requires ingredients in the request body
    Route::post('/recipes/search', [RecipeController::class, 'search'])->name('api.recipes.search');
    Route::get('/recipes/{id}', [RecipeController::class, 'show'])->name('api.recipes.show');
    Route::post('/recipes/save', [RecipeController::class, 'save'])->name('api.recipes.save');
    Route::post('/recipes/{recipeId}/favorite', [RecipeController::class, 'toggleFavorite'])->name('api.recipes.favorite');
    
    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('api.favorites.index');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('api.profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('api.profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('api.profile.destroy');
});
