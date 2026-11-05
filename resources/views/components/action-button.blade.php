@props([
    'href' => null,
    'action' => '',
    'icon' => 'fa-solid fa-eye',
    'tone' => 'neutral',
    'type' => 'button',
    'align' => 'center',
])

@php
    $toneClass = match ($tone) {
        'brand' => 'btn-icon--brand',
        'primary' => 'btn-icon--primary',
        'warning' => 'btn-icon--warning',
        'danger' => 'btn-icon--danger',
        'dark' => 'btn-icon--dark',
        default => 'btn-icon--neutral',
    };

    $tooltipClass = $align === 'end' ? 'tooltip-end' : '';

    $classes = trim("btn-icon {$toneClass} has-tooltip {$tooltipClass}");
@endphp

@if ($href)
    <a href="{{ $href }}" aria-label="{{ $action }}"
        {{ $attributes->merge(['class' => $classes]) }}>
        <i class="{{ $icon }}" aria-hidden="true"></i>
        <span class="tooltip-content" role="tooltip">{{ $action }}</span>
    </a>
@else
    <button type="{{ $type }}" aria-label="{{ $action }}"
        {{ $attributes->merge(['class' => $classes]) }}>
        <i class="{{ $icon }}" aria-hidden="true"></i>
        <span class="tooltip-content" role="tooltip">{{ $action }}</span>
    </button>
@endif
