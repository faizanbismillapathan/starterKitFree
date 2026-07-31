@props(['user'])

<x-ui.dropdown align="right" width="w-60">
    <x-slot:trigger>
        <button
            type="button"
            class="flex items-center gap-2 rounded-[var(--radius-md)] p-1 pr-2 transition hover:bg-surface-hover"
            aria-haspopup="menu"
        >
            <x-ui.avatar :user="$user" size="sm" />
            <span class="hidden min-w-0 text-left sm:block">
                <span class="block max-w-[9rem] truncate text-sm font-medium leading-tight text-default">
                    {{ $user->first_name }}
                </span>
            </span>
            <x-ui.icon name="chevron-down" class="size-4 shrink-0 text-subtle" />
        </button>
    </x-slot:trigger>

    <div class="border-b border-default px-3 pb-2.5 pt-1.5">
        <p class="text-[11px] font-medium uppercase tracking-wide text-subtle">
            {{ __('navigation.user_menu.signed_in_as') }}
        </p>
        <p class="mt-0.5 truncate text-sm font-semibold text-default">{{ $user->full_name }}</p>
        <p class="truncate text-xs text-muted">{{ $user->email }}</p>
    </div>

    <div class="py-1">
        @can(\App\Enums\Permission::ProfileView->value)
            <x-ui.dropdown-item :href="route('profile.edit')" icon="user-circle">
                {{ __('navigation.user_menu.profile') }}
            </x-ui.dropdown-item>
        @endcan

        @can(\App\Enums\Permission::SessionView->value)
            <x-ui.dropdown-item :href="route('profile.security')" icon="shield-check">
                {{ __('navigation.user_menu.security') }}
            </x-ui.dropdown-item>
        @endcan
    </div>

    <div class="border-t border-default pt-1">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.dropdown-item type="submit" icon="logout" danger>
                {{ __('navigation.user_menu.sign_out') }}
            </x-ui.dropdown-item>
        </form>
    </div>
</x-ui.dropdown>
