<?php

namespace Wezlo\FilamentSearchSpotlight\Categories;

use Filament\Facades\Filament;
use Wezlo\FilamentSearchSpotlight\Data\SpotlightResult;
use Wezlo\FilamentSearchSpotlight\FilamentSearchSpotlightPlugin;

class RecordsCategory implements Category
{
    public function key(): string
    {
        return 'records';
    }

    public function label(): string
    {
        return __('filament-search-spotlight::spotlight.groups.records');
    }

    public function search(string $query, int $limit): array
    {
        if (trim($query) === '') {
            return [];
        }

        $panel = Filament::getCurrentPanel();

        if ($panel === null) {
            return [];
        }

        $excluded = FilamentSearchSpotlightPlugin::current()?->getExcludedResources() ?? [];
        $flat = [];

        $resources = $panel->getResources();

        usort(
            $resources,
            fn (string $a, string $b): int => ($a::getGlobalSearchSort() ?? 0) <=> ($b::getGlobalSearchSort() ?? 0),
        );

        foreach ($resources as $resourceClass) {
            if (in_array($resourceClass, $excluded, true)) {
                continue;
            }

            if (! class_exists($resourceClass) || ! $resourceClass::canGloballySearch()) {
                continue;
            }

            $resourceLabel = (string) $resourceClass::getPluralModelLabel();

            $icon = method_exists($resourceClass, 'getNavigationIcon')
                ? ResourcesCategory::normalizeIcon($resourceClass::getNavigationIcon())
                : null;

            $resourceResults = $resourceClass::getGlobalSearchResults($query);

            foreach ($resourceResults as $result) {
                $spotlight = SpotlightResult::fromGlobalSearchResult($result, $resourceLabel);

                $flat[] = new SpotlightResult(
                    id: $spotlight->id,
                    category: $spotlight->category,
                    title: $spotlight->title,
                    subtitle: $spotlight->subtitle,
                    icon: $icon,
                    url: $spotlight->url,
                    details: $spotlight->details,
                    payload: array_merge($spotlight->payload, ['resource_class' => $resourceClass]),
                );

                if (count($flat) >= $limit) {
                    break 2;
                }
            }
        }

        return $flat;
    }
}
