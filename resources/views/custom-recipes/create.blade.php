<x-app-layout :title="__('Add custom recipe')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add custom recipe') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4" role="alert">
                    <p class="font-semibold mb-2">{{ __('Please fix the following:') }}</p>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-6">
                        {{ __('Create a recipe that lives in your account. These are separate from TheMealDB favorites.') }}
                    </p>

                    @include('custom-recipes._form', [
                        'recipe' => null,
                        'action' => route('custom-recipes.store'),
                        'submitLabel' => __('Save recipe'),
                        'cancelUrl' => route('dashboard'),
                        'cancelLabel' => __('Back to dashboard'),
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
