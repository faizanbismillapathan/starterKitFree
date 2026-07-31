@props([
    'title' => null,
    'heading' => null,
    'subheading' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="color-scheme" content="light dark" />

    <title>{{ $title ? $title.' · '.config('starter_kit.name') : config('starter_kit.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

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
</head>
<body class="h-full bg-canvas text-default antialiased">
    {{--
        Split authentication layout. Authentication pages must feel like a
        premium SaaS product and never resemble Laravel's default UI
        (05_Design_System.md §33).
    --}}
    <div class="flex min-h-full">
        <div class="flex w-full flex-col justify-center px-4 py-10 sm:px-6 lg:w-[52%] lg:px-14 xl:px-20">
            <div class="mx-auto w-full max-w-[25rem]">
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                        <span class="inline-flex size-9 items-center justify-center rounded-[var(--radius-md)] bg-[rgb(var(--color-primary))] text-[rgb(var(--color-on-primary))]">
                            <x-ui.icon name="bolt" class="size-5" />
                        </span>
                        <span class="text-sm font-semibold text-default">{{ config('starter_kit.name') }}</span>
                    </a>

                    <x-layout.theme-switcher />
                </div>

                <div class="mt-10">
                    @if ($heading)
                        <h1 class="text-2xl font-semibold tracking-[-0.015em] text-default">{{ $heading }}</h1>
                    @endif

                    @if ($subheading)
                        <p class="mt-2 text-sm text-muted">{{ $subheading }}</p>
                    @endif
                </div>

                @if (session('status'))
                    <x-ui.alert type="success" class="mt-6">{{ session('status') }}</x-ui.alert>
                @endif

                <div class="mt-7">
                    {{ $slot }}
                </div>
            </div>

            <p class="mx-auto mt-12 w-full max-w-[25rem] text-xs text-subtle">
                &copy; {{ now()->year }} {{ config('starter_kit.company.name') }}
            </p>
        </div>

        {{-- Decorative panel, hidden from assistive technology. --}}
        <div
            class="relative hidden overflow-hidden bg-[rgb(var(--color-surface-sunken))] lg:block lg:w-[48%]"
            aria-hidden="true"
        >
            <div class="absolute inset-0 bg-[radial-gradient(120%_90%_at_15%_10%,rgb(var(--color-primary)/0.14),transparent_58%)]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(80%_70%_at_85%_95%,rgb(var(--color-info)/0.10),transparent_60%)]"></div>

            <svg class="absolute inset-0 size-full opacity-[0.35]" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="auth-grid" width="44" height="44" patternUnits="userSpaceOnUse">
                        <path d="M44 0H0v44" fill="none" stroke="rgb(var(--color-border))" stroke-width="1" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#auth-grid)" />
            </svg>

            <div class="relative flex h-full flex-col justify-center px-14 xl:px-20">
                <blockquote class="max-w-md">
                    <p class="text-xl font-medium leading-relaxed tracking-[-0.01em] text-default">
                        &ldquo;A production-ready foundation so teams can focus on business logic
                        instead of rebuilding authentication and dashboards for every project.&rdquo;
                    </p>
                    <footer class="mt-6 text-sm text-muted">
                        {{ config('starter_kit.name') }} &mdash; {{ config('starter_kit.edition') }} Edition
                    </footer>
                </blockquote>

                <dl class="mt-12 grid max-w-md grid-cols-3 gap-6 border-t border-default pt-8">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-subtle">Architecture</dt>
                        <dd class="mt-1 text-sm font-semibold text-default">Layered</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-subtle">Security</dt>
                        <dd class="mt-1 text-sm font-semibold text-default">RBAC</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-subtle">API</dt>
                        <dd class="mt-1 text-sm font-semibold text-default">REST v1</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <x-ui.toast-stack />
</body>
</html>
