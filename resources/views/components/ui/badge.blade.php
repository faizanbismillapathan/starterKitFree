@props([
    'color' => 'neutral',
    'variant' => 'soft',
    'icon' => null,
    'dot' => false,
])

@php
    $tones = [
        'primary' => ['soft' => 'bg-[rgb(var(--color-primary-soft))] text-[rgb(var(--color-primary))] border-[rgb(var(--color-primary-border))]', 'dot' => 'bg-[rgb(var(--color-primary))]'],
        'success' => ['soft' => 'bg-[rgb(var(--color-success-soft))] text-[rgb(var(--color-success))] border-[rgb(var(--color-success-border))]', 'dot' => 'bg-[rgb(var(--color-success))]'],
        'warning' => ['soft' => 'bg-[rgb(var(--color-warning-soft))] text-[rgb(var(--color-warning))] border-[rgb(var(--color-warning-border))]', 'dot' => 'bg-[rgb(var(--color-warning))]'],
        'danger' => ['soft' => 'bg-[rgb(var(--color-danger-soft))] text-[rgb(var(--color-danger))] border-[rgb(var(--color-danger-border))]', 'dot' => 'bg-[rgb(var(--color-danger))]'],
        'info' => ['soft' => 'bg-[rgb(var(--color-info-soft))] text-[rgb(var(--color-info))] border-[rgb(var(--color-info-border))]', 'dot' => 'bg-[rgb(var(--color-info))]'],
        'neutral' => ['soft' => 'bg-[rgb(var(--color-neutral-soft))] text-muted border-[rgb(var(--color-neutral-border))]', 'dot' => 'bg-[rgb(var(--color-text-subtle))]'],
    ];
    $tone = $tones[$color] ?? $tones['neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium '.$tone['soft']]) }}>
    @if ($dot)
        <span class="size-1.5 rounded-full {{ $tone['dot'] }}" aria-hidden="true"></span>
    @elseif ($icon)
        <x-ui.icon :name="$icon" class="size-3.5" />
    @endif
    {{ $slot }}
</span>
