<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Recipe Search Results') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(isset($ingredients) && count($ingredients) > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-blue-800">
                        <strong>Searching for recipes with:</strong> {{ implode(', ', $ingredients) }}
                    </p>
                </div>
            @endif

            @if(count($recipes) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($recipes as $recipe)
                        <x-recipe-card :recipe="$recipe" />
                    @endforeach
                </div>
            @else
                <div class="card p-8 text-center">
                    <div class="text-6xl mb-4">🔍</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">No recipes found</h3>
                    <p class="text-gray-600 mb-6">TheMealDB requires specific ingredient names. Generic terms like "meat" won't work.</p>
                    
                    @if(isset($suggestions) && count($suggestions) > 0)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-6 text-left">
                            <h4 class="font-semibold text-yellow-900 mb-3">💡 Try these specific ingredients instead:</h4>
                            @foreach($suggestions as $ingredient => $alternatives)
                                <div class="mb-3">
                                    <p class="text-sm text-yellow-800 mb-2">
                                        <strong>"{{ $ingredient }}"</strong> not found. Try:
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($alternatives as $alt)
                                            <span class="bg-white px-3 py-1 rounded-lg text-sm text-yellow-900 border border-yellow-300">
                                                {{ $alt }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 text-left">
                        <p class="text-sm text-blue-800">
                            <strong>💡 Tip:</strong> Use specific ingredient names like "chicken", "beef", "tomato", "onion" instead of generic terms like "meat" or "vegetables".
                        </p>
                    </div>
                    
                    <a href="{{ route('dashboard') }}" class="btn-primary inline-block">
                        ← Back to Dashboard
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
