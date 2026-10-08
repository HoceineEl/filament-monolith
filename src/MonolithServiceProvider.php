<?php

declare(strict_types=1);

namespace HoceineEl\Monolith;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MonolithServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('monolith')
            ->hasTranslations()
            ->hasViews('monolith');
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('monolith', __DIR__.'/../resources/dist/monolith.css')->loadedOnRequest(),
        ], MonolithTheme::PACKAGE);
    }
}
