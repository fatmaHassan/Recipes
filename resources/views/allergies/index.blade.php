<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Allergies') }}
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
                    <h3 class="text-lg font-semibold mb-4 text-black">Add New Allergy</h3>
                    <p class="text-sm text-gray-600 mb-4">Add allergens to filter them out from recipe suggestions.</p>
                    <form method="POST" action="{{ route('allergies.store') }}" class="flex gap-3">
                        @csrf
                        <input type="text" name="allergen_name" required class="flex-1" placeholder="Enter allergen name (e.g., peanuts, dairy, gluten)">
                        <button type="submit" class="btn-primary">
                            Add
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-black">Your Allergies</h3>
                    @if($allergies->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            @foreach($allergies as $allergy)
                                <div class="card p-3 flex items-center justify-between border-red-200">
                                    <span class="text-red-800 font-medium text-sm">{{ $allergy->allergen_name }}</span>
                                    <form method="POST" action="{{ route('allergies.destroy', $allergy) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600 text-sm" onclick="return confirm('Are you sure you want to remove this allergy?')" title="Remove">
                                            ✕
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600">No allergies added yet. Add your allergies to filter recipes safely.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
