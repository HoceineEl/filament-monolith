<?php

declare(strict_types=1);

use HoceineEl\Monolith\Enums\Font;
use HoceineEl\Monolith\MonolithTheme;

it('resolves the font family', function (Font|string $font, string $family): void {
    expect(MonolithTheme::make()->font($font)->getFontFamily())->toBe($family);
})->with([
    'enum' => [Font::Inter, 'Inter'],
    'string' => ['Tajawal', 'Tajawal'],
    'system' => [Font::System, 'system'],
]);

it('evaluates a font closure', function (): void {
    expect(MonolithTheme::make()->font(fn (): Font => Font::Cairo)->getFontFamily())->toBe('Cairo');
});

it('lists the default fonts', function (): void {
    expect(MonolithTheme::make()->getFonts())->toBe(['Geist', 'Inter', 'Figtree', 'IBM Plex Sans', 'system']);
});

it('adds Arabic fonts for Arabic script locales', function (): void {
    app()->setLocale('ar');

    expect(MonolithTheme::make()->getFonts())->toBe([
        'Geist', 'Inter', 'Figtree', 'IBM Plex Sans', 'IBM Plex Sans Arabic', 'Tajawal', 'Cairo', 'system',
    ]);
});

it('de-duplicates the font list', function (): void {
    expect(MonolithTheme::make()->fonts([Font::Inter, 'Inter', 'Lato', Font::System, 'system'])->getFonts())
        ->toBe(['Inter', 'Lato', 'system']);
});

it('only uses fallback fonts for Arabic script locales', function (): void {
    expect(MonolithTheme::make()->getFallbackFonts())->toBe([]);

    app()->setLocale('ar');

    expect(MonolithTheme::make()->getFallbackFonts())->toBe(['IBM Plex Sans Arabic']);

    app()->setLocale('fa_IR');

    expect(MonolithTheme::make()->getFallbackFonts())->toBe(['IBM Plex Sans Arabic']);
});

it('overrides the fallback fonts', function (): void {
    app()->setLocale('ar');

    expect(MonolithTheme::make()->fallbackFonts(['Noto Kufi Arabic'])->getFallbackFonts())->toBe(['Noto Kufi Arabic'])
        ->and(MonolithTheme::make()->fallbackFonts([])->getFallbackFonts())->toBe([])
        ->and(MonolithTheme::make()->fallbackFonts(fn (): array => ['Cairo'])->getFallbackFonts())->toBe(['Cairo']);
});

it('builds Bunny font urls by default', function (): void {
    expect(MonolithTheme::make()->getFontUrl('IBM Plex Sans Arabic'))
        ->toBe('https://fonts.bunny.net/css?family=ibm-plex-sans-arabic:400,500,600,700&display=swap');
});

it('builds self-hosted font urls from a template', function (): void {
    $theme = MonolithTheme::make()->fontUrl('/fonts/{slug}.css?family={family}&w={weights}');

    expect($theme->getFontUrl('Plus Jakarta Sans', '500'))->toBe('/fonts/plus-jakarta-sans.css?family=Plus%20Jakarta%20Sans&w=500')
        ->and($theme->getFontUrlTemplate())->toBe('/fonts/{slug}.css?family={family}&w={weights}');
});

it('hides the font picker when fonts are self-hosted', function (): void {
    expect(MonolithTheme::make()->hasFontPicker())->toBeTrue()
        ->and(MonolithTheme::make()->fontUrl('/fonts/{slug}.css')->hasFontPicker())->toBeFalse();
});
