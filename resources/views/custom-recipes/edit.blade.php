<x-app-layout :title="__('Edit custom recipe')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit custom recipe') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
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
                    <p class="text-sm text-gray-600 mb-2">
                        <span class="font-medium text-gray-800">{{ $recipe->title }}</span>
                    </p>
                    <p class="text-sm text-gray-600 mb-6">
                        {{ __('Update any fields below. Ingredient rows replace the previous list when you save.') }}
                    </p>

                    @include('custom-recipes._form', [
                        'recipe' => $recipe,
                        'action' => route('custom-recipes.update', $recipe),
                        'method' => 'patch',
                        'submitLabel' => __('Save changes'),
                        'cancelUrl' => route('custom-recipes.show', $recipe),
                        'cancelLabel' => __('Cancel'),
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
