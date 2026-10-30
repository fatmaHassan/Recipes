<x-app-layout :title="__('Ingredients')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Ingredients') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div x-data="ingredientPicker(@js($ingredients->pluck('name')->all()), @js($catalog), @js($categories), @js($popular))">
                {{-- User's own ingredients (kept first so returning users see their list) --}}
                <div class="card">
                    <div class="p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <h3 class="text-lg font-semibold text-black">Your Ingredients ({{ $ingredients->count() }})</h3>
                            @if($ingredients->count() > 0)
                                <form method="GET" action="{{ route('recipes.search') }}">
                                    @foreach($ingredients as $ingredient)
                                        <input type="hidden" name="ingredients[]" value="{{ $ingredient->name }}">
                                    @endforeach
                                    <button type="submit" class="btn-secondary text-sm">Find recipes with your ingredients</button>
                                </form>
                            @endif
                        </div>

                        @if($ingredients->count() > 0)
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                @foreach($ingredients as $ingredient)
                                    <div class="card p-3 flex items-center justify-between">
                                        <span class="text-gray-800 text-sm">{{ $ingredient->name }}</span>
                                        <form method="POST" action="{{ route('ingredients.destroy', $ingredient) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-600 text-sm" onclick="return confirm('Are you sure you want to delete this ingredient?')" title="Delete">
                                                &#10005;
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-600">No ingredients added yet. Pick a few from the catalog below to start searching for recipes!</p>
                        @endif
                    </div>
                </div>

                @if(! empty($catalog))
                    <div class="card mt-6">
                        <div class="p-6">
                            <div class="flex flex-wrap items-baseline justify-between gap-2 mb-4">
                                <h3 class="text-lg font-semibold text-black">Browse Ingredients</h3>
                                <p class="text-sm text-gray-500">
                                    {{ count($catalog) }} ingredients from TheMealDB &mdash; search, filter by category, select several and add them all at once.
                                </p>
                            </div>

                            {{-- Search --}}
                            <div class="relative">
                                <input
                                    type="text"
                                    x-model="query"
                                    placeholder="Search ingredients..."
                                    autocomplete="off"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent"
                                >
                                <button
                                    type="button"
                                    x-show="query"
                                    @click="query = ''"
                                    class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600"
                                    title="Clear search"
                                >&#10005;</button>
                            </div>

                            {{-- Popular staples quick row (hidden while searching) --}}
                            <div x-cloak x-show="!query.trim()" x-transition class="mt-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Popular staples</p>
                                <div class="grid grid-cols-3 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-9 xl:grid-cols-10 gap-2">
                                    <template x-for="item in popular" :key="'popular-' + item.name">
                                        @include('ingredients._card')
                                    </template>
                                </div>
                            </div>

                            {{-- Category filter chips --}}
                            <div class="mt-4 flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    @click="activeCategory = 'All'"
                                    :class="activeCategory === 'All' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1 rounded-full text-sm transition-colors"
                                >All</button>
                                <template x-for="category in categories" :key="category">
                                    <button
                                        type="button"
                                        @click="activeCategory = category"
                                        x-text="category + ' (' + (countByCategory[category] ?? 0) + ')'"
                                        :class="activeCategory === category ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                        class="px-3 py-1 rounded-full text-sm transition-colors"
                                    ></button>
                                </template>
                            </div>

                            {{-- Selected tray --}}
                            <div x-show="selected.length > 0" x-transition class="mt-4 rounded-lg border border-gray-300 bg-gray-50 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <p class="text-sm font-medium text-gray-800">
                                        <span x-text="selected.length"></span> ingredient<span x-show="selected.length !== 1">s</span> selected
                                    </p>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <form method="GET" action="{{ route('recipes.search') }}">
                                            <template x-for="name in selected" :key="'search-' + name">
                                                <input type="hidden" name="ingredients[]" :value="name">
                                            </template>
                                            <button type="submit" class="btn-secondary text-sm">Find recipes</button>
                                        </form>
                                        <form method="POST" action="{{ route('ingredients.store') }}">
                                            @csrf
                                            <template x-for="name in selected" :key="'add-' + name">
                                                <input type="hidden" name="names[]" :value="name">
                                            </template>
                                            <button type="submit" class="btn-primary text-sm">
                                                Add <span x-text="selected.length"></span> to My Ingredients
                                            </button>
                                        </form>
                                        <button type="button" @click="clear()" class="text-sm text-gray-500 hover:text-gray-800">Clear</button>
                                    </div>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <template x-for="name in selected" :key="name">
                                        <button
                                            type="button"
                                            @click="removeSelected(name)"
                                            class="inline-flex items-center gap-1 px-3 py-1 text-sm bg-white border border-gray-300 rounded-full hover:border-red-400 hover:text-red-600 transition-colors"
                                        >
                                            <span x-text="name"></span>
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Custom ingredient escape hatch --}}
                            <div x-show="query.trim() && !exactMatch" x-transition class="mt-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-sm text-yellow-800">
                                    No exact match for &ldquo;<span class="font-semibold" x-text="query"></span>&rdquo; in TheMealDB.
                                </p>
                                <form method="POST" action="{{ route('ingredients.store') }}" class="mt-2 flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="name" :value="query.trim()">
                                    <button type="submit" class="px-4 py-1.5 text-sm bg-yellow-600 hover:bg-yellow-700 text-white rounded-md font-medium transition-colors">
                                        Add as custom ingredient
                                    </button>
                                    <span class="text-xs text-yellow-700">Custom ingredients still work for recipe search, but results may be limited.</span>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Catalog grid --}}
                    <div class="card mt-6">
                        <div class="p-6">
                            <p x-cloak class="text-sm text-gray-500 mb-3">
                                Showing <span class="font-medium text-gray-700" x-text="Math.min(visible, filtered.length)"></span>
                                of <span class="font-medium text-gray-700" x-text="filtered.length"></span>
                                ingredient<span x-show="filtered.length !== 1">s</span><span x-show="activeCategory !== 'All'"> in <span class="font-medium text-gray-700" x-text="activeCategory"></span></span>
                            </p>

                            <div x-show="filtered.length === 0" class="py-8 text-center text-sm text-gray-500">
                                No ingredients found. Try a different search or category.
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-2">
                                <template x-for="item in paged" :key="item.name">
                                    @include('ingredients._card')
                                </template>
                            </div>

                            <div x-cloak x-show="filtered.length > visible" class="mt-6 text-center">
                                <button type="button" @click="visible += pageSize" class="btn-secondary text-sm">
                                    Show more (<span x-text="filtered.length - visible"></span> remaining)
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card mt-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-2 text-black">Browse Ingredients</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Couldn't load the TheMealDB ingredient catalog right now. You can still add an ingredient by typing its name below.
                            </p>
                            <form method="POST" action="{{ route('ingredients.store') }}" class="flex gap-3">
                                @csrf
                                <input
                                    type="text"
                                    name="name"
                                    required
                                    maxlength="255"
                                    placeholder="Ingredient name..."
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent"
                                >
                                <button type="submit" class="btn-primary">Add</button>
                            </form>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <script>
        function ingredientPicker(existing, catalog, categories, popular) {
            return {
                query: '',
                activeCategory: 'All',
                selected: [],
                catalog: catalog,
                categories: categories,
                popular: popular,
                pageSize: 48,
                visible: 48,
                existingSet: new Set(existing.map((name) => name.toLowerCase())),

                init() {
                    // A new page of results whenever the query or category changes.
                    this.$watch('query', () => { this.visible = this.pageSize; });
                    this.$watch('activeCategory', () => { this.visible = this.pageSize; });
                },

                get filtered() {
                    const q = this.query.trim().toLowerCase();

                    return this.catalog.filter((item) =>
                        (this.activeCategory === 'All' || item.category === this.activeCategory) &&
                        (q === '' || item.name.toLowerCase().includes(q))
                    );
                },

                get paged() {
                    return this.filtered.slice(0, this.visible);
                },

                get countByCategory() {
                    const counts = {};

                    for (const item of this.catalog) {
                        counts[item.category] = (counts[item.category] ?? 0) + 1;
                    }

                    return counts;
                },

                get exactMatch() {
                    const q = this.query.trim().toLowerCase();

                    return q !== '' && this.catalog.some((item) => item.name.toLowerCase() === q);
                },

                isSelected(name) {
                    return this.selected.includes(name);
                },

                isExisting(name) {
                    return this.existingSet.has(name.toLowerCase());
                },

                toggle(name) {
                    if (this.isExisting(name)) {
                        return;
                    }

                    const index = this.selected.indexOf(name);
                    if (index === -1) {
                        this.selected.push(name);
                    } else {
                        this.selected.splice(index, 1);
                    }
                },

                removeSelected(name) {
                    this.selected = this.selected.filter((n) => n !== name);
                },

                clear() {
                    this.selected = [];
                },
            };
        }
    </script>
</x-app-layout>
