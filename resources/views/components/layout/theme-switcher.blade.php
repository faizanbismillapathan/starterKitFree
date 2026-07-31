@php
    $current = ($themeMode ?? \App\Enums\ThemeMode::System)->value;
    $modes = \App\Enums\ThemeMode::cases();
@endphp

<div x-data="themeSwitcher(@js($current))" class="relative">
    <button
        type="button"
        @click="open = ! open"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
        class="inline-flex size-9 items-center justify-center rounded-[var(--radius-md)] text-muted transition hover:bg-surface-hover hover:text-default"
        :aria-expanded="open.toString()"
        aria-haspopup="menu"
        aria-label="{{ __('theme.toggle') }}"
    >
        <span x-show="mode === 'light'">
            <x-ui.icon name="sun" class="size-5" />
        </span>
        <span x-show="mode === 'dark'" x-cloak>
            <x-ui.icon name="moon" class="size-5" />
        </span>
        <span x-show="mode === 'system'" x-cloak>
            <x-ui.icon name="computer-desktop" class="size-5" />
        </span>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="absolute right-0 z-40 mt-2 w-40 origin-top-right rounded-[var(--radius-lg)] border border-default bg-surface-raised py-1.5 shadow-3"
        role="menu"
    >
        @foreach ($modes as $mode)
            {{--
                The mode is passed through a data attribute because Blade
                directives cannot be nested inside Alpine expressions.
            --}}
            <button
                type="button"
                role="menuitemradio"
                data-mode="{{ $mode->value }}"
                @click="select($el.dataset.mode)"
                :aria-checked="isActive($el.dataset.mode).toString()"
                class="flex w-full items-center gap-2.5 px-3 py-2 text-sm text-default transition hover:bg-surface-hover"
                :class="isActive('{{ $mode->value }}') && 'font-semibold text-[rgb(var(--color-primary))]'"
            >
                <x-ui.icon :name="$mode->icon()" class="size-4 opacity-70" />
                <span>{{ $mode->label() }}</span>
                <span class="ml-auto" x-show="isActive('{{ $mode->value }}')" x-cloak>
                    <x-ui.icon name="check-circle" class="size-4" />
                </span>
            </button>
        @endforeach
    </div>
</div>

{{-- Preference is persisted server-side for signed-in users. --}}
<form id="theme-form" method="POST" action="{{ route('theme.update') }}" class="hidden">
    @csrf
    <input type="hidden" name="theme" value="{{ $current }}" />
</form>
