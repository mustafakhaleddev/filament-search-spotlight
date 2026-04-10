<?php

namespace Wezlo\FilamentSearchSpotlight\Actions;

class SpotlightActionRegistry
{
    /** @var array<string, SpotlightAction> */
    protected array $actions = [];

    public function register(SpotlightAction $action): void
    {
        $this->actions[$action->getName()] = $action;
    }

    public function forget(string $name): void
    {
        unset($this->actions[$name]);
    }

    public function flush(): void
    {
        $this->actions = [];
    }

    /**
     * @return array<string, SpotlightAction>
     */
    public function all(): array
    {
        return $this->actions;
    }
}
