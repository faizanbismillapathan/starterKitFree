@php
    use App\Enums\Permission;
    use App\Support\BreadcrumbBuilder;

    $breadcrumbs = BreadcrumbBuilder::forDashboard()
        ->add(__('profile.security_title'))
        ->toArray();
@endphp

<x-layouts.app :title="__('profile.security_title')" :breadcrumbs="$breadcrumbs">
    <x-layout.page-header
        :title="__('profile.security_heading')"
        :description="__('profile.security_subheading')"
    />

    <div class="mt-6 space-y-6">
        <x-form.errors />

        {{-- Change password --}}
        <x-ui.card>
            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PUT')

                <x-form.section
                    :title="__('profile.sections.password')"
                    :description="__('profile.sections.password_caption')"
                >
                    <div class="max-w-md space-y-4">
                        <x-form.field :label="__('profile.fields.current_password')" for="current_password" required>
                            <x-form.password name="current_password" id="current_password" autocomplete="current-password" required />
                        </x-form.field>

                        <x-form.field :label="__('profile.fields.new_password')" for="password" required>
                            <x-form.password name="password" id="password" autocomplete="new-password" strength required />
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

                        <x-form.field :label="__('profile.fields.confirm_password')" for="password_confirmation" required>
                            <x-form.password name="password_confirmation" id="password_confirmation" autocomplete="new-password" required />
                        </x-form.field>
                    </div>
                </x-form.section>

                <div class="mt-6 flex justify-end border-t border-default pt-5">
                    <x-ui.button type="submit" icon="key">{{ __('profile.actions.change_password') }}</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        {{-- Active sessions --}}
        <x-ui.card
            :title="__('profile.sections.sessions')"
            :description="__('profile.sections.sessions_caption')"
            :padding="false"
        >
            @can(Permission::SessionRevoke->value)
                @if ($sessions->where('is_current', false)->isNotEmpty())
                    <x-slot:actions>
                        <x-modal.confirm
                            :title="__('profile.revoke_others_confirm_title')"
                            :message="__('profile.revoke_others_confirm_message')"
                            :action="route('profile.sessions.destroy-others')"
                            method="DELETE"
                            :confirm-label="__('profile.actions.revoke_others')"
                            requires-password
                        >
                            <x-slot:trigger>
                                <x-ui.button variant="danger-soft" size="sm" icon="logout">
                                    {{ __('profile.actions.revoke_others') }}
                                </x-ui.button>
                            </x-slot:trigger>
                        </x-modal.confirm>
                    </x-slot:actions>
                @endif
            @endcan

            @if ($sessions->isEmpty())
                <x-ui.empty-state
                    icon="device"
                    :title="__('profile.sessions_empty_title')"
                    :message="__('profile.sessions_empty_message')"
                    compact
                />
            @else
                <ul class="divide-y divide-[rgb(var(--color-border))]">
                    @foreach ($sessions as $session)
                        <li class="flex flex-wrap items-center gap-3 px-5 py-4">
                            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-[rgb(var(--color-neutral-soft))] text-muted">
                                <x-ui.icon
                                    :name="($session['details']['device'] ?? null) === 'Mobile' ? 'device' : 'computer-desktop'"
                                    class="size-4"
                                />
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-medium text-default">{{ $session['device'] }}</p>

                                    @if ($session['is_current'])
                                        <x-ui.badge color="success" dot>{{ __('profile.current_session') }}</x-ui.badge>
                                    @endif
                                </div>

                                <p class="mt-0.5 text-xs text-muted">
                                    {{ $session['ip_address'] ?? '—' }}
                                    &middot;
                                    {{ __('profile.last_active', ['time' => $session['last_active']->diffForHumans()]) }}
                                </p>
                            </div>

                            @can(Permission::SessionRevoke->value)
                                @unless ($session['is_current'])
                                    <x-modal.confirm
                                        :title="__('profile.revoke_session_confirm_title')"
                                        :message="__('profile.revoke_session_confirm_message')"
                                        :action="route('profile.sessions.destroy', $session['id'])"
                                        method="DELETE"
                                        :confirm-label="__('profile.actions.revoke_session')"
                                    >
                                        <x-slot:trigger>
                                            <x-ui.button variant="ghost" size="sm">
                                                {{ __('profile.actions.revoke_session') }}
                                            </x-ui.button>
                                        </x-slot:trigger>
                                    </x-modal.confirm>
                                @endunless
                            @endcan
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        {{-- Recent sign-ins --}}
        <x-ui.card
            :title="__('profile.sections.recent_logins')"
            :description="__('profile.sections.recent_logins_caption')"
            :padding="false"
        >
            @can(Permission::LoginHistoryView->value)
                <x-slot:actions>
                    <x-ui.button
                        variant="ghost"
                        size="sm"
                        :href="route('profile.login-history')"
                        icon-trailing="arrow-right"
                    >
                        {{ __('profile.actions.view_full_history') }}
                    </x-ui.button>
                </x-slot:actions>
            @endcan

            @if ($recentLogins->isEmpty())
                <x-ui.empty-state
                    icon="clock"
                    :title="__('profile.history_empty_title')"
                    :message="__('profile.history_empty_message')"
                    compact
                />
            @else
                <ul class="divide-y divide-[rgb(var(--color-border))]">
                    @foreach ($recentLogins as $entry)
                        <x-dashboard.activity-item :entry="$entry" />
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>
</x-layouts.app>
