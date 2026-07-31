@props([
    'title' => null,
    'breadcrumbs' => [],
])

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full"
    @if (($themeMode ?? null)?->value === 'dark') style="color-scheme: dark" @endif
>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="color-scheme" content="light dark" />

    <title>{{ $title ? $title.' · '.config('starter_kit.name') : config('starter_kit.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    {{--
        Applied before first paint so the theme never flashes
        (11_Layout_System.md §24 — minimise layout shifts).
    --}}
    <script nonce="@cspNonce">
        (() => {
            const mode = @json(($themeMode ?? \App\Enums\ThemeMode::System)->value);
            const dark = mode === 'dark'
                || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full bg-canvas text-default antialiased">
    <a href="#main-content" class="skip-link">{{ __('navigation.skip_to_content') }}</a>

    <div x-data="{ sidebarOpen: false }" class="min-h-full">
        {{-- Mobile drawer overlay (11_Layout_System.md §14). --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-[rgb(var(--color-text))]/40 lg:hidden"
            aria-hidden="true"
        ></div>

        <aside
            x-show="sidebarOpen || window.innerWidth >= 1024"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            @keydown.escape.window="sidebarOpen = false"
            @resize.window="if (window.innerWidth >= 1024) sidebarOpen = false"
            class="fixed inset-y-0 left-0 z-50 w-[17rem] border-r border-default bg-surface lg:translate-x-0"
            x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            x-cloak
        >
            <button
                type="button"
                @click="sidebarOpen = false"
                class="absolute right-3 top-4 inline-flex size-8 items-center justify-center rounded-[var(--radius-md)] text-muted transition hover:bg-surface-hover lg:hidden"
                aria-label="{{ __('navigation.close_sidebar') }}"
            >
                <x-ui.icon name="x-mark" class="size-4" />
            </button>

            <x-layout.sidebar />
        </aside>

        <div class="lg:pl-[17rem]">
            {{-- Fixed header (11_Layout_System.md §6). --}}
            <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-default bg-surface/85 px-4 backdrop-blur-md sm:px-6">
                <button
                    type="button"
                    @click="sidebarOpen = true"
                    class="inline-flex size-9 items-center justify-center rounded-[var(--radius-md)] text-muted transition hover:bg-surface-hover hover:text-default lg:hidden"
                    aria-label="{{ __('navigation.toggle_sidebar') }}"
                >
                    <x-ui.icon name="bars-3" class="size-5" />
                </button>

                <div class="min-w-0 flex-1">
                    <x-layout.breadcrumb :items="$breadcrumbs" class="hidden sm:block" />
                </div>

                <div class="flex shrink-0 items-center gap-1">
                    <x-layout.theme-switcher />

                    @auth
                        <span class="mx-1 hidden h-6 w-px bg-[rgb(var(--color-border))] sm:block"></span>
                        <x-layout.user-menu :user="auth()->user()" />
                    @endauth
                </div>
            </header>

            <main id="main-content" class="mx-auto w-full max-w-[90rem] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @if (count($breadcrumbs) > 0)
                    <x-layout.breadcrumb :items="$breadcrumbs" class="mb-4 sm:hidden" />
                @endif

                {{ $slot }}
            </main>

            <footer class="border-t border-default px-4 py-5 sm:px-6 lg:px-8">
                <div class="mx-auto flex max-w-[90rem] flex-col items-center justify-between gap-2 text-xs text-subtle sm:flex-row">
                    <p>&copy; {{ now()->year }} {{ config('starter_kit.company.name') }}</p>
                    <p>v{{ config('starter_kit.version') }} &middot; {{ config('starter_kit.edition') }} Edition</p>
                </div>
            </footer>
        </div>
    </div>

    <x-ui.toast-stack />

    @stack('scripts')
</body>
</html>
