<?php

namespace Wezlo\FilamentSearchSpotlight;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Wezlo\FilamentSearchSpotlight\Actions\SpotlightAction;

class FilamentSearchSpotlightPlugin implements Plugin
{
    /** @var string|array<string>|null */
    protected string|array|null $keyBinding = null;

    protected ?bool $disableDefaultGlobalSearch = null;

    protected ?int $resultLimitPerCategory = null;

    /** @var array<class-string> */
    protected array $excludedResources = [];

    /** @var array<class-string>|null */
    protected ?array $categories = null;

    protected ?bool $includeRecords = null;

    protected ?bool $includeResources = null;

    protected ?bool $includePages = null;

    protected ?bool $includeActions = null;

    protected ?bool $disableCreateActions = null;

    protected ?string $placeholder = null;

    protected ?string $maxWidth = null;

    /** @var array<SpotlightAction> */
    protected array $actions = [];

    /** @var array<string> */
    protected array $overriddenActionNames = [];

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-search-spotlight';
    }

    /**
     * @param  string|array<string>  $binding
     */
    public function keyBinding(string|array $binding): static
    {
        $this->keyBinding = $binding;

        return $this;
    }

    public function disableDefaultGlobalSearch(bool $disable = true): static
    {
        $this->disableDefaultGlobalSearch = $disable;

        return $this;
    }

    public function resultLimitPerCategory(int $limit): static
    {
        $this->resultLimitPerCategory = $limit;

        return $this;
    }

    /**
     * @param  array<class-string>  $resources
     */
    public function excludeResources(array $resources): static
    {
        $this->excludedResources = $resources;

        return $this;
    }

    /**
     * @param  array<class-string>  $categories
     */
    public function categories(array $categories): static
    {
        $this->categories = $categories;

        return $this;
    }

    public function records(bool $enabled = true): static
    {
        $this->includeRecords = $enabled;

        return $this;
    }

    public function resources(bool $enabled = true): static
    {
        $this->includeResources = $enabled;

        return $this;
    }

    public function pages(bool $enabled = true): static
    {
        $this->includePages = $enabled;

        return $this;
    }

    public function actionsEnabled(bool $enabled = true): static
    {
        $this->includeActions = $enabled;

        return $this;
    }

    public function disableCreateActions(bool $disable = true): static
    {
        $this->disableCreateActions = $disable;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function maxWidth(string $width): static
    {
        $this->maxWidth = $width;

        return $this;
    }

    /**
     * @param  array<SpotlightAction>  $actions
     */
    public function actions(array $actions): static
    {
        foreach ($actions as $action) {
            $this->action($action);
        }

        return $this;
    }

    public function action(SpotlightAction $action): static
    {
        $this->actions[$action->getName()] = $action;

        return $this;
    }

    /**
     * @param  array<string>  $names
     */
    public function overrideActions(array $names): static
    {
        $this->overriddenActionNames = array_values(array_unique(array_merge($this->overriddenActionNames, $names)));

        return $this;
    }

    /**
     * @return string|array<string>
     */
    public function getKeyBinding(): string|array
    {
        return $this->keyBinding ?? config('filament-search-spotlight.key_binding', 'mod+k');
    }

    public function getResultLimitPerCategory(): int
    {
        return $this->resultLimitPerCategory
            ?? (int) config('filament-search-spotlight.result_limit_per_category', 8);
    }

    /**
     * @return array<class-string>
     */
    public function getExcludedResources(): array
    {
        return array_merge(
            config('filament-search-spotlight.excluded_resources', []),
            $this->excludedResources,
        );
    }

    /**
     * @return array<class-string>|null
     */
    public function getCategories(): ?array
    {
        return $this->categories;
    }

    public function includesRecords(): bool
    {
        return $this->includeRecords ?? (bool) config('filament-search-spotlight.categories.records', true);
    }

    public function includesResources(): bool
    {
        return $this->includeResources ?? (bool) config('filament-search-spotlight.categories.resources', true);
    }

    public function includesPages(): bool
    {
        return $this->includePages ?? (bool) config('filament-search-spotlight.categories.pages', true);
    }

    public function includesActions(): bool
    {
        return $this->includeActions ?? (bool) config('filament-search-spotlight.categories.actions', true);
    }

    public function createActionsDisabled(): bool
    {
        return $this->disableCreateActions ?? (bool) config('filament-search-spotlight.disable_create_actions', false);
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder
            ?? config('filament-search-spotlight.placeholder')
            ?? __('filament-search-spotlight::spotlight.placeholder');
    }

    public function getMaxWidth(): string
    {
        return $this->maxWidth ?? (string) config('filament-search-spotlight.max_width', '42rem');
    }

    /**
     * @return array<SpotlightAction>
     */
    public function getActions(): array
    {
        return array_values($this->actions);
    }

    /**
     * @return array<string>
     */
    public function getOverriddenActionNames(): array
    {
        return array_values(array_unique(array_merge(
            config('filament-search-spotlight.override_actions', []),
            $this->overriddenActionNames,
            array_keys($this->actions),
        )));
    }

    public function register(Panel $panel): void
    {
        if ($this->disableDefaultGlobalSearch ?? config('filament-search-spotlight.disable_default_global_search', false)) {
            $panel->globalSearch(false);
        }

        $panel->renderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => Blade::render("@livewire('filament-search-spotlight')"),
        );
    }

    public function boot(Panel $panel): void {}

    public static function current(): ?static
    {
        try {
            return filament()->getCurrentOrDefaultPanel()->getPlugin('filament-search-spotlight');
        } catch (\Throwable) {
            return null;
        }
    }
}
