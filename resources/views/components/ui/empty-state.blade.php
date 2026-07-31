@props([
    'icon' => 'inbox',
    'title',
    'message' => null,
    'compact' => false,
])

{{-- Empty states always educate and offer a next step (00A §13). --}}
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center '.($compact ? 'px-4 py-8' : 'px-6 py-14')]) }}>
    <span class="inline-flex items-center justify-center rounded-full bg-[rgb(var(--color-primary-soft))] p-3 ring-1 ring-[rgb(var(--color-primary-border))]">
        <x-ui.icon :name="$icon" class="size-6 text-[rgb(var(--color-primary))]" />
    </span>

    <h3 class="mt-4 text-sm font-semibold text-default">{{ $title }}</h3>

    @if ($message)
        <p class="mt-1 max-w-sm text-sm text-muted">{{ $message }}</p>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">{{ $slot }}</div>
    @endif
</div>
