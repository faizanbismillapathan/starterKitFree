@props(['entry', 'showUser' => false])

@php
    $status = $entry->status;
@endphp

<li class="flex items-start gap-3 px-5 py-3.5">
    <span @class([
        'mt-0.5 inline-flex size-8 shrink-0 items-center justify-center rounded-full',
        'bg-[rgb(var(--color-success-soft))] text-[rgb(var(--color-success))]' => $status->color() === 'success',
        'bg-[rgb(var(--color-danger-soft))] text-[rgb(var(--color-danger))]' => $status->color() === 'danger',
        'bg-[rgb(var(--color-warning-soft))] text-[rgb(var(--color-warning))]' => $status->color() === 'warning',
    ])>
        <x-ui.icon
            :name="$status->color() === 'success' ? 'login' : ($status->color() === 'danger' ? 'x-circle' : 'lock-closed')"
            class="size-4"
        />
    </span>

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-baseline gap-x-2">
            <p class="truncate text-sm font-medium text-default">
                {{ $showUser ? ($entry->user?->full_name ?? $entry->email) : $status->label() }}
            </p>
            @if ($showUser)
                <x-ui.badge :color="$status->color()" class="shrink-0">{{ $status->label() }}</x-ui.badge>
            @endif
        </div>

        <p class="mt-0.5 truncate text-xs text-muted">
            {{ collect([$entry->browser, $entry->platform, $entry->ip_address])->filter()->join(' · ') ?: __('auth.sessions.unknown_device') }}
        </p>
    </div>

    <time
        class="shrink-0 whitespace-nowrap text-xs text-subtle"
        datetime="{{ $entry->created_at?->toIso8601String() }}"
        title="{{ $entry->created_at?->toDayDateTimeString() }}"
    >
        {{ $entry->created_at?->diffForHumans(short: true) }}
    </time>
</li>
