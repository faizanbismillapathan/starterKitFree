@props([
    'href' => null,
    'icon' => null,
    'danger' => false,
])

@php
    $classes = 'flex w-full items-center gap-2.5 px-3 py-2 text-sm transition-colors '
        .($danger
            ? 'text-[rgb(var(--color-danger))] hover:bg-[rgb(var(--color-danger-soft))]'
            : 'text-default hover:bg-surface-hover');
@endphp

@if ($href)
    <a href="{{ $href }}" role="menuitem" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-ui.icon :name="$icon" class="size-4 shrink-0 opacity-70" />@endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $attributes->get('type', 'button') }}" role="menuitem" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-ui.icon :name="$icon" class="size-4 shrink-0 opacity-70" />@endif
        <span>{{ $slot }}</span>
    </button>
@endif
