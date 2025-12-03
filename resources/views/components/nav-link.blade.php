@props(['active'])

@php
$classes = ($active ?? false)
            ? 'md-app-nav-link md-app-nav-link--active'
            : 'md-app-nav-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
