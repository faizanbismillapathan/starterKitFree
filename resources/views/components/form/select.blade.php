@props([
    'name',
    'options' => [],
    'value' => null,
    'placeholder' => null,
])

@php
    $hasError = $errors->has($name);
    $id = $attributes->get('id', $name);
    $current = old($name, $value);
@endphp

<div class="relative">
    <select
        name="{{ $name }}"
        id="{{ $id }}"
        @if ($hasError) aria-invalid="true" @endif
        {{ $attributes->merge(['class' => 'field-input appearance-none pr-10']) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-subtle">
        <x-ui.icon name="chevron-down" class="size-4" />
    </span>
</div>
