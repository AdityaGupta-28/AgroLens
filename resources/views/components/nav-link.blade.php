@props(['active'])

@php
$classes = ($active ?? false)
    ? 'agro-nav-link-active'
    : 'agro-nav-link-inactive';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
