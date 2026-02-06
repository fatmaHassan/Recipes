<x-app-layout>
    @auth
        <!-- Logged-in user view -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                        ✅ {{ session('success') }}
                    </div>
                @endif
                <div class="card mb-6">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-3xl font-bold text-black mb-4">Welcome back, {{ Auth::user()->name }}!</h1>
                        <p class="text-gray-600 mb-6">Ready to discover new recipes from your pantry?</p>
                        
                        <!-- Quick Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="card p-4 border-l-4 border-orange-500">
                                <div class="text-2xl font-bold text-orange-600">{{ $ingredientsCount ?? 0 }}</div>
                                <div class="text-sm text-gray-600">Ingredients</div>
                            </div>
                            <div class="card p-4 border-l-4 border-amber-500">
                                <div class="text-2xl font-bold text-amber-600">{{ $savedRecipesCount ?? 0 }}</div>
                                <div class="text-sm text-gray-600">Saved Recipes</div>
                            </div>
                            <div class="card p-4 border-l-4 border-red-500">
                                <div class="text-2xl font-bold text-red-600">{{ $favoritesCount ?? 0 }}</div>
                                <div class="text-sm text-gray-600">Favorites</div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="flex flex-wrap gap-4 mb-6">
                            <a href="{{ route('ingredients.index') }}" class="btn-primary">
                                Add Ingredients
                            </a>
                            <a href="{{ route('dashboard') }}" class="btn-secondary">
                                Search Recipes
                            </a>
                            <a href="{{ route('my-recipes.index') }}" class="btn-secondary">
                                View My Recipes
                            </a>
                        </div>

                        <!-- Recent Activity -->
                        @if(isset($recentRecipes) && $recentRecipes->count() > 0)
                            <div class="mt-8">
                                <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Recipes</h2>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    @foreach($recentRecipes as $savedRecipe)
                                        @php
                                            $recipe = $savedRecipe->recipe_data;
                                        @endphp
                                        <div class="card overflow-hidden">
                                            @if(isset($recipe['strMealThumb']))
                                                <img src="{{ $recipe['strMealThumb'] }}" alt="{{ $recipe['strMeal'] ?? 'Recipe' }}" class="w-full h-48 object-cover">
                                            @endif
                                            <div class="p-4">
                                                <h3 class="font-semibold text-lg mb-2">{{ $recipe['strMeal'] ?? 'Recipe' }}</h3>
                                                <a href="{{ route('recipes.show', $savedRecipe->recipe_id) }}" class="text-orange-600 hover:text-orange-700 font-medium">
                                                    View Recipe →
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Random Meals Section -->
                        @if(isset($randomMeals) && count($randomMeals) > 0)
                            <div class="mt-8">
                                <h2 class="text-2xl font-bold text-gray-800 mb-4">Random Meals</h2>
                                <p class="text-gray-600 mb-6">Discover some delicious recipes to try!</p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    @foreach($randomMeals as $meal)
                                        <x-recipe-card :recipe="$meal" />
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Guest view -->
        <div class="py-12">
            <!-- Hero Section -->
            <div class="bg-white py-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <h1 class="text-5xl font-bold text-black mb-6 leading-tight">
                            The Fastest Way to Find<br/>Recipes from Your Pantry
                        </h1>
                        <p class="text-xl mb-10 max-w-2xl mx-auto text-gray-600 leading-relaxed">
                            Enter the ingredients you have at home, and we'll find delicious recipes you can make right now. Filter by your allergies and dietary preferences.
                        </p>
                        <div class="flex justify-center gap-4 flex-wrap">
                            <a href="{{ route('register') }}" class="btn-primary text-base px-6 py-3">
                                Get Started
                            </a>
                            <a href="{{ route('login') }}" class="btn-secondary text-base px-6 py-3 bg-white text-black">
                                Sign In
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-black mb-4">Why Choose Us?</h2>
                    <p class="text-gray-600 text-lg">Everything you need to discover amazing recipes</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="card card-hover p-6 text-center">
                        <div class="w-12 h-12 rounded flex items-center justify-center mx-auto mb-4 bg-gray-100">
                            <span class="emoji-lg">📝</span>
                        </div>
                        <h3 class="text-lg font-bold text-black mb-2">Add Your Ingredients</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">Manage your pantry inventory easily</p>
                    </div>
                    <div class="card card-hover p-6 text-center">
                        <div class="w-12 h-12 rounded flex items-center justify-center mx-auto mb-4 bg-gray-100">
                            <span class="emoji-lg">⚠️</span>
                        </div>
                        <h3 class="text-lg font-bold text-black mb-2">Filter by Allergies</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">Safe recipes tailored to your needs</p>
                    </div>
                    <div class="card card-hover p-6 text-center">
                        <div class="w-12 h-12 rounded flex items-center justify-center mx-auto mb-4 bg-gray-100">
                            <span class="emoji-lg">🔍</span>
                        </div>
                        <h3 class="text-lg font-bold text-black mb-2">Discover Recipes</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">Find recipes from TheMealDB</p>
                    </div>
                    <div class="card card-hover p-6 text-center">
                        <div class="w-12 h-12 rounded flex items-center justify-center mx-auto mb-4 bg-gray-100">
                            <span class="emoji-lg">❤️</span>
                        </div>
                        <h3 class="text-lg font-bold text-black mb-2">Save Favorites</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">Keep your favorite recipes handy</p>
                    </div>
                </div>
            </div>

            <!-- Random Meals Section -->
            @if(isset($randomMeals) && count($randomMeals) > 0)
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold text-black mb-4">Random Meals</h2>
                        <p class="text-gray-600 text-lg">Discover some delicious recipes to try!</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($randomMeals as $meal)
                            <x-recipe-card :recipe="$meal" />
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- How It Works Section -->
            <div class="bg-gray-50 py-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-16">
                        <h2 class="text-4xl font-bold text-black mb-4">How It Works</h2>
                        <p class="text-gray-600 text-lg">Get started in three simple steps</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="card p-8 text-center">
                            <div class="bg-black text-white w-12 h-12 rounded flex items-center justify-center mx-auto mb-6 text-xl font-bold">1</div>
                            <h3 class="text-xl font-bold text-black mb-3">Add Ingredients</h3>
                            <p class="text-gray-600 leading-relaxed">Add ingredients you have at home to your list</p>
                        </div>
                        <div class="card p-8 text-center">
                            <div class="bg-black text-white w-12 h-12 rounded flex items-center justify-center mx-auto mb-6 text-xl font-bold">2</div>
                            <h3 class="text-xl font-bold text-black mb-3">Set Your Allergies</h3>
                            <p class="text-gray-600 leading-relaxed">Add your allergies and dietary preferences</p>
                        </div>
                        <div class="card p-8 text-center">
                            <div class="bg-black text-white w-12 h-12 rounded flex items-center justify-center mx-auto mb-6 text-xl font-bold">3</div>
                            <h3 class="text-xl font-bold text-black mb-3">Get Recipes</h3>
                            <p class="text-gray-600 leading-relaxed">Get personalized recipe suggestions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endauth
</x-app-layout>
