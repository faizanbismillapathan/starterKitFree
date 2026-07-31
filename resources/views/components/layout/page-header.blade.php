@props([
    'title',
    'description' => null,
    'back' => null,
])

{{-- Standard page header (11_Layout_System.md §11). --}}
<header {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="min-w-0">
        @if ($back)
            <a
                href="{{ $back }}"
                class="mb-2 inline-flex items-center gap-1.5 text-sm font-medium text-muted transition hover:text-default"
            >
                <x-ui.icon name="arrow-left" class="size-4" />
                {{ __('ui.actions.previous') }}
            </a>
        @endif

        <h1 class="truncate text-2xl font-semibold tracking-[-0.01em] text-default">{{ $title }}</h1>

        @if ($description)
            <p class="mt-1 text-sm text-muted">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</header>
