@props(['value'])

<label {{ $attributes->merge(['class' => 'agro-label']) }}>
    {{ $value ?? $slot }}
</label>
