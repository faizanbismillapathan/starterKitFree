@props(['text', 'position' => 'top'])

@php
    $positions = [
        'top' => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
        'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
        'left' => 'right-full top-1/2 -translate-y-1/2 mr-2',
        'right' => 'left-full top-1/2 -translate-y-1/2 ml-2',
    ];
@endphp

<span class="relative inline-flex" x-data="{ show: false }">
    <span @mouseenter="show = true" @mouseleave="show = false" @focusin="show = true" @focusout="show = false">
        {{ $slot }}
    </span>
    <span
        x-show="show"
        x-cloak
        x-transition.opacity.duration.150ms
        role="tooltip"
        class="pointer-events-none absolute z-50 {{ $positions[$position] ?? $positions['top'] }} whitespace-nowrap rounded-[var(--radius-sm)] bg-[rgb(var(--color-text))] px-2 py-1 text-xs font-medium text-[rgb(var(--color-text-inverted))] shadow-3"
    >{{ $text }}</span>
</span>
