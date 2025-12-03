@props(['value'])

<label {{ $attributes->merge(['class' => 'md-label']) }}>
    {{ $value ?? $slot }}
</label>
