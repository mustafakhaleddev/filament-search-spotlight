<?php

namespace Wezlo\FilamentSearchSpotlight\Categories;

use Wezlo\FilamentSearchSpotlight\Data\SpotlightResult;

interface Category
{
    public function key(): string;

    public function label(): string;

    /**
     * @return array<SpotlightResult>
     */
    public function search(string $query, int $limit): array;
}
