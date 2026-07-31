@props(['card'])

{{-- Statistic card: value, label, trend, icon (18_Dashboard_Module.md §8). --}}
<div class="surface-card group relative overflow-hidden p-5 transition-shadow duration-200 hover:shadow-3">
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-muted">{{ $card->label }}</p>

        <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-[var(--radius-md)] bg-[rgb(var(--color-primary-soft))] text-[rgb(var(--color-primary))] ring-1 ring-[rgb(var(--color-primary-border))]">
            <x-ui.icon :name="$card->icon" class="size-4.5" />
        </span>
    </div>

    <p class="mt-3 text-3xl font-semibold tracking-[-0.02em] text-default tabular-nums">
        {{ $card->value }}
    </p>

    <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1">
        @if ($card->hasTrend())
            <span @class([
                'inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 text-xs font-semibold tabular-nums',
                'bg-[rgb(var(--color-success-soft))] text-[rgb(var(--color-success))]' => $card->trendIsPositive(),
                'bg-[rgb(var(--color-danger-soft))] text-[rgb(var(--color-danger))]' => ! $card->trendIsPositive(),
            ])>
                <x-ui.icon
                    :name="$card->trendIsPositive() ? 'arrow-trending-up' : 'arrow-trending-down'"
                    class="size-3.5"
                />
                {{ $card->formattedTrend() }}
            </span>
        @endif

        @if ($card->caption)
            <span class="text-xs text-subtle">{{ $card->caption }}</span>
        @endif
    </div>
</div>
