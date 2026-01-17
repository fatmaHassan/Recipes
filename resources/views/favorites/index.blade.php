<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Favorites') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($favorites->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($favorites as $savedRecipe)
                        @php
                            $recipe = $savedRecipe->recipe_data;
                        @endphp
                        <div class="card card-hover overflow-hidden border-red-200">
                            @if(isset($recipe['strMealThumb']))
                                <img src="{{ $recipe['strMealThumb'] }}" alt="{{ $recipe['strMeal'] ?? 'Recipe' }}" class="w-full h-48 object-cover">
                            @endif
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold text-lg text-gray-800">{{ $recipe['strMeal'] ?? 'Recipe' }}</h3>
                                    <span class="text-red-500 text-2xl">❤️</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">Added to favorites {{ $savedRecipe->updated_at->diffForHumans() }}</p>
                                <div class="flex gap-2">
                                    <a href="{{ route('recipes.show', $savedRecipe->recipe_id) }}" class="flex-1 btn-primary text-center">
                                        View Recipe
                                    </a>
                                    <form method="POST" action="{{ route('recipes.favorite', $savedRecipe->recipe_id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-favorite active" title="Remove from favorites">
                                            <span class="emoji-md">❤️</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card p-8 text-center">
                    <div class="text-6xl mb-4">❤️</div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">No favorites yet</h3>
                    <p class="text-gray-600 mb-6">Mark recipes as favorites to see them here!</p>
                    <a href="{{ route('dashboard') }}" class="btn-primary">
                        Go to Dashboard →
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
