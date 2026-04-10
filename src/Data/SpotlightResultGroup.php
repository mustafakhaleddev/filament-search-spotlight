<?php

namespace Wezlo\FilamentSearchSpotlight\Data;

class SpotlightResultGroup
{
    /**
     * @param  array<SpotlightResult>  $results
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly array $results,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'results' => array_map(fn (SpotlightResult $r) => $r->toArray(), $this->results),
        ];
    }
}
