@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full px-4 py-2 rounded-full text-base font-medium text-black bg-gray-100 transition duration-150 ease-in-out'
            : 'block w-full px-4 py-2 rounded-full text-base font-medium text-gray-600 hover:text-black hover:bg-gray-100 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
