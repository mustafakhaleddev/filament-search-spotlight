<?php

namespace Wezlo\FilamentSearchSpotlight\Categories;

use Filament\Facades\Filament;
use Wezlo\FilamentSearchSpotlight\Actions\SpotlightAction;
use Wezlo\FilamentSearchSpotlight\Actions\SpotlightActionRegistry;
use Wezlo\FilamentSearchSpotlight\Data\SpotlightResult;
use Wezlo\FilamentSearchSpotlight\FilamentSearchSpotlightPlugin;
use Wezlo\FilamentSearchSpotlight\Support\ResourceActionDiscovery;

class ActionsCategory implements Category
{
    public function key(): string
    {
        return 'actions';
    }

    public function label(): string
    {
        return __('filament-search-spotlight::spotlight.groups.actions');
    }

    public function search(string $query, int $limit): array
    {
        $plugin = FilamentSearchSpotlightPlugin::current();
        $overridden = $plugin?->getOverriddenActionNames() ?? [];

        $actions = [];

        foreach (app(SpotlightActionRegistry::class)->all() as $name => $action) {
            if (in_array($name, $overridden, true)) {
                continue;
            }

            $actions[$name] = $action;
        }

        if ($plugin !== null) {
            foreach ($plugin->getActions() as $action) {
                $actions[$action->getName()] = $action;
            }
        }

        $panel = Filament::getCurrentPanel();

        if ($panel !== null && ! ($plugin?->createActionsDisabled() ?? false)) {
            $excluded = $plugin?->getExcludedResources() ?? [];

            foreach (ResourceActionDiscovery::discover($panel, $excluded) as $action) {
                if (isset($actions[$action->getName()])) {
                    continue;
                }

                $actions[$action->getName()] = $action;
            }
        }

        $matched = array_values(array_filter(
            $actions,
            fn (SpotlightAction $a) => $a->matches($query),
        ));

        $matched = array_slice($matched, 0, $limit);

        return array_map(function (SpotlightAction $action): SpotlightResult {
            return new SpotlightResult(
                id: 'actions:'.$action->getName(),
                category: 'actions',
                title: $action->getLabel(),
                subtitle: $action->getGroup(),
                icon: $action->getIcon(),
                url: $action->getUrl(),
                details: [],
                payload: [
                    'action' => $action->getName(),
                    'shortcut' => $action->getShortcut(),
                ],
            );
        }, $matched);
    }
}
