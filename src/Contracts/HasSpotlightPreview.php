<?php

namespace Wezlo\FilamentSearchSpotlight\Contracts;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

interface HasSpotlightPreview
{
    public static function getSpotlightPreview(Model $record): Htmlable;
}
