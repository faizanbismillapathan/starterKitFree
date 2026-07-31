@php
    use App\Enums\Permission;

    $user = auth()->user();
    $canSeeSystem = $user->can(Permission::DashboardViewSystemStatistics->value);
@endphp

<x-layouts.app :title="__('dashboard.title')">
    <x-layout.page-header
        :title="__('dashboard.welcome', ['name' => $user->first_name])"
        :description="__('dashboard.subtitle')"
    >
        <x-slot:actions>
            @if (count($quickActions) > 0)
                <x-ui.dropdown align="right" width="w-64">
                    <x-slot:trigger>
                        <x-ui.button
                            variant="secondary"
                            icon="bolt"
                            icon-trailing="chevron-down"
                            aria-haspopup="menu"
                        >
                            {{ __('navigation.quick_actions.title') }}
                        </x-ui.button>
                    </x-slot:trigger>

                    @foreach ($quickActions as $action)
                        <x-ui.dropdown-item :href="$action->url()" :icon="$action->icon">
                            {{ $action->label }}
                        </x-ui.dropdown-item>
                    @endforeach
                </x-ui.dropdown>
            @endif
        </x-slot:actions>
    </x-layout.page-header>

    {{-- Statistics row --}}
    @if (count($statistics) > 0)
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($statistics as $card)
                <x-dashboard.stat-card :card="$card" />
            @endforeach
        </div>
    @endif

    {{-- Charts --}}
    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-dashboard.chart-card
                :title="__('dashboard.charts.registrations')"
                :caption="__('dashboard.charts.registrations_caption')"
                :config="[
                    'type' => 'area',
                    'height' => 280,
                    'categories' => $registrationTrend['categories'],
                    'series' => [[
                        'name' => __('dashboard.charts.registrations'),
                        'data' => $registrationTrend['series'],
                    ]],
                ]"
                :height="280"
            />
        </div>

        <x-dashboard.chart-card
            :title="__('dashboard.charts.authentication')"
            :caption="__('dashboard.charts.authentication_caption')"
            :config="[
                'type' => 'donut',
                'height' => 280,
                'labels' => [__('dashboard.charts.successful'), __('dashboard.charts.failed')],
                'colors' => ['--color-success', '--color-danger'],
                'totalLabel' => __('dashboard.charts.authentication'),
                'series' => [
                    $authenticationSummary['successful'],
                    $authenticationSummary['failed'],
                ],
            ]"
            :height="280"
        />
    </div>

    {{-- Activity + members --}}
    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-ui.card
                :title="__('dashboard.recent_activity.title')"
                :description="__('dashboard.recent_activity.caption')"
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

                @if ($recentActivity->isEmpty())
                    <x-ui.empty-state
                        icon="clock"
                        :title="__('dashboard.recent_activity.empty_title')"
                        :message="__('dashboard.recent_activity.empty_message')"
                        compact
                    />
                @else
                    <ul class="divide-y divide-[rgb(var(--color-border))]">
                        @foreach ($recentActivity as $entry)
                            <x-dashboard.activity-item :entry="$entry" :show-user="$canSeeSystem" />
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>

        <div class="space-y-4">
            @if ($canSeeSystem)
                <x-ui.card
                    :title="__('dashboard.recent_users.title')"
                    :description="__('dashboard.recent_users.caption')"
                    :padding="false"
                >
                    @if ($recentUsers->isEmpty())
                        <x-ui.empty-state
                            icon="users"
                            :title="__('dashboard.recent_users.empty_title')"
                            :message="__('dashboard.recent_users.empty_message')"
                            compact
                        />
                    @else
                        <ul class="divide-y divide-[rgb(var(--color-border))]">
                            @foreach ($recentUsers as $member)
                                <li class="flex items-center gap-3 px-5 py-3">
                                    <x-ui.avatar :user="$member" size="sm" />

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-default">
                                            {{ $member->full_name }}
                                        </p>
                                        <p class="truncate text-xs text-muted">{{ $member->email }}</p>
                                    </div>

                                    <x-ui.badge :color="$member->status->color()" dot class="shrink-0">
                                        {{ $member->status->label() }}
                                    </x-ui.badge>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card>
            @endif

            @if (count($systemStatus) > 0)
                <x-ui.card
                    :title="__('dashboard.system.title')"
                    :description="__('dashboard.system.caption')"
                >
                    <dl class="space-y-3">
                        @foreach ($systemStatus as $label => $value)
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <dt class="text-muted">{{ $label }}</dt>
                                <dd class="font-medium text-default">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-ui.card>
            @endif
        </div>
    </div>
</x-layouts.app>
