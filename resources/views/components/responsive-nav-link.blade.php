@props(['active'])

@php
$classes = ($active ?? false)
            ? 'md-responsive-nav-link md-responsive-nav-link--active'
            : 'md-responsive-nav-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
