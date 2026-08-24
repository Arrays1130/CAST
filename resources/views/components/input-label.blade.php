@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm text-notion-muted']) }}>
    {{ $value ?? $slot }}
</label>
