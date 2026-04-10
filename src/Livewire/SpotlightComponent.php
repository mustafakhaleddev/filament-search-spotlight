<?php

namespace Wezlo\FilamentSearchSpotlight\Livewire;

use Livewire\Component;
use Wezlo\FilamentSearchSpotlight\Categories\ActionsCategory;
use Wezlo\FilamentSearchSpotlight\Categories\Category;
use Wezlo\FilamentSearchSpotlight\Categories\PagesCategory;
use Wezlo\FilamentSearchSpotlight\Categories\RecordsCategory;
use Wezlo\FilamentSearchSpotlight\Categories\ResourcesCategory;
use Wezlo\FilamentSearchSpotlight\Data\SpotlightResult;
use Wezlo\FilamentSearchSpotlight\Data\SpotlightResultGroup;
use Wezlo\FilamentSearchSpotlight\FilamentSearchSpotlightPlugin;

class SpotlightComponent extends Component
{
    public string $query = '';

    public int $selectedIndex = 0;

    /** @var array<int, array<string, mixed>> */
    public array $groups = [];

    /** @var array<int, array<string, mixed>> */
    public array $recent = [];

    /** @var array<int, array<string, mixed>> */
    public array $pinned = [];

    public bool $isOpen = false;

    public function mount(): void
    {
        $this->search();
    }

    public function updatedQuery(): void
    {
        $this->selectedIndex = 0;
        $this->search();
    }

    public function updatedRecent(): void
    {
        if (trim($this->query) === '') {
            $this->search();
        }
    }

    public function updatedPinned(): void
    {
        if (trim($this->query) === '') {
            $this->search();
        }
    }

    public function search(): void
    {
        $limit = $this->getResultLimit();
        $groups = [];

        if (trim($this->query) === '') {
            if (! empty($this->pinned)) {
                $groups[] = $this->rehydrateGroup('pinned', __('filament-search-spotlight::spotlight.groups.pinned'), $this->pinned);
            }

            if (! empty($this->recent)) {
                $groups[] = $this->rehydrateGroup('recent', __('filament-search-spotlight::spotlight.groups.recent'), $this->recent);
            }

            if (FilamentSearchSpotlightPlugin::current()?->includesActions() ?? true) {
                $actions = (new ActionsCategory)->search('', $limit);

                if ($actions !== []) {
                    $groups[] = (new SpotlightResultGroup('actions', __('filament-search-spotlight::spotlight.groups.actions'), $actions))->toArray();
                }
            }

            $this->groups = $groups;

            return;
        }

        foreach ($this->resolveCategories() as $category) {
            $results = $category->search($this->query, $limit);

            if ($results === []) {
                continue;
            }

            $groups[] = (new SpotlightResultGroup(
                $category->key(),
                $category->label(),
                $results,
            ))->toArray();
        }

        $this->groups = $groups;
    }

    public function moveDown(): void
    {
        $count = $this->countFlatResults();

        if ($count === 0) {
            $this->selectedIndex = 0;

            return;
        }

        $this->selectedIndex = ($this->selectedIndex + 1) % $count;
    }

    public function moveUp(): void
    {
        $count = $this->countFlatResults();

        if ($count === 0) {
            $this->selectedIndex = 0;

            return;
        }

        $this->selectedIndex = ($this->selectedIndex - 1 + $count) % $count;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function selectResult(?int $flatIndex = null): ?array
    {
        $index = $flatIndex ?? $this->selectedIndex;
        $flat = $this->flatResults();

        return $flat[$index] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPreviewedResultProperty(): ?array
    {
        $flat = $this->flatResults();

        return $flat[$this->selectedIndex] ?? null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function flatResults(): array
    {
        $flat = [];

        foreach ($this->groups as $group) {
            foreach ($group['results'] ?? [] as $result) {
                $flat[] = $result;
            }
        }

        return $flat;
    }

    protected function countFlatResults(): int
    {
        return count($this->flatResults());
    }

    /**
     * @return array<Category>
     */
    protected function resolveCategories(): array
    {
        $plugin = FilamentSearchSpotlightPlugin::current();
        $configured = $plugin?->getCategories();

        if ($configured !== null && $configured !== []) {
            return array_map(fn (string $class) => app($class), $configured);
        }

        $categories = [];

        if ($plugin?->includesRecords() ?? true) {
            $categories[] = new RecordsCategory;
        }

        if ($plugin?->includesResources() ?? true) {
            $categories[] = new ResourcesCategory;
        }

        if ($plugin?->includesPages() ?? true) {
            $categories[] = new PagesCategory;
        }

        if ($plugin?->includesActions() ?? true) {
            $categories[] = new ActionsCategory;
        }

        return $categories;
    }

    protected function getResultLimit(): int
    {
        return FilamentSearchSpotlightPlugin::current()?->getResultLimitPerCategory()
            ?? (int) config('filament-search-spotlight.result_limit_per_category', 8);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    protected function rehydrateGroup(string $key, string $label, array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $payload = (array) ($item['payload'] ?? []);
            $icon = $item['icon'] ?? null;

            if ($icon === null && isset($payload['resource_class']) && class_exists($payload['resource_class'])) {
                $resourceClass = $payload['resource_class'];

                if (method_exists($resourceClass, 'getNavigationIcon')) {
                    $icon = ResourcesCategory::normalizeIcon($resourceClass::getNavigationIcon());
                }
            }

            $results[] = (new SpotlightResult(
                id: (string) ($item['id'] ?? uniqid('spotlight-', true)),
                category: (string) ($item['category'] ?? $key),
                title: (string) ($item['title'] ?? ''),
                subtitle: $item['subtitle'] ?? null,
                icon: $icon,
                url: (string) ($item['url'] ?? ''),
                details: (array) ($item['details'] ?? []),
                payload: $payload,
            ))->toArray();
        }

        return [
            'key' => $key,
            'label' => $label,
            'results' => $results,
        ];
    }

    public function render()
    {
        return view('filament-search-spotlight::livewire.spotlight');
    }
}
