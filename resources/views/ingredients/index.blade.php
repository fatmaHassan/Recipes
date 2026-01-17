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
                    <form method="POST" action="{{ route('ingredients.store') }}" class="flex gap-3">
                        @csrf
                        <input type="text" name="name" required class="flex-1" placeholder="Enter ingredient name">
                        <button type="submit" class="btn-primary">
                            Add
                        </button>
                    </form>
                </div>
            </div>

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
