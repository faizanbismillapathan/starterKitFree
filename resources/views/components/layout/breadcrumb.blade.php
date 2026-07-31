@props(['items' => []])

{{-- Every page except the dashboard shows a breadcrumb (11_Layout_System.md §12). --}}
@if (count($items) > 0)
    <nav aria-label="{{ __('navigation.breadcrumb') }}" {{ $attributes }}>
        <ol class="flex flex-wrap items-center gap-1 text-sm">
            @foreach ($items as $index => $item)
                <li class="flex items-center gap-1">
                    @if ($index > 0)
                        <x-ui.icon name="chevron-right" class="size-3.5 text-subtle" />
                    @endif

                    @if (! empty($item['url']) && ! $loop->last)
                        <a href="{{ $item['url'] }}" class="text-muted transition hover:text-default">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="font-medium text-default" @if ($loop->last) aria-current="page" @endif>
                            {{ $item['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
