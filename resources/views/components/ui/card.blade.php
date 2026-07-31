@props([
    'title' => null,
    'description' => null,
    'padding' => true,
])

<section {{ $attributes->merge(['class' => 'surface-card overflow-hidden']) }}>
    @if ($title || $description || isset($actions))
        <header class="flex flex-wrap items-start justify-between gap-4 border-b border-default px-5 py-4">
            <div class="min-w-0">
                @if ($title)
                    <h2 class="text-sm font-semibold text-default">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mt-0.5 text-sm text-muted">{{ $description }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    <div @class(['px-5 py-5' => $padding])>
        {{ $slot }}
    </div>

    @isset($footer)
        <footer class="border-t border-default bg-surface-sunken px-5 py-3">
            {{ $footer }}
        </footer>
    @endisset
</section>
