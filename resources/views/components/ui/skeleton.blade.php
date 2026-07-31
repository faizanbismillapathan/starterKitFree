@props([
    'lines' => 3,
    'avatar' => false,
])

{{-- Preferred loading treatment (05_Design_System.md §23). --}}
<div {{ $attributes->merge(['class' => 'space-y-3']) }} role="status" aria-label="{{ __('ui.states.loading') }}">
    @for ($i = 0; $i < (int) $lines; $i++)
        <div class="flex items-center gap-3">
            @if ($avatar && $i === 0)
                <div class="skeleton size-10 rounded-full"></div>
            @endif
            <div class="skeleton h-3 flex-1" style="width: {{ [100, 82, 64, 90, 72][$i % 5] }}%"></div>
        </div>
    @endfor
    <span class="sr-only">{{ __('ui.states.loading') }}</span>
</div>
