@props([
    'title',
    'caption' => null,
    'config' => [],
    'height' => 260,
])

<x-ui.card :title="$title" :description="$caption" :padding="false">
    <div class="px-2 pb-2 pt-4" x-data="chart(@js($config))" x-on:destroy="destroy()">
        {{-- Skeleton shown until the chart library resolves (§23). --}}
        <div x-ref="placeholder" class="px-3 pb-3">
            <div class="skeleton w-full" style="height: {{ $height }}px"></div>
        </div>

        <div x-ref="canvas" role="img" aria-label="{{ $title }}"></div>
    </div>
</x-ui.card>
