<?php

declare(strict_types=1);

use HoceineEl\Monolith\Enums\Accent;
use HoceineEl\Monolith\Enums\BadgeStyle;
use HoceineEl\Monolith\Enums\BaseColor;
use HoceineEl\Monolith\Enums\Density;
use HoceineEl\Monolith\Enums\Radius;
use HoceineEl\Monolith\Enums\SidebarStyle;
use HoceineEl\Monolith\MonolithTheme;

it('has sensible default appearance values', function (): void {
    expect(MonolithTheme::make()->getAppearance())->toBe([
        'sidebar' => 'inset',
        'density' => 'default',
        'radius' => 'md',
        'accent' => 'neutral',
        'base' => 'zinc',
        'font' => 'Geist',
        'badges' => 'soft',
        'connected' => 'off',
        'sticky' => 'on',
        'crumbs' => 'topbar',
    ]);
});

it('reflects the fluent setters in the appearance', function (): void {
    $theme = MonolithTheme::make()
        ->floatingSidebar()
        ->compact()
        ->radius(Radius::ExtraLarge)
        ->accent(Accent::Emerald)
        ->base(BaseColor::Slate)
        ->badges(BadgeStyle::Outline)
        ->connectedNavigation()
        ->stickyActions(false)
        ->topbarBreadcrumbs(false);

    expect($theme->getAppearance())->toBe([
        'sidebar' => 'floating',
        'density' => 'compact',
        'radius' => 'xl',
        'accent' => 'emerald',
        'base' => 'slate',
        'font' => 'Geist',
        'badges' => 'outline',
        'connected' => 'on',
        'sticky' => 'off',
        'crumbs' => 'page',
    ]);
});

it('switches between the sidebar and density shortcuts', function (): void {
    $theme = MonolithTheme::make()->classicSidebar()->comfortable();

    expect($theme->getAppearance())
        ->sidebar->toBe('classic')
        ->density->toBe('comfortable');

    $theme->insetSidebar()->density(Density::Default);

    expect($theme->getAppearance())
        ->sidebar->toBe('inset')
        ->density->toBe('default');
});

it('evaluates closures lazily', function (): void {
    $accent = Accent::Rose;

    $theme = MonolithTheme::make()
        ->accent(function () use (&$accent): Accent {
            return $accent;
        })
        ->sidebar(fn (): SidebarStyle => SidebarStyle::Classic)
        ->density(fn (): Density => Density::Compact)
        ->connectedNavigation(fn (): bool => true)
        ->customizer(fn (): bool => false);

    $accent = Accent::Sky;

    expect($theme->getAppearance())
        ->accent->toBe('sky')
        ->sidebar->toBe('classic')
        ->density->toBe('compact')
        ->connected->toBe('on')
        ->and($theme->getAccent())->toBe(Accent::Sky)
        ->and($theme->hasCustomizer())->toBeFalse();
});

it('enables the customizer by default', function (): void {
    expect(MonolithTheme::make()->hasCustomizer())->toBeTrue()
        ->and(MonolithTheme::make()->customizer(false)->hasCustomizer())->toBeFalse();
});

it('treats a hex color as a custom accent', function (): void {
    $theme = MonolithTheme::make()->accent('#0f766e');

    expect($theme->getAccent())->toBe(MonolithTheme::CUSTOM_ACCENT)
        ->and($theme->getAppearance()['accent'])->toBe('custom')
        ->and($theme->getTokens())->toHaveKey('--mn-primary-fg')
        ->and($theme->getAccentSwatch())->toStartWith('oklch(');
});

it('picks a dark foreground for light custom palettes', function (): void {
    expect(MonolithTheme::make()->accent([600 => 'oklch(0.85 0.17 95)'])->getTokens()['--mn-primary-fg'])->toBe('oklch(0.21 0 0)')
        ->and(MonolithTheme::make()->accent([600 => 'oklch(0.45 0.1 260)'])->getTokens()['--mn-primary-fg'])->toBe('oklch(1 0 0)');
});

it('accepts a Filament palette as a custom accent', function (): void {
    $palette = [500 => 'oklch(0.6 0.1 180)', 600 => 'oklch(0.5 0.1 180)'];

    $theme = MonolithTheme::make()->accent($palette);

    expect($theme->getAccent())->toBe('custom')
        ->and($theme->getAccentSwatch())->toBe('oklch(0.6 0.1 180)')
        ->and($theme->getTokens())->toBe(['--mn-primary-fg' => 'oklch(1 0 0)']);
});

it('does not add a foreground token for preset accents', function (): void {
    expect(MonolithTheme::make()->accent(Accent::Blue)->getTokens())->toBe([]);
});

it('restricts the accent and base color lists', function (): void {
    $theme = MonolithTheme::make()
        ->accents([Accent::Blue, Accent::Rose])
        ->baseColors([BaseColor::Stone]);

    expect(MonolithTheme::make()->getAccents())->toBe(Accent::cases())
        ->and(MonolithTheme::make()->getBaseColors())->toBe(BaseColor::cases())
        ->and($theme->getAccents())->toBe([Accent::Blue, Accent::Rose])
        ->and($theme->getBaseColors())->toBe([BaseColor::Stone]);
});
