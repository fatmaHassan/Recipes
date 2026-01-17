<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $recipe['strMeal'] ?? 'Recipe Details' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="p-6">
                    @if(session('success'))
                        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded mb-4">
                            ✅ {{ session('success') }}
                        </div>
                    @endif
                    @if(session('info'))
                        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded mb-4">
                            ℹ️ {{ session('info') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4">
                            ❌ {{ session('error') }}
                        </div>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        @if(isset($recipe['strMealThumb']))
                            <img src="{{ $recipe['strMealThumb'] }}" alt="{{ $recipe['strMeal'] ?? 'Recipe' }}" class="w-full rounded-lg">
                        @endif
                        <div>
                            <h1 class="text-3xl font-bold text-black mb-4">{{ $recipe['strMeal'] ?? 'Recipe' }}</h1>
                            
                            @if(isset($recipe['strCategory']))
                                <p class="text-gray-600 mb-2"><strong>Category:</strong> {{ $recipe['strCategory'] }}</p>
                            @endif
                            
                            @if(isset($recipe['strArea']))
                                <p class="text-gray-600 mb-2"><strong>Cuisine:</strong> {{ $recipe['strArea'] }}</p>
                            @endif

                            @auth
                                <div class="mt-4 flex items-center gap-3">
                                    @if(!$isSaved)
                                        <form method="POST" action="{{ route('recipes.save') }}" id="saveRecipeForm" class="inline">
                                            @csrf
                                            <input type="hidden" name="recipe_id" value="{{ $recipe['idMeal'] ?? '' }}">
                                            @foreach($recipe as $key => $value)
                                                @if(is_string($value) || is_numeric($value))
                                                    <input type="hidden" name="recipe_data[{{ $key }}]" value="{{ $value }}">
                                                @endif
                                            @endforeach
                                            <button type="submit" class="btn-primary">
                                                <span class="emoji-sm">💾</span> Save Recipe
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-sm text-gray-600">✓ Saved</span>
                                    @endif
                                    
                                    @if($isSaved)
                                        <form method="POST" action="{{ route('recipes.favorite', $recipe['idMeal'] ?? '') }}" class="inline">
                                            @csrf
                                            <button type="submit" class="btn-favorite {{ $isFavorite ? 'active' : '' }}" title="{{ $isFavorite ? 'Remove from Favorites' : 'Add to Favorites' }}">
                                                <span class="emoji-md">{{ $isFavorite ? '❤️' : '🤍' }}</span>
                                            </button>
                                        </form>
                                    @else
                                        <!-- Favorite button - saves and favorites in one action -->
                                        <form method="POST" action="{{ route('recipes.save') }}" id="saveAndFavoriteForm" class="inline">
                                            @csrf
                                            <input type="hidden" name="recipe_id" value="{{ $recipe['idMeal'] ?? '' }}">
                                            <input type="hidden" name="favorite" value="1">
                                            @foreach($recipe as $key => $value)
                                                @if(is_string($value) || is_numeric($value))
                                                    <input type="hidden" name="recipe_data[{{ $key }}]" value="{{ $value }}">
                                                @endif
                                            @endforeach
                                            <button type="submit" class="btn-favorite" title="Save and Add to Favorites">
                                                <span class="emoji-md">🤍</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endauth
                        </div>
                    </div>

                    @if(isset($recipe['strInstructions']))
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-black mb-4">Instructions</h2>
                            <div class="prose max-w-none text-gray-700">
                                {!! nl2br(e($recipe['strInstructions'])) !!}
                            </div>
                        </div>
                    @endif

                    <div>
                        <h2 class="text-2xl font-bold text-black mb-4">Ingredients</h2>
                        <ul class="list-disc list-inside space-y-2">
                            @for($i = 1; $i <= 20; $i++)
                                @php
                                    $ingredient = $recipe["strIngredient{$i}"] ?? null;
                                    $measure = $recipe["strMeasure{$i}"] ?? null;
                                @endphp
                                @if($ingredient && trim($ingredient) !== '')
                                    <li class="text-gray-700">
                                        <strong>{{ trim($measure) }}</strong> {{ trim($ingredient) }}
                                    </li>
                                @endif
                            @endfor
                        </ul>
                    </div>

                    @if(isset($recipe['strYoutube']))
                        <div class="mt-6">
                            <a href="{{ $recipe['strYoutube'] }}" target="_blank" class="btn-secondary">
                                <span class="emoji-sm">▶️</span>
                                Watch on YouTube
                            </a>
                        </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-black font-medium">
                            ← Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
