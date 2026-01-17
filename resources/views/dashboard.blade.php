<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-black">Select Ingredients to Search Recipes</h3>
                    
                    @if($ingredients->count() > 0)
                        <form id="recipeSearchForm" method="POST" action="{{ route('recipes.search') }}">
                            @csrf
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-4">
                                @foreach($ingredients as $ingredient)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" name="ingredients[]" value="{{ $ingredient->name }}" class="rounded border-gray-300 text-black focus:ring-black">
                                        <span class="text-sm text-gray-700">{{ $ingredient->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <button type="submit" class="btn-primary">
                                Search Recipes
                            </button>
                        </form>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded p-4">
                            <p class="text-gray-700 flex items-center gap-2">
                                <span class="emoji-md">ℹ️</span>
                                You don't have any ingredients yet. <a href="{{ route('ingredients.index') }}" class="font-semibold text-black hover:underline">Add some ingredients</a> to start searching for recipes!
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-black">Quick Actions</h3>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('ingredients.index') }}" class="btn-primary">
                            Manage Ingredients
                        </a>
                        <a href="{{ route('allergies.index') }}" class="btn-secondary">
                            Manage Allergies
                        </a>
                        <a href="{{ route('my-recipes.index') }}" class="btn-secondary">
                            My Recipes
                        </a>
                        <a href="{{ route('favorites.index') }}" class="btn-secondary">
                            Favorites
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
