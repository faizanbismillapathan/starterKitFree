@php
    use App\Support\BreadcrumbBuilder;

    $breadcrumbs = BreadcrumbBuilder::forDashboard()
        ->add(__('profile.security_title'), route('profile.security'))
        ->add(__('profile.login_history_title'))
        ->toArray();

    $perPage = $histories->perPage();
    $pageSizes = (array) config('starter_kit.pagination.options');
@endphp

<x-layouts.app :title="__('profile.login_history_title')" :breadcrumbs="$breadcrumbs">
    <x-layout.page-header
        :title="__('profile.login_history_heading')"
        :description="__('profile.login_history_subheading')"
        :back="route('profile.security')"
    />

    <x-ui.card class="mt-6" :padding="false">
        {{-- Table toolbar: page size selector (13_Table_System.md §19). --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-default px-5 py-3">
            <p class="text-sm text-muted">
                @if ($histories->total() > 0)
                    {{ __('ui.table.showing', [
                        'first' => $histories->firstItem(),
                        'last' => $histories->lastItem(),
                        'total' => $histories->total(),
                    ]) }}
                @else
                    {{ __('ui.table.no_results') }}
                @endif
            </p>

            <form method="GET" class="flex items-center gap-2">
                <label for="per_page" class="text-xs font-medium text-muted">
                    {{ __('ui.table.per_page') }}
                </label>

                {{--
                    Submitted through Alpine rather than an inline handler so
                    the Content Security Policy can forbid unsafe-inline.
                --}}
                <select
                    name="per_page"
                    id="per_page"
                    x-on:change="$el.form.requestSubmit()"
                    class="field-input h-8 w-20 py-0 pr-8 text-xs"
                >
                    @foreach ($pageSizes as $size)
                        <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if ($histories->isEmpty())
            <x-ui.empty-state
                icon="clock"
                :title="__('profile.history_empty_title')"
                :message="__('profile.history_empty_message')"
            />
        @else
            {{-- Desktop table --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-default bg-surface-sunken">
                        <tr>
                            <th scope="col" class="px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-subtle">
                                {{ __('Status') }}
                            </th>
                            <th scope="col" class="px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-subtle">
                                {{ __('Device') }}
                            </th>
                            <th scope="col" class="px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-subtle">
                                {{ __('IP address') }}
                            </th>
                            <th scope="col" class="px-5 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-subtle">
                                {{ __('When') }}
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[rgb(var(--color-border))]">
                        @foreach ($histories as $entry)
                            <tr class="transition-colors hover:bg-surface-hover">
                                <td class="px-5 py-3">
                                    <x-ui.badge :color="$entry->status->color()" dot>
                                        {{ $entry->status->label() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-5 py-3">
                                    <p class="font-medium text-default">
                                        {{ collect([$entry->browser, $entry->platform])->filter()->join(' · ') ?: '—' }}
                                    </p>
                                    <p class="text-xs text-muted">{{ $entry->device ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-3 text-muted tabular-nums">{{ $entry->ip_address ?? '—' }}</td>
                                <td class="px-5 py-3 text-right">
                                    <time
                                        class="text-muted"
                                        datetime="{{ $entry->created_at?->toIso8601String() }}"
                                        title="{{ $entry->created_at?->toDayDateTimeString() }}"
                                    >{{ $entry->created_at?->diffForHumans() }}</time>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile card layout (13_Table_System.md §20). --}}
            <ul class="divide-y divide-[rgb(var(--color-border))] md:hidden">
                @foreach ($histories as $entry)
                    <li class="space-y-1.5 px-5 py-4">
                        <div class="flex items-center justify-between gap-2">
                            <x-ui.badge :color="$entry->status->color()" dot>
                                {{ $entry->status->label() }}
                            </x-ui.badge>
                            <time class="text-xs text-subtle" datetime="{{ $entry->created_at?->toIso8601String() }}">
                                {{ $entry->created_at?->diffForHumans(short: true) }}
                            </time>
                        </div>
                        <p class="text-sm font-medium text-default">
                            {{ collect([$entry->browser, $entry->platform])->filter()->join(' · ') ?: '—' }}
                        </p>
                        <p class="text-xs text-muted">{{ $entry->ip_address ?? '—' }}</p>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($histories->hasPages())
            <div class="border-t border-default px-5 py-3">
                {{ $histories->onEachSide(1)->links() }}
            </div>
        @endif
    </x-ui.card>
</x-layouts.app>
