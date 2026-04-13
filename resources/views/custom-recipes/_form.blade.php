@php
    $ingredientRows = old('ingredients');
    if (! is_array($ingredientRows) || count($ingredientRows) === 0) {
        if (isset($recipe) && $recipe && $recipe->relationLoaded('ingredients') && $recipe->ingredients->count() > 0) {
            $ingredientRows = $recipe->ingredients->map(fn ($i) => [
                'name' => $i->name,
                'measure' => $i->measure ?? '',
            ])->all();
        } else {
            $ingredientRows = [
                ['name' => '', 'measure' => ''],
                ['name' => '', 'measure' => ''],
                ['name' => '', 'measure' => ''],
            ];
        }
    }
@endphp

<form method="POST" action="{{ $action }}" id="customRecipeForm">
    @csrf
    @isset($method)
        @method($method)
    @endisset

    <div class="space-y-5">
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Recipe title') }} <span class="text-red-600">*</span></label>
            <x-text-input id="title" name="title" type="text" class="block w-full" :value="old('title', $recipe?->title ?? '')" required autofocus />
        </div>

        <div>
            <label for="instructions" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Instructions') }} <span class="text-red-600">*</span></label>
            <textarea id="instructions" name="instructions" rows="8" class="border-gray-300 focus:border-black focus:ring-black rounded-md shadow-sm block w-full" required>{{ old('instructions', $recipe?->instructions ?? '') }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category') }}</label>
                <x-text-input id="category" name="category" type="text" class="block w-full" :value="old('category', $recipe?->category ?? '')" placeholder="{{ __('e.g. Dessert') }}" />
            </div>
            <div>
                <label for="area" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Cuisine / area') }}</label>
                <x-text-input id="area" name="area" type="text" class="block w-full" :value="old('area', $recipe?->area ?? '')" placeholder="{{ __('e.g. Italian') }}" />
            </div>
        </div>

        <div>
            <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Image URL') }}</label>
            <x-text-input id="image_url" name="image_url" type="url" class="block w-full" :value="old('image_url', $recipe?->image_url ?? '')" placeholder="https://..." />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="servings" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Servings') }}</label>
                <x-text-input id="servings" name="servings" type="text" class="block w-full" :value="old('servings', $recipe?->servings ?? '')" placeholder="{{ __('e.g. 4') }}" />
            </div>
            <div>
                <label for="prep_time_minutes" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Prep (minutes)') }}</label>
                <x-text-input id="prep_time_minutes" name="prep_time_minutes" type="number" min="0" class="block w-full" :value="old('prep_time_minutes', $recipe?->prep_time_minutes ?? '')" />
            </div>
            <div>
                <label for="cook_time_minutes" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Cook (minutes)') }}</label>
                <x-text-input id="cook_time_minutes" name="cook_time_minutes" type="number" min="0" class="block w-full" :value="old('cook_time_minutes', $recipe?->cook_time_minutes ?? '')" />
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="block text-sm font-medium text-gray-700">{{ __('Ingredients') }} <span class="text-red-600">*</span></span>
                <button type="button" id="addIngredientRow" class="text-sm font-semibold text-orange-600 hover:text-orange-700">
                    + {{ __('Add row') }}
                </button>
            </div>
            @error('ingredients')
                <p class="text-sm text-red-600 mb-2">{{ $message }}</p>
            @enderror
            <div id="ingredientRows" class="space-y-2">
                @foreach($ingredientRows as $index => $row)
                    <div class="ingredient-row flex flex-col sm:flex-row gap-2 sm:items-center">
                        <div class="w-full sm:w-40 sm:shrink-0">
                            <x-text-input
                                name="ingredients[{{ $index }}][measure]"
                                type="text"
                                class="block w-full"
                                :value="$row['measure'] ?? ''"
                                placeholder="{{ __('Amount') }}"
                            />
                        </div>
                        <div class="min-w-0 flex-1 w-full">
                            <x-text-input
                                name="ingredients[{{ $index }}][name]"
                                type="text"
                                class="block w-full min-w-0"
                                :value="$row['name'] ?? ''"
                                placeholder="{{ __('Ingredient name') }}"
                            />
                        </div>
                        <button type="button" class="remove-ingredient shrink-0 text-gray-400 hover:text-red-600 text-sm sm:w-8 text-left sm:text-center" title="{{ __('Remove row') }}">✕</button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mt-8">
        <button type="submit" class="btn-primary">
            {{ $submitLabel }}
        </button>
        <a href="{{ $cancelUrl }}" class="btn-secondary">
            {{ $cancelLabel }}
        </a>
    </div>
</form>

<template id="ingredientRowTemplate">
    <div class="ingredient-row flex flex-col sm:flex-row gap-2 sm:items-center">
        <div class="w-full sm:w-40 sm:shrink-0">
            <input type="text" name="ingredients[__INDEX__][measure]" class="border-gray-300 focus:border-black focus:ring-black rounded shadow-sm block w-full" placeholder="{{ __('Amount') }}" />
        </div>
        <div class="min-w-0 flex-1 w-full">
            <input type="text" name="ingredients[__INDEX__][name]" class="border-gray-300 focus:border-black focus:ring-black rounded shadow-sm block w-full min-w-0" placeholder="{{ __('Ingredient name') }}" />
        </div>
        <button type="button" class="remove-ingredient shrink-0 text-gray-400 hover:text-red-600 text-sm sm:w-8 text-left sm:text-center" title="{{ __('Remove row') }}">✕</button>
    </div>
</template>

<script>
    (function () {
        var container = document.getElementById('ingredientRows');
        var template = document.getElementById('ingredientRowTemplate');
        var addBtn = document.getElementById('addIngredientRow');

        function nextIndex() {
            return container.querySelectorAll('.ingredient-row').length;
        }

        function bindRemove(row) {
            var btn = row.querySelector('.remove-ingredient');
            if (!btn) return;
            btn.addEventListener('click', function () {
                if (container.querySelectorAll('.ingredient-row').length <= 1) return;
                row.remove();
            });
        }

        container.querySelectorAll('.ingredient-row').forEach(bindRemove);

        addBtn.addEventListener('click', function () {
            var html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex()));
            var wrap = document.createElement('div');
            wrap.innerHTML = html.trim();
            var row = wrap.firstElementChild;
            container.appendChild(row);
            bindRemove(row);
        });
    })();
</script>
