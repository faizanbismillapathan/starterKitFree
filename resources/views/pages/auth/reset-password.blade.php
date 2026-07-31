<x-layouts.guest
    :title="__('auth.reset_password.title')"
    :heading="__('auth.reset_password.heading')"
    :subheading="__('auth.reset_password.subheading')"
>
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}" />

        @if ($errors->any())
            <x-ui.alert type="danger">{{ $errors->first() }}</x-ui.alert>
        @endif

        <x-form.field :label="__('auth.fields.email')" for="email" required>
            <x-form.input
                name="email"
                type="email"
                id="email"
                icon="envelope"
                :value="$email"
                autocomplete="username"
                required
                readonly
            />
        </x-form.field>

        <x-form.field :label="__('auth.fields.password')" for="password" required>
            <x-form.password name="password" id="password" autocomplete="new-password" strength required autofocus />
        </x-form.field>

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
            <x-form.password name="password_confirmation" id="password_confirmation" autocomplete="new-password" required />
        </x-form.field>

        <x-ui.button type="submit" block size="lg">{{ __('auth.reset_password.submit') }}</x-ui.button>
    </form>
</x-layouts.guest>
