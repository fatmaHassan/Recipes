@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-3 py-1.5 rounded text-sm font-semibold text-black bg-white shadow-sm transition duration-150 ease-in-out'
            : 'inline-flex items-center px-3 py-1.5 rounded text-sm font-medium text-gray-600 hover:text-gray-900 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
