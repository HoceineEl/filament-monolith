<?php

declare(strict_types=1);

use HoceineEl\Monolith\Enums\CustomizerSection;
use HoceineEl\Monolith\MonolithTheme;

it('makes every appearance key customizable by default', function (): void {
    $theme = MonolithTheme::make();

    expect($theme->getCustomizerSections())->toBe(CustomizerSection::cases())
        ->and($theme->getCustomizableKeys())->toBe([
            'sidebar', 'accent', 'base', 'radius', 'density', 'font', 'badges', 'connected', 'sticky',
        ])
        ->and($theme->hasFontPicker())->toBeTrue();
});

it('maps customizer sections to appearance keys', function (): void {
    $theme = MonolithTheme::make()->customizerSections([
        CustomizerSection::Mode,
        CustomizerSection::Accent,
        CustomizerSection::Behaviour,
    ]);

    expect($theme->getCustomizableKeys())->toBe(['accent', 'connected', 'sticky'])
        ->and($theme->hasCustomizerSection(CustomizerSection::Accent))->toBeTrue()
        ->and($theme->hasCustomizerSection(CustomizerSection::Font))->toBeFalse()
        ->and($theme->hasFontPicker())->toBeFalse();
});

it('hides the font picker when disabled', function (): void {
    expect(MonolithTheme::make()->fontPicker(false)->hasFontPicker())->toBeFalse();
});
