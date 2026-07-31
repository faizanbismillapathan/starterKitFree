@props(['code', 'title', 'message'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title }} · {{ config('starter_kit.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script nonce="@cspNonce">
        (() => {
            const dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        })();
    </script>
    @vite(['resources/css/app.css'])
</head>
<body class="flex h-full flex-col bg-canvas text-default antialiased">
    <main class="flex flex-1 items-center justify-center px-4 py-16">
        <div class="w-full max-w-md text-center">
            <p class="text-sm font-semibold tracking-widest text-[rgb(var(--color-primary))]">
                {{ __('Error') }} {{ $code }}
            </p>

            <h1 class="mt-3 text-3xl font-semibold tracking-[-0.015em] text-default sm:text-4xl">
                {{ $title }}
            </h1>

            <p class="mt-3 text-sm text-muted">{{ $message }}</p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-2">
                @auth
                    <a
                        href="{{ url('/dashboard') }}"
                        class="inline-flex h-10 items-center gap-2 rounded-[var(--radius-md)] bg-[rgb(var(--color-primary))] px-4 text-sm font-semibold text-[rgb(var(--color-on-primary))] transition hover:bg-[rgb(var(--color-primary-hover))]"
                    >{{ __('errors.back_to_dashboard') }}</a>
                @else
                    <a
                        href="{{ url('/login') }}"
                        class="inline-flex h-10 items-center gap-2 rounded-[var(--radius-md)] bg-[rgb(var(--color-primary))] px-4 text-sm font-semibold text-[rgb(var(--color-on-primary))] transition hover:bg-[rgb(var(--color-primary-hover))]"
                    >{{ __('errors.back_to_home') }}</a>
                @endauth
            </div>

            <p class="mt-10 text-xs text-subtle">
                {{ config('starter_kit.name') }} &middot; v{{ config('starter_kit.version') }}
            </p>
        </div>
    </main>
</body>
</html>
