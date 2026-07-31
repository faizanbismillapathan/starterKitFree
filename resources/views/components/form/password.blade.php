@props([
    'name' => 'password',
    'strength' => false,
    'autocomplete' => 'current-password',
])

@php
    $hasError = $errors->has($name);
    $id = $attributes->get('id', $name);
@endphp

<div
    x-data="passwordField({
        strength: {{ $strength ? 'true' : 'false' }},
        labels: {
            weak: @js(__('Weak')),
            fair: @js(__('Fair')),
            good: @js(__('Good')),
            strong: @js(__('Strong')),
        },
    })"
    class="space-y-2"
>
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-subtle">
            <x-ui.icon name="lock-closed" class="size-4" />
        </span>

        <input
            :type="inputType"
            name="{{ $name }}"
            id="{{ $id }}"
            autocomplete="{{ $autocomplete }}"
            x-model="value"
            @if ($hasError) aria-invalid="true" @endif
            {{ $attributes->merge(['class' => 'field-input pl-10 pr-11']) }}
        />

        <button
            type="button"
            @click="toggle()"
            class="absolute inset-y-0 right-0 flex items-center px-3 text-subtle transition hover:text-default"
            :aria-label="visible ? @js(__('ui.hide_password')) : @js(__('ui.show_password'))"
            :aria-pressed="visible.toString()"
            tabindex="-1"
        >
            <x-ui.icon name="eye" class="size-4" x-show="! visible" />
            <x-ui.icon name="eye-slash" class="size-4" x-show="visible" x-cloak />
        </button>
    </div>

    @if ($strength)
        <div x-show="value.length > 0" x-cloak class="space-y-1">
            <div class="flex gap-1" role="presentation">
                <template x-for="step in 4" :key="step">
                    <span
                        class="h-1 flex-1 rounded-full transition-colors duration-200"
                        :class="step <= score ? strengthClass : 'bg-[rgb(var(--color-neutral-soft))]'"
                    ></span>
                </template>
            </div>
            <p class="text-xs text-muted" aria-live="polite">
                <span x-text="strengthLabel"></span>
            </p>
        </div>
    @endif
</div>
