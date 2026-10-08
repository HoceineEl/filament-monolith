<?php

declare(strict_types=1);
use Filament\Panel;
use HoceineEl\Monolith\MonolithTheme;

function dashboardUrl(): string
{
    return filament()->getPanel('admin')->getUrl();
}

it('renders the theme assets on the dashboard', function (): void {
    $this->actingAs(user())
        ->get(dashboardUrl())
        ->assertOk()
        ->assertSee('data-navigate-track', false)
        ->assertSee('monolith:appearance', false)
        ->assertSee('monolithCustomizer', false)
        ->assertSee('mn-customizer-trigger', false)
        ->assertSee('Appearance');
});

it('renders the appearance defaults into the head script', function (): void {
    theme()->floatingSidebar();

    $this->actingAs(user())
        ->get(dashboardUrl())
        ->assertOk()
        ->assertSee('\u0022sidebar\u0022:\u0022floating\u0022', false);
});

it('hides the customizer when disabled', function (): void {
    theme()->customizer(false);

    $this->actingAs(user())
        ->get(dashboardUrl())
        ->assertOk()
        ->assertSee('monolith:appearance', false)
        ->assertDontSee('x-data="monolithCustomizer', false)
        ->assertDontSee('mn-customizer-trigger', false);
});

it('does not render the customizer for guests', function (): void {
    $this->get(filament()->getPanel('admin')->getLoginUrl())
        ->assertOk()
        ->assertSee('monolith:appearance', false)
        ->assertDontSee('x-data="monolithCustomizer', false);
});

it('renders the customizer in Arabic', function (): void {
    app()->setLocale('ar');

    $this->actingAs(user())
        ->get(dashboardUrl())
        ->assertOk()
        ->assertSee('المظهر')
        ->assertSee('الشريط الجانبي')
        ->assertSee('fonts.bunny.net/css?family=ibm-plex-sans-arabic', false);
});

it('does not load a fallback font for English', function (): void {
    $this->actingAs(user())
        ->get(dashboardUrl())
        ->assertOk()
        ->assertDontSee('family=ibm-plex-sans-arabic', false);
});

it('renders the tokens into a style tag', function (): void {
    theme()->tokens(['--mn-radius' => '2rem', 'invalid' => 'red']);

    $this->actingAs(user())
        ->get(dashboardUrl())
        ->assertOk()
        ->assertSee('html.fi:root[data-mn-sidebar] { --mn-radius: 2rem;', false)
        ->assertDontSee('invalid: red', false);
});

it('renders the custom accent foreground token', function (): void {
    theme()->accent('#0f766e');

    $this->actingAs(user())
        ->get(dashboardUrl())
        ->assertOk()
        ->assertSee('--mn-primary-fg: oklch(1 0 0);', false)
        ->assertSee('\u0022accent\u0022:\u0022custom\u0022', false);
});

it('exposes the save endpoint only with server persistence', function (): void {
    $this->actingAs(user())
        ->get(dashboardUrl())
        ->assertSee('const endpoint = null;', false);

    theme()->persistAppearanceOnUser();

    $this->get(dashboardUrl())
        ->assertSee('monolith\/appearance', false);
});

it('shows the command palette hint by default', function (): void {
    expect(theme()->getGlobalSearchHint())->toBe(['mac' => '⌘ K', 'other' => 'Ctrl K']);
});

it('shows no search shortcut hint when the panel binds no search keys', function (): void {
    filament()->getPanel('admin')->globalSearchKeyBindings([]);

    expect(theme()->getGlobalSearchHint())->toBeNull();

    $this->actingAs(user())
        ->get(dashboardUrl())
        ->assertOk()
        ->assertSee('const searchHint = null', false);
});

it('derives the search shortcut hint from the panel key bindings', function (): void {
    filament()->getPanel('admin')->globalSearchKeyBindings(['command+k', 'ctrl+k']);

    expect(theme()->getGlobalSearchHint())->toBe(['mac' => '⌘ K', 'other' => 'Ctrl K']);

    $this->actingAs(user())
        ->get(dashboardUrl())
        ->assertOk()
        ->assertSee('data-mn-search-hint', false);
});

it('labels a custom search binding for both platforms', function (): void {
    filament()->getPanel('admin')->globalSearchKeyBindings(['mod+shift+f']);

    expect(theme()->getGlobalSearchHint())->toBe(['mac' => '⌘ ⇧ F', 'other' => 'Ctrl Shift F']);
});

it('leaves the hint to Filament when the panel shows its own key binding suffix', function (): void {
    filament()->getPanel('admin')
        ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
        ->globalSearchFieldKeyBindingSuffix();

    request()->headers->set('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0)');

    expect(theme()->getGlobalSearchHint())->toBeNull();

    request()->headers->set('User-Agent', 'CustomBrowser/1.0');

    expect(theme()->getGlobalSearchHint())->toBe(['mac' => '⌘ K', 'other' => 'Ctrl K']);
});

it('keeps search key bindings the panel already set', function (): void {
    $panel = Panel::make()->id('custom')->globalSearchKeyBindings(['ctrl+shift+s']);

    MonolithTheme::make()->register($panel);

    expect($panel->getGlobalSearchKeyBindings())->toBe(['ctrl+shift+s']);
});
