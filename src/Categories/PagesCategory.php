<?php

namespace Wezlo\FilamentSearchSpotlight\Categories;

use Filament\Facades\Filament;
use Wezlo\FilamentSearchSpotlight\Data\SpotlightResult;

class PagesCategory implements Category
{
    public function key(): string
    {
        return 'pages';
    }

    public function label(): string
    {
        return __('filament-search-spotlight::spotlight.groups.pages');
    }

    public function search(string $query, int $limit): array
    {
        $panel = Filament::getCurrentPanel();

        if ($panel === null) {
            return [];
        }

        $needle = mb_strtolower(trim($query));
        $results = [];

        foreach ($panel->getPages() as $pageClass) {
            if (! class_exists($pageClass)) {
                continue;
            }

            $label = method_exists($pageClass, 'getNavigationLabel')
                ? (string) $pageClass::getNavigationLabel()
                : class_basename($pageClass);

            if ($needle !== '' && ! str_contains(mb_strtolower($label), $needle)) {
                continue;
            }

            $url = method_exists($pageClass, 'getUrl') ? (string) $pageClass::getUrl() : '';

            if ($url === '') {
                continue;
            }

            $group = method_exists($pageClass, 'getNavigationGroup')
                ? $pageClass::getNavigationGroup()
                : null;

            $icon = method_exists($pageClass, 'getNavigationIcon')
                ? $pageClass::getNavigationIcon()
                : null;

            $results[] = new SpotlightResult(
                id: 'pages:'.md5($pageClass),
                category: 'pages',
                title: $label,
                subtitle: is_string($group) ? $group : null,
                icon: ResourcesCategory::normalizeIcon($icon),
                url: $url,
                details: [],
                payload: ['page' => $pageClass],
            );

            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }
}
