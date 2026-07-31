{{--
    Primary navigation. Groups come from MenuBuilder and are already filtered by
    permission (11_Layout_System.md §7, §19).
--}}
<nav class="flex h-full flex-col" aria-label="{{ __('navigation.main_navigation') }}">
    <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-default px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 overflow-hidden">
            <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-[var(--radius-md)] bg-[rgb(var(--color-primary))] text-[rgb(var(--color-on-primary))]">
                <x-ui.icon name="bolt" class="size-4.5" />
            </span>
            <span class="min-w-0">
                <span class="block truncate text-sm font-semibold leading-tight text-default">
                    {{ config('starter_kit.name') }}
                </span>
                <span class="block text-[11px] leading-tight text-subtle">
                    {{ config('starter_kit.edition') }}
                </span>
            </span>
        </a>
    </div>

    <div class="scrollbar-slim flex-1 overflow-y-auto px-3 py-4">
        @foreach ($menuGroups ?? [] as $group)
            <div @class(['mt-6' => ! $loop->first])>
                <p class="px-2 pb-1.5 text-[11px] font-semibold uppercase tracking-wider text-subtle">
                    {{ $group['label'] }}
                </p>

                <ul class="space-y-0.5">
                    @foreach ($group['items'] as $item)
                        @php $active = $item->isActive(); @endphp
                        <li>
                            <a
                                href="{{ $item->url() }}"
                                @class([
                                    'group flex items-center gap-2.5 rounded-[var(--radius-md)] px-2.5 py-2 text-sm font-medium transition-colors',
                                    'bg-[rgb(var(--color-primary-soft))] text-[rgb(var(--color-primary))]' => $active,
                                    'text-muted hover:bg-surface-hover hover:text-default' => ! $active,
                                ])
                                @if ($active) aria-current="page" @endif
                            >
                                @if ($item->icon)
                                    <x-ui.icon :name="$item->icon" @class([
                                        'size-4.5 shrink-0',
                                        'opacity-100' => $active,
                                        'opacity-70 group-hover:opacity-100' => ! $active,
                                    ]) />
                                @endif
                                <span class="truncate">{{ $item->label }}</span>
                                @if ($item->badge)
                                    <x-ui.badge color="primary" class="ml-auto">{{ $item->badge }}</x-ui.badge>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    <div class="shrink-0 border-t border-default p-3">
        <div class="rounded-[var(--radius-md)] bg-surface-sunken px-3 py-2.5">
            <p class="text-[11px] font-medium text-muted">
                {{ config('starter_kit.name') }}
            </p>
            <p class="mt-0.5 text-[11px] text-subtle">
                v{{ config('starter_kit.version') }} &middot; {{ config('starter_kit.edition') }}
            </p>
        </div>
    </div>
</nav>
