<div class="flex flex-col items-center gap-2 px-4 py-10 text-center text-sm text-gray-400">
    @if (trim($query) === '')
        <span>{{ __('filament-search-spotlight::spotlight.empty_state.prompt') }}</span>
    @else
        <span>{{ __('filament-search-spotlight::spotlight.empty_state.no_results', ['query' => $query]) }}</span>
    @endif
</div>
