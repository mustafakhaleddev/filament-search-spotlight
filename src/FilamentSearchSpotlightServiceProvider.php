<?php

namespace Wezlo\FilamentSearchSpotlight;

use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wezlo\FilamentSearchSpotlight\Actions\SpotlightActionRegistry;
use Wezlo\FilamentSearchSpotlight\Livewire\SpotlightComponent;

class FilamentSearchSpotlightServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-search-spotlight';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasViews('filament-search-spotlight')
            ->hasTranslations();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(SpotlightActionRegistry::class);
    }

    public function packageBooted(): void
    {
        Livewire::component(
            'filament-search-spotlight',
            SpotlightComponent::class,
        );
    }
}
