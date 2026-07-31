<x-layouts.guest
    :title="__('auth.confirm_password.title')"
    :heading="__('auth.confirm_password.heading')"
    :subheading="__('auth.confirm_password.subheading')"
>
    <form method="POST" action="{{ route('password.confirm.store') }}" class="space-y-5">
        @csrf

        @if ($errors->any())
            <x-ui.alert type="danger">{{ $errors->first() }}</x-ui.alert>
        @endif

        <x-form.field :label="__('auth.fields.password')" for="password" required>
            <x-form.password name="password" id="password" autocomplete="current-password" required autofocus />
        </x-form.field>

        <x-ui.button type="submit" block size="lg">{{ __('auth.confirm_password.submit') }}</x-ui.button>
    </form>
</x-layouts.guest>
