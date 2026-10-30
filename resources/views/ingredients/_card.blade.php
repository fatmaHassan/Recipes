<button
    type="button"
    @click="toggle(item.name)"
    :disabled="isExisting(item.name)"
    :class="isExisting(item.name)
        ? 'border-gray-200 cursor-default'
        : (isSelected(item.name)
            ? 'ring-2 ring-black bg-gray-100 border-black'
            : 'border-gray-200 hover:border-gray-400')"
    class="relative flex flex-col items-center gap-1 rounded-lg border p-2 text-center transition-colors"
    :title="isExisting(item.name) ? 'Already in your list' : (isSelected(item.name) ? 'Selected — click to deselect' : 'Click to select')"
>
    <span x-show="isExisting(item.name)" class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-green-600 text-white text-[9px]" title="In your list">&#10003;</span>
    <span x-show="isSelected(item.name)" class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-black text-white text-[9px]">&#10003;</span>

    <span class="flex h-14 w-14 items-center justify-center">
        <template x-if="item.thumb">
            <img :src="item.thumb" :alt="item.name" loading="lazy" class="h-14 w-14 object-contain" x-on:error="$event.target.style.display = 'none'">
        </template>
    </span>

    <span class="text-xs text-gray-800 leading-tight line-clamp-2" x-text="item.name"></span>
</button>
