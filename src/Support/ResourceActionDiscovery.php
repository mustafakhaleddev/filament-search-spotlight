<?php

namespace Wezlo\FilamentSearchSpotlight\Support;

use Filament\Panel;
use Wezlo\FilamentSearchSpotlight\Actions\SpotlightAction;

class ResourceActionDiscovery
{
    /**
     * @param  array<class-string>  $excluded
     * @return array<SpotlightAction>
     */
    public static function discover(Panel $panel, array $excluded = []): array
    {
        $actions = [];

        foreach ($panel->getResources() as $resourceClass) {
            if (in_array($resourceClass, $excluded, true)) {
                continue;
            }

            if (! class_exists($resourceClass)) {
                continue;
            }

            $pages = $resourceClass::getPages();

            if (! isset($pages['create'])) {
                continue;
            }

            $label = method_exists($resourceClass, 'getModelLabel')
                ? (string) $resourceClass::getModelLabel()
                : class_basename($resourceClass);

            $actions[] = SpotlightAction::make('create-'.md5($resourceClass))
                ->label(__('filament-search-spotlight::spotlight.actions.create.label', ['label' => ucfirst($label)]))
                ->icon('heroicon-o-plus')
                ->keywords(['create', 'new', 'add', $label])
                ->group(__('filament-search-spotlight::spotlight.actions.create.group'))
                ->url(fn () => $resourceClass::getUrl('create'));
        }

        return $actions;
    }
}
