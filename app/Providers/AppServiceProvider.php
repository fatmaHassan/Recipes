<?php

namespace App\Providers;

use App\Contracts\RecipeRepository;
use App\Repositories\DatabaseRecipeRepository;
use App\Repositories\MealDbApiRecipeRepository;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RecipeRepository::class, function ($app) {
            return config('recipes.source') === 'db'
                ? $app->make(DatabaseRecipeRepository::class)
                : $app->make(MealDbApiRecipeRepository::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
