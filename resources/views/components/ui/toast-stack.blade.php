{{--
    Toast region. Session flashes are converted to toasts on load and further
    messages can be pushed with a `toast` window event
    (15_Notification_System.md §6-§7).
--}}
<div
    x-data="toastStack()"
    x-init="
        @if (session('status')) push({ type: 'success', message: @js(session('status')) }); @endif
        @if (session('error')) push({ type: 'danger', message: @js(session('error')) }); @endif
    "
    class="pointer-events-none fixed inset-x-0 bottom-0 z-[60] flex flex-col items-center gap-2 p-4 sm:inset-x-auto sm:right-0 sm:top-0 sm:items-end"
    role="region"
    aria-label="{{ __('Notifications') }}"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-3"
            x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-[var(--radius-lg)] border bg-surface-raised px-4 py-3 shadow-3"
            :class="{
                'border-[rgb(var(--color-success-border))]': toast.type === 'success',
                'border-[rgb(var(--color-danger-border))]': toast.type === 'danger' || toast.type === 'critical',
                'border-[rgb(var(--color-warning-border))]': toast.type === 'warning',
                'border-[rgb(var(--color-info-border))]': toast.type === 'info',
            }"
            role="status"
            aria-live="polite"
        >
            <span
                class="mt-0.5 shrink-0"
                :class="{
                    'text-[rgb(var(--color-success))]': toast.type === 'success',
                    'text-[rgb(var(--color-danger))]': toast.type === 'danger' || toast.type === 'critical',
                    'text-[rgb(var(--color-warning))]': toast.type === 'warning',
                    'text-[rgb(var(--color-info))]': toast.type === 'info',
                }"
            >
                <x-ui.icon name="check-circle" class="size-5" x-show="toast.type === 'success'" />
                <x-ui.icon name="x-circle" class="size-5" x-show="toast.type === 'danger' || toast.type === 'critical'" />
                <x-ui.icon name="exclamation-triangle" class="size-5" x-show="toast.type === 'warning'" />
                <x-ui.icon name="information-circle" class="size-5" x-show="toast.type === 'info'" />
            </span>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-default" x-show="toast.title" x-text="toast.title"></p>
                <p class="text-sm text-muted" x-text="toast.message"></p>
            </div>

            <button
                type="button"
                @click="dismiss(toast.id)"
                class="-m-1 shrink-0 rounded-[var(--radius-sm)] p-1 text-subtle transition hover:bg-surface-hover hover:text-default"
                aria-label="{{ __('ui.dismiss') }}"
            >
                <x-ui.icon name="x-mark" class="size-4" />
            </button>
        </div>
    </template>
</div>
