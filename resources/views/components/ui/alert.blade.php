@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $tones = [
        'success' => ['class' => 'bg-[rgb(var(--color-success-soft))] border-[rgb(var(--color-success-border))] text-[rgb(var(--color-success))]', 'icon' => 'check-circle'],
        'warning' => ['class' => 'bg-[rgb(var(--color-warning-soft))] border-[rgb(var(--color-warning-border))] text-[rgb(var(--color-warning))]', 'icon' => 'exclamation-triangle'],
        'danger' => ['class' => 'bg-[rgb(var(--color-danger-soft))] border-[rgb(var(--color-danger-border))] text-[rgb(var(--color-danger))]', 'icon' => 'x-circle'],
        'info' => ['class' => 'bg-[rgb(var(--color-info-soft))] border-[rgb(var(--color-info-border))] text-[rgb(var(--color-info))]', 'icon' => 'information-circle'],
    ];
    $tone = $tones[$type] ?? $tones['info'];
@endphp

<div
    @if ($dismissible) x-data="{ shown: true }" x-show="shown" x-transition.opacity.duration.200ms @endif
    role="{{ in_array($type, ['danger', 'warning'], true) ? 'alert' : 'status' }}"
    {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-[var(--radius-md)] border px-4 py-3 '.$tone['class']]) }}
>
    <x-ui.icon :name="$tone['icon']" class="mt-0.5 size-5 shrink-0" />

    <div class="min-w-0 flex-1 text-sm">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div @class(['mt-0.5' => $title])>{{ $slot }}</div>
    </div>

    @if ($dismissible)
        <button
            type="button"
            @click="shown = false"
            class="-m-1 shrink-0 rounded-[var(--radius-sm)] p-1 opacity-70 transition hover:opacity-100"
            aria-label="{{ __('ui.dismiss') }}"
        >
            <x-ui.icon name="x-mark" class="size-4" />
        </button>
    @endif
</div>
