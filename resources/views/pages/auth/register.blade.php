<x-layouts.guest
    :title="__('auth.register.title')"
    :heading="__('auth.register.heading')"
    :subheading="__('auth.register.subheading')"
>
    <form method="POST" action="{{ route('register.store') }}" class="space-y-5" x-data="dirtyForm(@js(__('You have unsaved changes.')))">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.field :label="__('auth.fields.first_name')" for="first_name" required>
                <x-form.input name="first_name" id="first_name" autocomplete="given-name" required autofocus />
            </x-form.field>

            <x-form.field :label="__('auth.fields.last_name')" for="last_name" required>
                <x-form.input name="last_name" id="last_name" autocomplete="family-name" required />
            </x-form.field>
        </div>

        <x-form.field :label="__('auth.fields.email')" for="email" required>
            <x-form.input
                name="email"
                type="email"
                id="email"
                icon="envelope"
                autocomplete="username"
                placeholder="you@company.com"
                required
            />
        </x-form.field>

        <x-form.field :label="__('auth.fields.phone')" for="phone" optional>
            <x-form.input name="phone" type="tel" id="phone" autocomplete="tel" placeholder="+91 98765 43210" />
        </x-form.field>

        <x-form.field :label="__('auth.fields.password')" for="password" required>
            <x-form.password name="password" id="password" autocomplete="new-password" strength required />

            <x-slot:help>
                <span class="sr-only">{{ __('auth.password_policy.title') }}</span>
            </x-slot:help>
        </x-form.field>

        {{-- Requirements are listed so users are never left guessing. --}}
        <div class="rounded-[var(--radius-md)] border border-default bg-surface-sunken px-3.5 py-3">
            <p class="text-xs font-semibold text-default">{{ __('auth.password_policy.title') }}</p>
            <ul class="mt-1.5 space-y-1">
                @foreach ($passwordRequirements as $requirement)
                    <li class="flex items-start gap-1.5 text-xs text-muted">
                        <x-ui.icon name="check-circle" class="mt-px size-3.5 shrink-0 text-subtle" />
                        <span>{{ $requirement }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <x-form.field :label="__('auth.fields.password_confirmation')" for="password_confirmation" required>
            <x-form.password
                name="password_confirmation"
                id="password_confirmation"
                autocomplete="new-password"
                required
            />
        </x-form.field>

        <x-form.field for="terms">
            <x-form.checkbox name="terms" id="terms" :label="__('auth.register.terms')" required />
        </x-form.field>

        <x-ui.button type="submit" block size="lg">{{ __('auth.register.submit') }}</x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm text-muted">
        {{ __('auth.register.have_account') }}
        <a href="{{ route('login') }}" class="font-semibold text-[rgb(var(--color-primary))] transition hover:underline">
            {{ __('auth.register.sign_in') }}
        </a>
    </p>
</x-layouts.guest>
