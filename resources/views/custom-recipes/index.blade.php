<x-app-layout :title="__('My custom recipes')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My custom recipes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <p class="text-gray-600 text-sm">
                    {{ __('Recipes you created in your account.') }}
                </p>
                <a href="{{ route('custom-recipes.create') }}" class="btn-primary">
                    {{ __('Add custom recipe') }}
                </a>
            </div>

            @if($recipes->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($recipes as $recipe)
                        <div class="card card-hover overflow-hidden">
                            @if($recipe->image_url)
                                <img src="{{ $recipe->image_url }}" alt="{{ $recipe->title }}" class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gray-100 flex items-center justify-center text-5xl">📝</div>
                            @endif
                            <div class="p-4">
                                <h3 class="font-semibold text-lg text-gray-800 mb-1">{{ $recipe->title }}</h3>
                                @if($recipe->category || $recipe->area)
                                    <p class="text-sm text-gray-600 mb-2">
                                        {{ collect([$recipe->category, $recipe->area])->filter()->join(' · ') }}
                                    </p>
                                @endif
                                <p class="text-sm text-gray-500 mb-4">{{ __('Updated') }} {{ $recipe->updated_at->diffForHumans() }}</p>
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('custom-recipes.show', $recipe) }}" class="flex-1 btn-primary text-center min-w-[7rem]">
                                        {{ __('View') }}
                                    </a>
                                    <a href="{{ route('custom-recipes.edit', $recipe) }}" class="flex-1 btn-secondary text-center min-w-[7rem]">
                                        {{ __('Edit') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card p-8 text-center bg-gray-50 border border-gray-200">
                    <div class="text-6xl mb-4">📝</div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ __('You have no custom recipes yet') }}</h3>
                    <p class="text-gray-600 mb-6">
                        {{ __('Add your own recipes from the dashboard to see them here.') }}
                    </p>
                    <a href="{{ route('custom-recipes.create') }}" class="btn-primary inline-flex items-center gap-2">
                        {{ __('Add custom recipe') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
