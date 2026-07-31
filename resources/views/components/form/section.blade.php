@props([
    'title',
    'description' => null,
])

{{-- Large forms are divided into labelled sections (12_Form_System.md §7). --}}
<div {{ $attributes->merge(['class' => 'grid gap-6 lg:grid-cols-[minmax(0,17rem)_1fr]']) }}>
    <div class="lg:pt-1">
        <h2 class="text-sm font-semibold text-default">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 text-sm text-muted">{{ $description }}</p>
        @endif
    </div>

    <div class="min-w-0">{{ $slot }}</div>
</div>
