@props(['recipe'])

<div class="card card-hover overflow-hidden">
    @if(isset($recipe['strMealThumb']))
        <img src="{{ $recipe['strMealThumb'] }}" alt="{{ $recipe['strMeal'] ?? 'Recipe' }}" class="w-full h-48 object-cover">
    @else
        <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
            <span class="emoji-lg">🍽️</span>
        </div>
    @endif
    <div class="p-4">
        <h3 class="font-semibold text-lg mb-2 text-gray-800">{{ $recipe['strMeal'] ?? 'Recipe' }}</h3>
        @if(isset($recipe['strCategory']))
            <p class="text-sm text-gray-600 mb-3">{{ $recipe['strCategory'] }}</p>
        @endif
        <a href="{{ route('recipes.show', $recipe['idMeal'] ?? '') }}" class="text-sm font-medium text-gray-700 hover:text-black inline-flex items-center">
            View Recipe <span class="ml-1">→</span>
        </a>
    </div>
</div>
