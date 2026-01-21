<x-app-layout>
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

            <div class="card mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-black">Add New Ingredient</h3>
                    <div x-data="ingredientAutocomplete()" x-init="init()" class="relative">
                        <form method="POST" action="{{ route('ingredients.store') }}" @submit.prevent="handleSubmit($event)" class="flex gap-3">
                            @csrf
                            <div class="flex-1 relative">
                                <input 
                                    type="text" 
                                    name="name" 
                                    x-model="query"
                                    @input="searchIngredients()"
                                    @focus="if (suggestions.length > 0) showSuggestions = true"
                                    @blur="setTimeout(() => showSuggestions = false, 200)"
                                    @keydown.arrow-down.prevent="highlightNext(); showSuggestions = true"
                                    @keydown.arrow-up.prevent="highlightPrevious(); showSuggestions = true"
                                    @keydown.enter.prevent="selectHighlighted()"
                                    @keydown.escape="showSuggestions = false"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent"
                                    placeholder="Start typing ingredient name (min 2 characters)..."
                                    autocomplete="off"
                                >
                                
                                <!-- Loading indicator -->
                                <div x-show="loading" class="absolute right-3 top-2.5">
                                    <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>

                                <!-- Autocomplete suggestions dropdown -->
                                <div 
                                    x-show="showSuggestions && suggestions.length > 0" 
                                    x-transition
                                    class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                                >
                                    <template x-for="(suggestion, index) in suggestions" :key="index">
                                        <div 
                                            @click="selectSuggestion(suggestion)"
                                            @mouseenter="highlightedIndex = index"
                                            :class="{
                                                'bg-gray-100': highlightedIndex === index,
                                                'bg-white': highlightedIndex !== index
                                            }"
                                            class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-gray-800"
                                        >
                                            <span x-text="suggestion"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <button type="submit" class="btn-primary" :disabled="!query.trim()">
                                Add
                            </button>
                        </form>

                        <!-- Suggestions for ingredient not found -->
                        <div 
                            x-show="showNotFoundSuggestions && notFoundSuggestions.length > 0" 
                            x-transition
                            class="mt-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg"
                        >
                            <p class="text-sm font-semibold text-yellow-800 mb-2">
                                Ingredient not found in TheMealDB. Did you mean:
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="(suggestion, index) in notFoundSuggestions" :key="index">
                                    <button 
                                        @click="selectSuggestion(suggestion)"
                                        class="px-3 py-1 text-sm bg-yellow-100 hover:bg-yellow-200 text-yellow-900 rounded-md transition-colors"
                                    >
                                        <span x-text="suggestion"></span>
                                    </button>
                                </template>
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                <button 
                                    @click="addAnyway()"
                                    class="px-4 py-2 text-sm bg-yellow-600 hover:bg-yellow-700 text-white rounded-md transition-colors font-medium"
                                >
                                    Add Anyway
                                </button>
                                <span class="text-xs text-yellow-700">
                                    Click to add as a custom ingredient
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Define function globally - works with Alpine.js
                function ingredientAutocomplete() {
                    return {
                            query: '',
                        suggestions: [],
                        notFoundSuggestions: [],
                        showSuggestions: false,
                        showNotFoundSuggestions: false,
                        highlightedIndex: -1,
                        loading: false,
                        searchTimeout: null,

                        init() {
                            console.log('Ingredient autocomplete initialized');
                            // Don't load initial suggestions - wait for user input
                        },

                        searchIngredients() {
                            const query = this.query.trim();
                            
                            // Clear previous timeout
                            if (this.searchTimeout) {
                                clearTimeout(this.searchTimeout);
                            }

                            // Reset states
                            this.showNotFoundSuggestions = false;
                            this.notFoundSuggestions = [];

                            if (query.length === 0) {
                                this.suggestions = [];
                                this.showSuggestions = false;
                                this.loading = false;
                                return;
                            }

                            // Require at least 2 characters before searching
                            if (query.length < 2) {
                                this.suggestions = [];
                                this.showSuggestions = false;
                                return;
                            }

                            // Debounce API calls
                            this.searchTimeout = setTimeout(() => {
                                this.loading = true;
                                
                                const url = `{{ route('ingredients.search') }}?q=${encodeURIComponent(query)}&limit=10`;
                                console.log('Fetching suggestions from:', url);
                                
                                fetch(url, {
                                    method: 'GET',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                    },
                                    credentials: 'same-origin'
                                })
                                .then(response => {
                                    console.log('Response status:', response.status);
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    console.log('Suggestions received:', data);
                                    this.suggestions = data.suggestions || [];
                                    // Show suggestions if we have results and query is at least 2 characters
                                    if (this.suggestions.length > 0 && this.query.trim().length >= 2) {
                                        this.showSuggestions = true;
                                    } else {
                                        this.showSuggestions = false;
                                    }
                                    this.loading = false;
                                })
                                .catch(error => {
                                    console.error('Error fetching suggestions:', error);
                                    this.loading = false;
                                    this.suggestions = [];
                                    this.showSuggestions = false;
                                });
                            }, 300);
                        },

                        selectSuggestion(suggestion) {
                            this.query = suggestion;
                            this.suggestions = [];
                            this.showSuggestions = false;
                            this.showNotFoundSuggestions = false;
                            this.notFoundSuggestions = [];
                            this.highlightedIndex = -1;
                        },

                        highlightNext() {
                            if (this.highlightedIndex < this.suggestions.length - 1) {
                                this.highlightedIndex++;
                            }
                        },

                        highlightPrevious() {
                            if (this.highlightedIndex > 0) {
                                this.highlightedIndex--;
                            }
                        },

                        selectHighlighted() {
                            if (this.highlightedIndex >= 0 && this.suggestions[this.highlightedIndex]) {
                                this.selectSuggestion(this.suggestions[this.highlightedIndex]);
                            } else if (this.query.trim()) {
                                // Submit form if no suggestion is highlighted but query exists
                                const form = this.$el.querySelector('form');
                                if (form) {
                                    this.handleSubmit({ target: form, preventDefault: () => {} });
                                }
                            }
                        },

                        async handleSubmit(event) {
                            event.preventDefault();
                            const form = event.target;
                            const query = this.query.trim();

                            if (!query) {
                                return;
                            }

                            // Check if ingredient exists in TheMealDB
                            try {
                                const formData = new FormData();
                                formData.append('ingredient', query);
                                
                                const response = await fetch('{{ route('ingredients.check') }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]')?.value
                                    },
                                    body: formData
                                });

                                const data = await response.json();

                                if (!data.exists && data.suggestions && data.suggestions.length > 0) {
                                    // Show suggestions - user can click a suggestion or proceed anyway
                                    this.notFoundSuggestions = data.suggestions;
                                    this.showNotFoundSuggestions = true;
                                    
                                    // Scroll to suggestions if needed
                                    this.$nextTick(() => {
                                        const suggestionBox = this.$el.querySelector('[x-show*="showNotFoundSuggestions"]');
                                        if (suggestionBox) {
                                            suggestionBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                        }
                                    });
                                } else {
                                    // Ingredient exists or no suggestions, submit normally
                                    this.submitForm(form);
                                }
                            } catch (error) {
                                console.error('Error checking ingredient:', error);
                                // Submit anyway if check fails
                                this.submitForm(form);
                            }
                        },

                        submitForm(form) {
                            // Hide suggestions before submitting
                            this.showNotFoundSuggestions = false;
                            this.showSuggestions = false;
                            
                            // Submit the form
                            form.submit();
                        },

                        addAnyway() {
                            const form = this.$el.querySelector('form');
                            if (form) {
                                this.submitForm(form);
                            }
                        }
                    }
                }
            </script>

            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-black">Your Ingredients</h3>
                    @if($ingredients->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            @foreach($ingredients as $ingredient)
                                <div class="card p-3 flex items-center justify-between">
                                    <span class="text-gray-800 text-sm">{{ $ingredient->name }}</span>
                                    <form method="POST" action="{{ route('ingredients.destroy', $ingredient) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600 text-sm" onclick="return confirm('Are you sure you want to delete this ingredient?')" title="Delete">
                                            ✕
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600">No ingredients added yet. Add some ingredients to start searching for recipes!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
