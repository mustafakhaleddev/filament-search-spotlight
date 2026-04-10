@php
    $isSelected = $flatIndex === $selectedIndex;
@endphp

<button
    type="button"
    x-on:click.prevent="$wire.set('selectedIndex', {{ $flatIndex }}).then(() => activate())"
    x-on:mouseenter="$wire.set('selectedIndex', {{ $flatIndex }})"
    @class([
        'flex w-full items-center gap-3 px-4 py-2 text-left text-sm',
        'bg-primary-500/10 text-primary-900 dark:text-primary-100' => $isSelected,
        'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5' => ! $isSelected,
    ])
>
    @if ($result['icon'] ?? null)
        <x-filament::icon :icon="$result['icon']" class="h-5 w-5 flex-shrink-0 text-gray-400" />
    @else
        <span class="h-5 w-5 flex-shrink-0 rounded bg-gray-200 dark:bg-white/10"></span>
    @endif

    <div class="min-w-0 flex-1">
        <div class="truncate font-medium">{{ $result['title'] }}</div>

        @if ($result['subtitle'] ?? null)
            <div class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $result['subtitle'] }}</div>
        @endif
    </div>
</button>
