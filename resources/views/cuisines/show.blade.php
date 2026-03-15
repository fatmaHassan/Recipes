<x-app-layout :title="$cuisine . ' Recipes'">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header with Flag -->
            <div class="mb-8">
                <a href="{{ route('cuisines.index') }}" class="text-gray-600 hover:text-black mb-4 inline-flex items-center">
                    ← Back to All Cuisines
                </a>
                
                <div class="flex items-center gap-4 mt-4">
                    @if($cuisineInfo && $cuisineInfo['code'])
                        <img 
                            src="https://flagcdn.com/w80/{{ $cuisineInfo['code'] }}.png"
                            srcset="https://flagcdn.com/w160/{{ $cuisineInfo['code'] }}.png 2x"
                            width="80"
                            height="60"
                            alt="{{ $cuisine }} flag"
                            class="rounded shadow-sm"
                        >
                    @else
                        <div class="w-20 h-[60px] bg-gray-100 rounded flex items-center justify-center">
                            <span class="text-3xl">🍽️</span>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-3xl font-bold text-black">{{ $cuisine }} Recipes</h1>
                        <p class="text-gray-600">
                            {{ $paginatedRecipes->total() }} {{ Str::plural('recipe', $paginatedRecipes->total()) }} found
                        </p>
                    </div>
                </div>
            </div>

            @if($paginatedRecipes->count() > 0)
                <div class="mb-4 text-gray-600">
                    Showing {{ $paginatedRecipes->firstItem() }} to {{ $paginatedRecipes->lastItem() }} of {{ $paginatedRecipes->total() }} recipes
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    @foreach($paginatedRecipes as $recipe)
                        <x-recipe-card :recipe="$recipe" />
                    @endforeach
                </div>

                <!-- Pagination Links -->
                <div class="mt-8">
                    {{ $paginatedRecipes->links() }}
                </div>
            @else
                <div class="card p-8 text-center">
                    <div class="text-6xl mb-4">🔍</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">No recipes found</h3>
                    <p class="text-gray-600 mb-6">We couldn't find any {{ $cuisine }} recipes at the moment.</p>
                    <a href="{{ route('cuisines.index') }}" class="btn-primary inline-block">
                        ← Explore Other Cuisines
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
