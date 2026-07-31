@props([
    'user' => null,
    'src' => null,
    'initials' => null,
    'size' => 'md',
    'status' => null,
])

@php
    $sizes = [
        'xs' => 'size-7 text-[11px]',
        'sm' => 'size-8 text-xs',
        'md' => 'size-10 text-sm',
        'lg' => 'size-14 text-base',
        'xl' => 'size-20 text-xl',
    ];
    $dimension = $sizes[$size] ?? $sizes['md'];

    $image = $src ?? $user?->avatar_url;
    $label = $initials ?? $user?->initials ?? '?';
    $name = $user?->full_name;
@endphp

<span {{ $attributes->merge(['class' => 'relative inline-flex shrink-0']) }}>
    @if ($image)
        <img
            src="{{ $image }}"
            alt="{{ $name ? __('Profile picture of :name', ['name' => $name]) : '' }}"
            class="{{ $dimension }} rounded-full object-cover ring-1 ring-[rgb(var(--color-border))]"
            loading="lazy"
        />
    @else
        <span
            class="{{ $dimension }} inline-flex items-center justify-center rounded-full bg-[rgb(var(--color-primary-soft))] font-semibold uppercase text-[rgb(var(--color-primary))] ring-1 ring-[rgb(var(--color-primary-border))]"
            @if ($name) title="{{ $name }}" @endif
            aria-hidden="true"
        >{{ $label }}</span>
        @if ($name)
            <span class="sr-only">{{ $name }}</span>
        @endif
    @endif

    @if ($status)
        <span class="absolute -bottom-0.5 -right-0.5 size-3 rounded-full border-2 border-[rgb(var(--color-surface))] {{ $status === 'active' ? 'bg-[rgb(var(--color-success))]' : 'bg-[rgb(var(--color-text-subtle))]' }}"></span>
    @endif
</span>
