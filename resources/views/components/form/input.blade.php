@props([
    'name',
    'type' => 'text',
    'value' => null,
    'icon' => null,
    'suffix' => null,
])

@php
    $hasError = $errors->has($name);
    $id = $attributes->get('id', $name);
@endphp

<div class="relative">
    @if ($icon)
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-subtle">
            <x-ui.icon :name="$icon" class="size-4" />
        </span>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ old($name, $value) }}"
        @if ($hasError)
            aria-invalid="true"
            aria-describedby="{{ $id }}-error"
        @endif
        {{ $attributes->merge([
            'class' => 'field-input'.($icon ? ' pl-10' : '').($suffix ? ' pr-10' : ''),
        ]) }}
    />

    @if ($suffix)
        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-subtle">
            <x-ui.icon :name="$suffix" class="size-4" />
        </span>
    @endif
</div>
