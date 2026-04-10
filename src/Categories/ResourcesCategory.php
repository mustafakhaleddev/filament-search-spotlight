<?php

namespace Wezlo\FilamentSearchSpotlight\Categories;

use BackedEnum;
use Filament\Facades\Filament;
use Filament\Support\Contracts\ScalableIcon;
use Filament\Support\Enums\IconSize;
use Wezlo\FilamentSearchSpotlight\Data\SpotlightResult;
use Wezlo\FilamentSearchSpotlight\FilamentSearchSpotlightPlugin;

class ResourcesCategory implements Category
{
    public function key(): string
    {
        return 'resources';
    }

    public function label(): string
    {
        return __('filament-search-spotlight::spotlight.groups.resources');
    }

    public function search(string $query, int $limit): array
    {
        $panel = Filament::getCurrentPanel();

        if ($panel === null) {
            return [];
        }

        $excluded = FilamentSearchSpotlightPlugin::current()?->getExcludedResources() ?? [];
        $needle = mb_strtolower(trim($query));
        $results = [];

        foreach ($panel->getResources() as $resourceClass) {
            if (in_array($resourceClass, $excluded, true)) {
                continue;
            }

            if (! class_exists($resourceClass)) {
                continue;
            }

            $label = (string) $resourceClass::getPluralModelLabel();
            $singular = (string) $resourceClass::getModelLabel();

            $haystack = mb_strtolower($label.' '.$singular);

            if ($needle !== '' && ! str_contains($haystack, $needle)) {
                continue;
            }

            if (! isset($resourceClass::getPages()['index'])) {
                continue;
            }

            $url = $resourceClass::getUrl('index');

            $group = method_exists($resourceClass, 'getNavigationGroup')
                ? $resourceClass::getNavigationGroup()
                : null;

            $icon = method_exists($resourceClass, 'getNavigationIcon')
                ? $resourceClass::getNavigationIcon()
                : null;

            $results[] = new SpotlightResult(
                id: 'resources:'.md5($resourceClass),
                category: 'resources',
                title: ucfirst($label),
                subtitle: is_string($group) ? $group : null,
                icon: self::normalizeIcon($icon),
                url: $url,
                details: [],
                payload: ['resource' => $resourceClass],
            );

            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }

    public static function normalizeIcon(mixed $icon): ?string
    {
        if ($icon === null) {
            return null;
        }

        if (is_string($icon)) {
            return $icon;
        }

        if ($icon instanceof ScalableIcon) {
            return $icon->getIconForSize(IconSize::Medium);
        }

        if ($icon instanceof BackedEnum) {
            return is_string($icon->value) ? $icon->value : null;
        }

        return null;
    }
}
