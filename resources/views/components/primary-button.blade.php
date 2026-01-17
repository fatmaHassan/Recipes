@props(['type' => 'submit'])

<button {{ $attributes->merge(['type' => $type, 'class' => 'btn-primary']) }}>
    {{ $slot }}
</button>
