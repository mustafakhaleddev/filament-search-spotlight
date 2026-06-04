<?php

namespace Wezlo\FilamentSearchSpotlight\Actions;

use BackedEnum;
use Closure;

class SpotlightAction
{
    protected ?string $label = null;

    protected ?string $icon = null;

    /** @var array<string> */
    protected array $keywords = [];

    protected string|Closure $url = '';

    protected ?string $group = null;

    protected ?string $shortcut = null;

    final public function __construct(public readonly string $name) {}

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function icon(BackedEnum|string $icon): static
    {
        if($icon instanceof BackedEnum) {
            $this->icon = $icon->value;
        } else {
            $this->icon = $icon;
        }

        return $this;
    }

    /**
     * @param  array<string>  $keywords
     */
    public function keywords(array $keywords): static
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function url(string|Closure $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function group(string $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function shortcut(string $shortcut): static
    {
        $this->shortcut = $shortcut;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label ?? $this->name;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @return array<string>
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    public function getUrl(): string
    {
        return $this->url instanceof Closure ? (string) ($this->url)() : (string) $this->url;
    }

    public function matches(string $query): bool
    {
        $query = trim(mb_strtolower($query));

        if ($query === '') {
            return true;
        }

        $haystack = mb_strtolower(implode(' ', [
            $this->getLabel(),
            $this->name,
            $this->group ?? '',
            implode(' ', $this->keywords),
        ]));

        foreach (preg_split('/\s+/', $query) ?: [] as $term) {
            if ($term === '') {
                continue;
            }

            if (! str_contains($haystack, $term)) {
                return false;
            }
        }

        return true;
    }

    public function register(): static
    {
        app(SpotlightActionRegistry::class)->register($this);

        return $this;
    }
}
