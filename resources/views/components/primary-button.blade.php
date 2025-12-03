<button {{ $attributes->merge(['type' => 'submit', 'class' => 'md-button']) }}>
    {{ $slot }}
</button>
