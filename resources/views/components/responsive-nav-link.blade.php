@props(['active'])

@php
$classes = ($active ?? false)
            ? 'nav-drawer-link nav-drawer-link-active'
            : 'nav-drawer-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
