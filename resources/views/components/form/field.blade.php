@props([
    'label' => null,
    'for' => null,
    'help' => null,
    'error' => null,
    'required' => false,
    'optional' => false,
])

@php
    $error = $error ?? ($for ? $errors->first($for) : null);
@endphp

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <div class="flex items-baseline justify-between gap-2">
            <label @if ($for) for="{{ $for }}" @endif class="text-sm font-medium text-default">
                {{ $label }}
                @if ($required)
                    <span class="text-[rgb(var(--color-danger))]" aria-hidden="true">*</span>
                    <span class="sr-only">({{ __('ui.required') }})</span>
                @endif
            </label>
            @if ($optional)
                <span class="text-xs text-subtle">{{ __('ui.optional') }}</span>
            @endif
        </div>
    @endif

    {{ $slot }}

    {{-- Help text only where it improves usability (12_Form_System.md §12). --}}
    @if ($help && ! $error)
        <p class="text-xs text-muted">{{ $help }}</p>
    @endif

    @if ($error)
        <p class="flex items-start gap-1.5 text-xs font-medium text-[rgb(var(--color-danger))]" role="alert">
            <x-ui.icon name="exclamation-triangle" class="mt-px size-3.5 shrink-0" />
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
