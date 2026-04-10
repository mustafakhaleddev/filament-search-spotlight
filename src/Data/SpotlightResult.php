<?php

namespace Wezlo\FilamentSearchSpotlight\Data;

use Filament\GlobalSearch\GlobalSearchResult;
use Illuminate\Contracts\Support\Htmlable;

class SpotlightResult
{
    /**
     * @param  array<string, string>  $details
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly string $id,
        public readonly string $category,
        public readonly string|Htmlable $title,
        public readonly ?string $subtitle,
        public readonly ?string $icon,
        public readonly string $url,
        public readonly array $details = [],
        public readonly array $payload = [],
    ) {}

    public static function fromGlobalSearchResult(
        GlobalSearchResult $result,
        string $resourceLabel,
        ?string $modelClass = null,
        mixed $recordKey = null,
    ): self {
        $id = 'records:'.md5($resourceLabel.'|'.(string) $result->title.'|'.$result->url);

        return new self(
            id: $id,
            category: 'records',
            title: $result->title,
            subtitle: $resourceLabel,
            icon: null,
            url: $result->url,
            details: $result->details,
            payload: array_filter([
                'resource_label' => $resourceLabel,
                'model' => $modelClass,
                'key' => $recordKey,
            ], fn ($v) => $v !== null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'title' => $this->title instanceof Htmlable ? $this->title->toHtml() : $this->title,
            'subtitle' => $this->subtitle,
            'icon' => $this->icon,
            'url' => $this->url,
            'details' => $this->details,
            'payload' => $this->payload,
        ];
    }
}
