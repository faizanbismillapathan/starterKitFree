@props([
    'align' => 'right',
    'width' => 'w-56',
])

@php
    $alignment = $align === 'left' ? 'left-0 origin-top-left' : 'right-0 origin-top-right';
@endphp

<div
    class="relative"
    x-data="{
        open: false,
        toggle() {
            this.open = ! this.open;
            this.syncTrigger();
        },
        close() {
            if (! this.open) {
                return;
            }

            this.open = false;
            this.syncTrigger();
        },
        /*
         * The trigger markup is supplied by the caller, so the expanded state
         * is reflected onto whichever control it rendered. Without this
         * assistive technology never learns the menu opened
         * (05A_Component_Inventory.md §17).
         */
        syncTrigger() {
            const trigger = $refs.trigger?.querySelector('button, [role=button], a');

            trigger?.setAttribute('aria-expanded', this.open ? 'true' : 'false');
        },
    }"
    x-init="syncTrigger()"
    @keydown.escape.window="close()"
>
    <div x-ref="trigger" @click="toggle()" @click.outside="close()">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-40 mt-2 {{ $width }} {{ $alignment }} rounded-[var(--radius-lg)] border border-default bg-surface-raised py-1.5 shadow-3"
        role="menu"
        @click="close()"
    >
        {{ $slot }}
    </div>
</div>
