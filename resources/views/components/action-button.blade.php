@props([
    'href' => null,
    'action' => '',
    'icon' => 'fa-solid fa-eye',
    'tone' => 'neutral',
    'type' => 'button',
    'align' => 'center',
    'size' => 'md',
])

@php
    // Paleta corporativa estandarizada de acciones.
    // Los aliases permiten usar un tono semántico (view, edit, delete...)
    // o los tonos históricos (neutral, warning, danger, brand...).
    $toneClass = match ($tone) {
        'create', 'save', 'confirm', 'primary' => 'btn-icon--primary',
        'edit', 'warning' => 'btn-icon--edit',
        'delete', 'destroy', 'danger' => 'btn-icon--delete',
        'view', 'show', 'detail', 'info' => 'btn-icon--view',
        'export', 'pdf', 'download' => 'btn-icon--export',
        'back', 'return' => 'btn-icon--back',
        'replan', 'replanificar' => 'btn-icon--replan',
        'evidence', 'evidencia' => 'btn-icon--evidence',
        'brand' => 'btn-icon--brand',
        'dark' => 'btn-icon--dark',
        default => 'btn-icon--neutral',
    };

    $sizeClass = $size === 'sm' ? 'btn-icon--sm' : '';

    $tooltipClass = $align === 'end' ? 'tooltip-end' : '';

    $classes = trim("btn-icon {$toneClass} {$sizeClass} has-tooltip {$tooltipClass}");
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
