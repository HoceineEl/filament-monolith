<?php

declare(strict_types=1);

use Filament\FontProviders\LocalFontProvider;
use HoceineEl\Monolith\Enums\Font;
use HoceineEl\Monolith\MonolithTheme;
use Illuminate\Support\Facades\Route;

it('resolves the registered plugin', function (): void {
    expect(MonolithTheme::get())->toBe(theme())
        ->and(theme()->getId())->toBe('monolith');
});

it('configures the panel', function (): void {
    $panel = filament()->getPanel('admin');

    expect($panel->getSidebarWidth())->toBe('16rem')
        ->and($panel->getFontFamily())->toBe('Geist')
        ->and($panel->getGlobalSearchKeyBindings())->toBe(['mod+k'])
        ->and(Route::has($panel->generateRouteName('monolith.appearance')))->toBeTrue();
});

it('follows the theme font lazily', function (): void {
    $panel = filament()->getPanel('admin');

    theme()->font('Lato');

    expect($panel->getFontFamily())->toBe('Lato');

    theme()->font(Font::System);

    expect($panel->getFontFamily())->toBe('system-ui')
        ->and($panel->getFontProvider())->toBe(LocalFontProvider::class);
});
