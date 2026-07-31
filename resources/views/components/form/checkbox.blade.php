@props([
    'name',
    'label' => null,
    'help' => null,
    'checked' => false,
    'value' => 1,
])

@php
    $id = $attributes->get('id', $name);
    $isChecked = (bool) old($name, $checked);
@endphp

<div class="flex items-start gap-2.5">
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $value }}"
        @checked($isChecked)
        {{ $attributes->merge([
            'class' => 'mt-0.5 size-4 shrink-0 rounded-[4px] border-[rgb(var(--color-border-strong))] bg-[rgb(var(--color-surface))] text-[rgb(var(--color-primary))] transition focus:ring-2 focus:ring-[rgb(var(--color-primary))] focus:ring-offset-0',
        ]) }}
    />

    @if ($label || $help)
        <div class="min-w-0 text-sm leading-snug">
            @if ($label)
                <label for="{{ $id }}" class="cursor-pointer font-medium text-default">{{ $label }}</label>
            @endif
            @if ($help)
                <p class="text-xs text-muted">{{ $help }}</p>
            @endif
        </div>
    @endif
</div>
