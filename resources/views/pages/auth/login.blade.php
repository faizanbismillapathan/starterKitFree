<x-layouts.guest
    :title="__('auth.login.title')"
    :heading="__('auth.login.heading')"
    :subheading="__('auth.login.subheading')"
>
    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
        @csrf

        @if ($errors->any())
            <x-ui.alert type="danger">{{ $errors->first() }}</x-ui.alert>
        @endif

        <x-form.field :label="__('auth.fields.email')" for="email" required>
            <x-form.input
                name="email"
                type="email"
                id="email"
                icon="envelope"
                autocomplete="username"
                placeholder="you@company.com"
                required
                autofocus
            />
        </x-form.field>

        <div class="space-y-1.5">
            <div class="flex items-baseline justify-between gap-2">
                <label for="password" class="text-sm font-medium text-default">
                    {{ __('auth.fields.password') }}
                    <span class="text-[rgb(var(--color-danger))]" aria-hidden="true">*</span>
                    <span class="sr-only">({{ __('ui.required') }})</span>
                </label>

                <a
                    href="{{ route('password.request') }}"
                    class="text-xs font-medium text-[rgb(var(--color-primary))] transition hover:underline"
                >
                    {{ __('auth.login.forgot') }}
                </a>
            </div>

            <x-form.password name="password" id="password" autocomplete="current-password" required />

            @error('password')
                <p class="flex items-start gap-1.5 text-xs font-medium text-[rgb(var(--color-danger))]" role="alert">
                    <x-ui.icon name="exclamation-triangle" class="mt-px size-3.5 shrink-0" />
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        <x-form.checkbox name="remember" :label="__('auth.login.remember')" />

        <x-ui.button type="submit" block size="lg">{{ __('auth.login.submit') }}</x-ui.button>
    </form>

    @if (config('starter_kit.auth.registration_enabled'))
        <p class="mt-6 text-center text-sm text-muted">
            {{ __('auth.login.no_account') }}
            <a
                href="{{ route('register') }}"
                class="font-semibold text-[rgb(var(--color-primary))] transition hover:underline"
            >
                {{ __('auth.login.create_account') }}
            </a>
        </p>
    @endif
</x-layouts.guest>
