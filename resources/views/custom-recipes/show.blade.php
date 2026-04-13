<x-app-layout :title="$recipe->title">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $recipe->title }}
            </h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('custom-recipes.edit', $recipe) }}" class="btn-primary text-sm">
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('custom-recipes.index') }}" class="btn-secondary text-sm">
                    {{ __('All my recipes') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card overflow-hidden mb-6">
                @if($recipe->image_url)
                    <img src="{{ $recipe->image_url }}" alt="{{ $recipe->title }}" class="w-full max-h-80 object-cover">
                @endif
                <div class="p-6 space-y-4">
                    @if($recipe->category || $recipe->area || $recipe->servings || $recipe->prep_time_minutes || $recipe->cook_time_minutes)
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            @if($recipe->category)
                                <div>
                                    <dt class="text-gray-500">{{ __('Category') }}</dt>
                                    <dd class="font-medium text-gray-900">{{ $recipe->category }}</dd>
                                </div>
                            @endif
                            @if($recipe->area)
                                <div>
                                    <dt class="text-gray-500">{{ __('Cuisine / area') }}</dt>
                                    <dd class="font-medium text-gray-900">{{ $recipe->area }}</dd>
                                </div>
                            @endif
                            @if($recipe->servings)
                                <div>
                                    <dt class="text-gray-500">{{ __('Servings') }}</dt>
                                    <dd class="font-medium text-gray-900">{{ $recipe->servings }}</dd>
                                </div>
                            @endif
                            @if($recipe->prep_time_minutes !== null || $recipe->cook_time_minutes !== null)
                                <div>
                                    <dt class="text-gray-500">{{ __('Time') }}</dt>
                                    <dd class="font-medium text-gray-900">
                                        @if($recipe->prep_time_minutes !== null)
                                            {{ __('Prep') }}: {{ $recipe->prep_time_minutes }} {{ __('min') }}
                                        @endif
                                        @if($recipe->prep_time_minutes !== null && $recipe->cook_time_minutes !== null)
                                            ·
                                        @endif
                                        @if($recipe->cook_time_minutes !== null)
                                            {{ __('Cook') }}: {{ $recipe->cook_time_minutes }} {{ __('min') }}
                                        @endif
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    @if($recipe->ingredients->count() > 0)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('Ingredients') }}</h3>
                            <ul class="list-disc list-inside text-gray-800 space-y-1">
                                @foreach($recipe->ingredients as $ing)
                                    <li>
                                        @if($ing->measure)
                                            <span class="font-medium">{{ $ing->measure }}</span>
                                            {{ $ing->name }}
                                        @else
                                            {{ $ing->name }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('Instructions') }}</h3>
                        <div class="prose prose-sm max-w-none text-gray-800 whitespace-pre-wrap">{{ $recipe->instructions }}</div>
                    </div>
                </div>
            </div>

            <p class="text-center text-sm text-gray-500">
                <a href="{{ route('dashboard') }}" class="text-black font-medium hover:underline">{{ __('Back to dashboard') }}</a>
            </p>
        </div>
    </div>
</x-app-layout>
