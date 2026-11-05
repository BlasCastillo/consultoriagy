@props(['padding' => 'p-6'])

<div {{ $attributes->merge(['class' => trim("card {$padding}")]) }}>
    {{ $slot }}
</div>
