@php
    $plugin = \Wezlo\FilamentSearchSpotlight\FilamentSearchSpotlightPlugin::current();
    $binding = $plugin?->getKeyBinding() ?? config('filament-search-spotlight.key_binding', 'mod+k');
    $bindings = is_array($binding) ? $binding : [$binding];
    $placeholder = $plugin?->getPlaceholder() ?? __('filament-search-spotlight::spotlight.placeholder');
    $maxWidth = $plugin?->getMaxWidth() ?? config('filament-search-spotlight.max_width', '42rem');
@endphp

<div
    x-data="{
        isOpen: @entangle('isOpen'),
        recent: JSON.parse(localStorage.getItem('spotlight.recent') ?? '[]'),
        pinned: JSON.parse(localStorage.getItem('spotlight.pinned') ?? '[]'),
        open() {
            this.isOpen = true;
            this.$wire.set('recent', this.recent);
            this.$wire.set('pinned', this.pinned);
            this.$nextTick(() => {
                const input = this.$refs.input;
                if (input) input.focus();
            });
        },
        close() {
            this.isOpen = false;
        },
        activate() {
            const promise = this.$wire.selectResult(null);
            Promise.resolve(promise).then((result) => {
                if (! result || ! result.url) return;

                const entry = {
                    id: result.id,
                    category: result.category,
                    title: result.title,
                    subtitle: result.subtitle,
                    icon: result.icon,
                    url: result.url,
                    details: result.details ?? {},
                    payload: result.payload ?? {},
                };

                this.recent = [entry, ...this.recent.filter((r) => r.id !== entry.id)].slice(0, 10);
                localStorage.setItem('spotlight.recent', JSON.stringify(this.recent));

                window.location = result.url;
            });
        },
        togglePin(item) {
            const exists = this.pinned.some((r) => r.id === item.id);
            this.pinned = exists
                ? this.pinned.filter((r) => r.id !== item.id)
                : [item, ...this.pinned].slice(0, 20);
            localStorage.setItem('spotlight.pinned', JSON.stringify(this.pinned));
            this.$wire.set('pinned', this.pinned);
        },
    }"
    @foreach ($bindings as $b)
        x-mousetrap.global.{{ str_replace('+', '-', $b) }}.prevent="open()"
    @endforeach
    x-on:keydown.escape.window="isOpen && close()"
    x-on:keydown.arrow-down.prevent="isOpen && $wire.moveDown()"
    x-on:keydown.arrow-up.prevent="isOpen && $wire.moveUp()"
    x-on:keydown.enter.prevent="isOpen && activate()"
    x-cloak
    x-show="isOpen"
    class="fixed inset-0 z-50 flex items-start justify-center bg-gray-900/70 p-4 pt-24 backdrop-blur-sm"
    wire:ignore.self
>
    <div
        x-on:click.outside="close()"
        style="max-width: {{ $maxWidth }}"
        class="flex w-full flex-col overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black/10 dark:bg-gray-900 dark:ring-white/10"
    >
        <div class="flex items-center gap-2 border-b border-gray-200 px-4 py-3 dark:border-white/10">
            <x-filament::icon
                icon="heroicon-o-magnifying-glass"
                class="h-5 w-5 text-gray-400"
            />
            <input
                x-ref="input"
                wire:model.live.debounce.150ms="query"
                type="text"
                placeholder="{{ $placeholder }}"
                class="flex-1 border-0 bg-transparent text-base text-gray-900 placeholder-gray-400 outline-none focus:ring-0 dark:text-white"
            />
            <kbd class="rounded border border-gray-300 bg-gray-100 px-1.5 text-xs text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">{{ __('filament-search-spotlight::spotlight.keys.escape') }}</kbd>
        </div>

        <div class="max-h-[60vh] flex-1 overflow-y-auto py-2">
            @php $flatIndex = 0; @endphp

            @forelse ($groups as $group)
                <div class="px-3 pb-1 pt-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                    {{ $group['label'] }}
                </div>

                @foreach ($group['results'] as $result)
                    @include('filament-search-spotlight::partials.result', [
                        'result' => $result,
                        'flatIndex' => $flatIndex,
                        'selectedIndex' => $selectedIndex,
                    ])
                    @php $flatIndex++; @endphp
                @endforeach
            @empty
                @include('filament-search-spotlight::partials.empty-state', ['query' => $query])
            @endforelse
        </div>
    </div>
</div>
