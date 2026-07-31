<x-layouts.guest
    :title="__('auth.verify_email.title')"
    :heading="__('auth.verify_email.heading')"
    :subheading="__('auth.verify_email.subheading', ['email' => auth()->user()->email])"
>
    <div class="space-y-5">
        <div class="flex items-start gap-3 rounded-[var(--radius-md)] border border-default bg-surface-sunken px-4 py-3.5">
            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-[rgb(var(--color-primary-soft))] text-[rgb(var(--color-primary))]">
                <x-ui.icon name="envelope" class="size-4.5" />
            </span>
            <p class="text-sm text-muted">{{ __('auth.verify_email.hint') }}</p>
        </div>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui.button type="submit" block size="lg" icon="arrow-path">
                {{ __('auth.verify_email.resend') }}
            </x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="ghost" block size="sm">
                {{ __('auth.verify_email.logout') }}
            </x-ui.button>
        </form>
    </div>
</x-layouts.guest>
