@props([
    'title',
    'message' => null,
    'action',
    'method' => 'DELETE',
    'confirmLabel' => null,
    'variant' => 'danger',
    'requiresPassword' => false,
])

{{--
    Confirmation dialog required before destructive actions
    (14_Modal_System.md §10). Focus is trapped and returned to the trigger.
--}}
<div x-data="confirmAction()" class="inline-flex">
    <span @click="show()">{{ $trigger }}</span>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $id = 'confirm-'.Str::random(6) }}-title"
            @keydown.escape.window="close()"
            @keydown.tab.prevent="
                (() => {
                    const focusable = $refs.panel.querySelectorAll('button, [href], input, select, textarea');
                    if (! focusable.length) return;
                    const list = Array.from(focusable);
                    const index = list.indexOf(document.activeElement);
                    const next = $event.shiftKey ? index - 1 : index + 1;
                    (list[(next + list.length) % list.length]).focus();
                })()
            "
        >
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                class="fixed inset-0 bg-[rgb(var(--color-text))]/45 backdrop-blur-[2px]"
                @click="close()"
                aria-hidden="true"
            ></div>

            <div class="flex min-h-full items-end justify-center p-4 sm:items-center">
                <div
                    x-ref="panel"
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95 sm:translate-y-0"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95 sm:translate-y-0"
                    class="relative w-full max-w-md rounded-[var(--radius-xl)] border border-default bg-surface-raised shadow-4"
                >
                    <div class="flex gap-4 p-5">
                        <span @class([
                            'inline-flex size-10 shrink-0 items-center justify-center rounded-full',
                            'bg-[rgb(var(--color-danger-soft))] text-[rgb(var(--color-danger))]' => $variant === 'danger',
                            'bg-[rgb(var(--color-warning-soft))] text-[rgb(var(--color-warning))]' => $variant === 'warning',
                            'bg-[rgb(var(--color-primary-soft))] text-[rgb(var(--color-primary))]' => $variant === 'primary',
                        ])>
                            <x-ui.icon :name="$variant === 'danger' ? 'exclamation-triangle' : 'information-circle'" class="size-5" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <h2 id="{{ $id }}-title" class="text-base font-semibold text-default">{{ $title }}</h2>

                            @if ($message)
                                <p class="mt-1 text-sm text-muted">{{ $message }}</p>
                            @endif

                            <form
                                x-ref="form"
                                method="POST"
                                action="{{ $action }}"
                                @submit="submitting = true"
                                class="mt-4 space-y-4"
                            >
                                @csrf
                                @method($method)

                                @if ($requiresPassword)
                                    <div class="space-y-1.5">
                                        <label for="{{ $id }}-password" class="text-sm font-medium text-default">
                                            {{ __('auth.fields.password') }}
                                        </label>
                                        <input
                                            type="password"
                                            name="password"
                                            id="{{ $id }}-password"
                                            autocomplete="current-password"
                                            class="field-input"
                                            data-autofocus
                                            required
                                        />
                                    </div>
                                @endif

                                {{ $slot ?? '' }}

                                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                    <x-ui.button type="button" variant="secondary" size="sm" @click="close()">
                                        {{ __('ui.actions.cancel') }}
                                    </x-ui.button>

                                    {{--
                                        Blade directives cannot appear inside a
                                        component tag, so the initial focus flag
                                        is bound. Null attributes are omitted.
                                    --}}
                                    <x-ui.button
                                        type="submit"
                                        :variant="$variant"
                                        size="sm"
                                        x-bind:disabled="submitting"
                                        :data-autofocus="$requiresPassword ? null : true"
                                    >
                                        {{ $confirmLabel ?? __('ui.actions.confirm') }}
                                    </x-ui.button>
                                </div>
                            </form>
                        </div>

                        <button
                            type="button"
                            @click="close()"
                            class="-m-1 h-fit rounded-[var(--radius-sm)] p-1 text-subtle transition hover:bg-surface-hover hover:text-default"
                            aria-label="{{ __('ui.actions.close') }}"
                        >
                            <x-ui.icon name="x-mark" class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
