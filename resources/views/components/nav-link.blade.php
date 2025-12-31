@props(['active'])

@php
$classes = ($active ?? false)
            ? 'nav-link-custom active'
            : 'nav-link-custom';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
