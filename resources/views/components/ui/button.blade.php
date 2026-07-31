@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'iconTrailing' => null,
    'loading' => false,
    'block' => false,
])

@php
    // Variants map to the semantic hierarchy in 05_Design_System.md §14.
    $variants = [
        'primary' => 'bg-[rgb(var(--color-primary))] text-[rgb(var(--color-on-primary))] border-transparent hover:bg-[rgb(var(--color-primary-hover))] shadow-1',
        'secondary' => 'bg-surface text-default border-[rgb(var(--color-border-strong))] hover:bg-surface-hover shadow-1',
        'outline' => 'bg-transparent text-default border-[rgb(var(--color-border-strong))] hover:bg-surface-hover',
        'ghost' => 'bg-transparent text-muted border-transparent hover:bg-surface-hover hover:text-default',
        'danger' => 'bg-[rgb(var(--color-danger))] text-white border-transparent hover:opacity-90 shadow-1',
        'danger-soft' => 'bg-[rgb(var(--color-danger-soft))] text-[rgb(var(--color-danger))] border-[rgb(var(--color-danger-border))] hover:bg-[rgb(var(--color-danger))] hover:text-white',
        'success' => 'bg-[rgb(var(--color-success))] text-white border-transparent hover:opacity-90 shadow-1',
    ];

    $sizes = [
        'xs' => 'h-8 px-2.5 text-xs gap-1.5',
        'sm' => 'h-9 px-3 text-sm gap-1.5',
        'md' => 'h-10 px-4 text-sm gap-2',
        'lg' => 'h-11 px-5 text-base gap-2',
    ];

    $classes = implode(' ', [
        'inline-flex items-center justify-center rounded-[var(--radius-md)] border font-semibold',
        'transition-[background-color,border-color,color,opacity,box-shadow] duration-150',
        'disabled:opacity-55 disabled:pointer-events-none select-none whitespace-nowrap',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
        $block ? 'w-full' : '',
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-ui.icon :name="$icon" class="size-4 shrink-0" />
        @endif
        <span>{{ $slot }}</span>
        @if ($iconTrailing)
            <x-ui.icon :name="$iconTrailing" class="size-4 shrink-0" />
        @endif
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($loading)
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{-- Loading buttons block duplicate submissions (05_Design_System.md §14). --}}
        <template x-if="false"></template>
        @if ($loading)
            <svg class="size-4 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z" />
            </svg>
        @elseif ($icon)
            <x-ui.icon :name="$icon" class="size-4 shrink-0" />
        @endif
        <span>{{ $slot }}</span>
        @if ($iconTrailing)
            <x-ui.icon :name="$iconTrailing" class="size-4 shrink-0" />
        @endif
    </button>
@endif
