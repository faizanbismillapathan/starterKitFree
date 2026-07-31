<x-layouts.guest
    :title="__('auth.forgot_password.title')"
    :heading="__('auth.forgot_password.heading')"
    :subheading="__('auth.forgot_password.subheading')"
>
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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

        <x-ui.button type="submit" block size="lg">{{ __('auth.forgot_password.submit') }}</x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm">
        <a
            href="{{ route('login') }}"
            class="inline-flex items-center gap-1.5 font-medium text-muted transition hover:text-default"
        >
            <x-ui.icon name="arrow-left" class="size-4" />
            {{ __('auth.forgot_password.back') }}
        </a>
    </p>
</x-layouts.guest>
