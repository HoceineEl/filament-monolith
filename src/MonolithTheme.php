<?php

declare(strict_types=1);

namespace HoceineEl\Monolith;

use Closure;
use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\Enums\UserMenuPosition;
use Filament\FontProviders\LocalFontProvider;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use HoceineEl\Monolith\Enums\Accent;
use HoceineEl\Monolith\Enums\BadgeStyle;
use HoceineEl\Monolith\Enums\BaseColor;
use HoceineEl\Monolith\Enums\CustomizerSection;
use HoceineEl\Monolith\Enums\Density;
use HoceineEl\Monolith\Enums\Font;
use HoceineEl\Monolith\Enums\Radius;
use HoceineEl\Monolith\Enums\SidebarStyle;
use HoceineEl\Monolith\Http\Controllers\SaveAppearanceController;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class MonolithTheme implements Plugin
{
    use EvaluatesClosures;

    public const PACKAGE = 'hoceineel/filament-monolith';

    public const CUSTOM_ACCENT = 'custom';

    public const BUNNY_FONT_URL = 'https://fonts.bunny.net/css?family={slug}:{weights}&display=swap';

    protected SidebarStyle|Closure $sidebar = SidebarStyle::Inset;

    protected Density|Closure $density = Density::Default;

    protected Radius|Closure $radius = Radius::Medium;

    /**
     * @var Accent | string | array<int | string, string> | Closure
     */
    protected Accent|string|array|Closure $accent = Accent::Neutral;

    protected BaseColor|Closure $base = BaseColor::Zinc;

    protected Font|string|Closure $font = Font::Geist;

    protected ?string $fontProvider = null;

    protected ?string $fontUrl = null;

    protected BadgeStyle|Closure $badges = BadgeStyle::Soft;

    /**
     * @var array<Font | string> | null
     */
    protected ?array $fonts = null;

    /**
     * @var array<Accent> | null
     */
    protected ?array $accents = null;

    /**
     * @var array<BaseColor> | null
     */
    protected ?array $baseColors = null;

    /**
     * @var array<string> | Closure | null
     */
    protected array|Closure|null $fallbackFonts = null;

    /**
     * @var array<CustomizerSection> | null
     */
    protected ?array $customizerSections = null;

    /**
     * @var array<string, string> | Closure
     */
    protected array|Closure $tokens = [];

    protected ?Closure $loadAppearanceUsing = null;

    protected ?Closure $saveAppearanceUsing = null;

    protected bool|Closure $hasCustomizer = true;

    protected bool $hasFontPicker = true;

    protected bool $hasCommandPalette = true;

    protected bool $hasUserMenuInSidebar = true;

    protected bool $isSidebarCollapsible = true;

    protected bool $shouldConfigurePanelColors = true;

    protected bool $hasTopbarBreadcrumbs = true;

    protected bool $hasBrandMonogram = true;

    protected bool $hasChartStyling = true;

    protected bool|Closure $hasConnectedNavigation = false;

    protected bool|Closure $hasStickyActions = true;

    protected string $sidebarWidth = '16rem';

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'monolith';
    }

    public function sidebar(SidebarStyle|Closure $style): static
    {
        $this->sidebar = $style;

        return $this;
    }

    public function floatingSidebar(): static
    {
        return $this->sidebar(SidebarStyle::Floating);
    }

    public function insetSidebar(): static
    {
        return $this->sidebar(SidebarStyle::Inset);
    }

    public function classicSidebar(): static
    {
        return $this->sidebar(SidebarStyle::Classic);
    }

    public function density(Density|Closure $density): static
    {
        $this->density = $density;

        return $this;
    }

    public function compact(): static
    {
        return $this->density(Density::Compact);
    }

    public function comfortable(): static
    {
        return $this->density(Density::Comfortable);
    }

    public function radius(Radius|Closure $radius): static
    {
        $this->radius = $radius;

        return $this;
    }

    /**
     * @param  Accent | string | array<int | string, string> | Closure  $accent  An accent, a hex brand color, or a Filament color palette.
     */
    public function accent(Accent|string|array|Closure $accent): static
    {
        $this->accent = $accent;

        return $this;
    }

    /**
     * @param  array<Accent>  $accents
     */
    public function accents(array $accents): static
    {
        $this->accents = $accents;

        return $this;
    }

    public function base(BaseColor|Closure $base): static
    {
        $this->base = $base;

        return $this;
    }

    /**
     * @param  array<BaseColor>  $baseColors
     */
    public function baseColors(array $baseColors): static
    {
        $this->baseColors = $baseColors;

        return $this;
    }

    public function font(Font|string|Closure $font, ?string $provider = null): static
    {
        $this->font = $font;
        $this->fontProvider = $provider;

        return $this;
    }

    /**
     * @param  string  $template  A stylesheet URL with `{slug}`, `{family}` and `{weights}` placeholders, e.g. `/fonts/{slug}.css`.
     */
    public function fontUrl(string $template): static
    {
        $this->fontUrl = $template;

        return $this;
    }

    /**
     * @param  array<Font | string>  $fonts
     */
    public function fonts(array $fonts): static
    {
        $this->fonts = $fonts;

        return $this;
    }

    public function fontPicker(bool $condition = true): static
    {
        $this->hasFontPicker = $condition;

        return $this;
    }

    /**
     * @param  array<string> | Closure  $fonts
     */
    public function fallbackFonts(array|Closure $fonts): static
    {
        $this->fallbackFonts = $fonts;

        return $this;
    }

    public function badges(BadgeStyle|Closure $style): static
    {
        $this->badges = $style;

        return $this;
    }

    public function customizer(bool|Closure $condition = true): static
    {
        $this->hasCustomizer = $condition;

        return $this;
    }

    /**
     * @param  array<CustomizerSection>  $sections
     */
    public function customizerSections(array $sections): static
    {
        $this->customizerSections = $sections;

        return $this;
    }

    /**
     * @param  array<string, string> | Closure  $tokens
     */
    public function tokens(array|Closure $tokens): static
    {
        $this->tokens = $tokens;

        return $this;
    }

    public function persistAppearanceUsing(Closure $load, Closure $save): static
    {
        $this->loadAppearanceUsing = $load;
        $this->saveAppearanceUsing = $save;

        return $this;
    }

    public function persistAppearanceOnUser(string $column = 'monolith_appearance'): static
    {
        return $this->persistAppearanceUsing(
            load: fn (?Authenticatable $user): array => (array) ($user?->getAttribute($column) ?? []),
            save: fn (array $appearance, ?Authenticatable $user) => $user?->forceFill([$column => $appearance])->save(),
        );
    }

    public function commandPalette(bool $condition = true): static
    {
        $this->hasCommandPalette = $condition;

        return $this;
    }

    public function userMenuInSidebar(bool $condition = true): static
    {
        $this->hasUserMenuInSidebar = $condition;

        return $this;
    }

    public function sidebarCollapsible(bool $condition = true): static
    {
        $this->isSidebarCollapsible = $condition;

        return $this;
    }

    public function topbarBreadcrumbs(bool $condition = true): static
    {
        $this->hasTopbarBreadcrumbs = $condition;

        return $this;
    }

    public function brandMonogram(bool $condition = true): static
    {
        $this->hasBrandMonogram = $condition;

        return $this;
    }

    public function connectedNavigation(bool|Closure $condition = true): static
    {
        $this->hasConnectedNavigation = $condition;

        return $this;
    }

    public function stickyActions(bool|Closure $condition = true): static
    {
        $this->hasStickyActions = $condition;

        return $this;
    }

    public function chartStyling(bool $condition = true): static
    {
        $this->hasChartStyling = $condition;

        return $this;
    }

    public function sidebarWidth(string $width): static
    {
        $this->sidebarWidth = $width;

        return $this;
    }

    public function configurePanelColors(bool $condition = true): static
    {
        $this->shouldConfigurePanelColors = $condition;

        return $this;
    }

    public function hasCustomizer(): bool
    {
        return (bool) $this->evaluate($this->hasCustomizer);
    }

    public function hasFontPicker(): bool
    {
        return $this->hasFontPicker && $this->fontUrl === null && $this->hasCustomizerSection(CustomizerSection::Font);
    }

    public function hasCustomizerSection(CustomizerSection $section): bool
    {
        return in_array($section, $this->getCustomizerSections(), true);
    }

    /**
     * @return array<CustomizerSection>
     */
    public function getCustomizerSections(): array
    {
        return $this->customizerSections ?? CustomizerSection::cases();
    }

    /**
     * @return array<string>
     */
    public function getCustomizableKeys(): array
    {
        return collect($this->getCustomizerSections())
            ->flatMap(fn (CustomizerSection $section): array => $section->getAppearanceKeys())
            ->values()
            ->all();
    }

    public function getAccent(): Accent|string
    {
        $accent = $this->evaluate($this->accent);

        return $accent instanceof Accent ? $accent : static::CUSTOM_ACCENT;
    }

    public function getAccentSwatch(): string
    {
        return $this->getPrimaryPalette()[500] ?? 'var(--primary-500)';
    }

    /**
     * @return array<Accent>
     */
    public function getAccents(): array
    {
        return $this->accents ?? Accent::cases();
    }

    /**
     * @return array<BaseColor>
     */
    public function getBaseColors(): array
    {
        return $this->baseColors ?? BaseColor::cases();
    }

    public function getFontFamily(): string
    {
        return static::resolveFontFamily($this->evaluate($this->font));
    }

    /**
     * @return array<string>
     */
    public function getFonts(): array
    {
        $fonts = $this->fonts ?? [
            Font::Geist,
            Font::Inter,
            Font::Figtree,
            Font::IbmPlexSans,
            ...($this->isArabicScriptLocale() ? [Font::IbmPlexSansArabic, Font::Tajawal, Font::Cairo] : []),
            Font::System,
        ];

        return collect($fonts)
            ->map(fn (Font|string $font): string => static::resolveFontFamily($font))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string>
     */
    public function getFallbackFonts(): array
    {
        $fonts = $this->evaluate($this->fallbackFonts);

        if ($fonts !== null) {
            return $fonts;
        }

        return $this->isArabicScriptLocale() ? [Font::IbmPlexSansArabic->getFamily()] : [];
    }

    /**
     * @return array<string, string>
     */
    public function getTokens(): array
    {
        $tokens = (array) $this->evaluate($this->tokens);

        if ($this->getAccent() === static::CUSTOM_ACCENT) {
            $tokens = ['--mn-primary-fg' => $this->getCustomAccentForeground(), ...$tokens];
        }

        return collect($tokens)
            ->filter(fn (mixed $value, string $token): bool => is_scalar($value) && preg_match('/^--[\w-]+$/', $token) === 1)
            ->map(fn (mixed $value): string => str_replace(['<', '>', '{', '}', ';', '\\'], '', (string) $value))
            ->all();
    }

    public function getFontUrlTemplate(): string
    {
        return $this->fontUrl ?? static::BUNNY_FONT_URL;
    }

    public function getFontUrl(string $family, string $weights = '400,500,600,700'): string
    {
        return strtr($this->getFontUrlTemplate(), [
            '{slug}' => Str::slug($family),
            '{family}' => rawurlencode($family),
            '{weights}' => $weights,
        ]);
    }

    public static function resolveFontFamily(Font|string $font): string
    {
        return $font instanceof Font ? ($font->getFamily() ?? Font::System->value) : $font;
    }

    /**
     * @return array{sidebar: string, density: string, radius: string, accent: string, base: string, font: string, badges: string, connected: string, sticky: string, crumbs: string}
     */
    public function getAppearance(): array
    {
        $accent = $this->getAccent();

        return [
            'sidebar' => $this->evaluate($this->sidebar)->value,
            'density' => $this->evaluate($this->density)->value,
            'radius' => $this->evaluate($this->radius)->value,
            'accent' => $accent instanceof Accent ? $accent->value : $accent,
            'base' => $this->evaluate($this->base)->value,
            'font' => $this->getFontFamily(),
            'badges' => $this->evaluate($this->badges)->value,
            'connected' => $this->evaluate($this->hasConnectedNavigation) ? 'on' : 'off',
            'sticky' => $this->evaluate($this->hasStickyActions) ? 'on' : 'off',
            'crumbs' => $this->hasTopbarBreadcrumbs ? 'topbar' : 'page',
        ];
    }

    public function isAppearanceStoredOnServer(): bool
    {
        return $this->loadAppearanceUsing !== null && $this->saveAppearanceUsing !== null;
    }

    /**
     * @return array<string, string> | null
     */
    public function loadAppearance(): ?array
    {
        if (! $this->isAppearanceStoredOnServer()) {
            return null;
        }

        return collect((array) $this->evaluate($this->loadAppearanceUsing, ['user' => filament()->auth()->user()]))
            ->only($this->getCustomizableKeys())
            ->filter(fn (mixed $value): bool => is_string($value))
            ->all();
    }

    /**
     * @param  array<string, string>  $appearance
     */
    public function saveAppearance(array $appearance, ?Authenticatable $user): void
    {
        $this->evaluate($this->saveAppearanceUsing, ['appearance' => $appearance, 'user' => $user]);
    }

    public function register(Panel $panel): void
    {
        $panel
            ->sidebarWidth($this->sidebarWidth)
            ->collapsedSidebarWidth('3.5rem')
            ->sidebarCollapsibleOnDesktop($this->isSidebarCollapsible)
            ->monoFont('Geist Mono')
            ->font(
                fn (): string => $this->usesSystemFont() ? 'system-ui' : $this->getFontFamily(),
                url: fn (): ?string => $this->usesSystemFont() || $this->fontUrl === null ? null : $this->getFontUrl($this->getFontFamily()),
                provider: fn (): ?string => $this->usesSystemFont() ? LocalFontProvider::class : $this->fontProvider,
            )
            ->renderHook(PanelsRenderHook::HEAD_START, fn (): View => view('monolith::head', [
                'appearance' => $this->getAppearance(),
                'customizable' => $this->hasCustomizer() ? $this->getCustomizableKeys() : [],
                'stored' => $this->hasCustomizer() ? $this->loadAppearance() : null,
                'endpoint' => $this->isAppearanceStoredOnServer() ? $panel->route('monolith.appearance') : null,
                'charts' => $this->hasChartStyling,
                'fallbackFonts' => $this->getFallbackFonts(),
                'fontUrl' => $this->getFontUrlTemplate(),
                'searchHint' => $this->getGlobalSearchHint(),
            ]))
            ->renderHook(PanelsRenderHook::STYLES_BEFORE, fn (): View => view('monolith::styles', [
                'tokens' => $this->getTokens(),
            ]))
            ->authenticatedRoutes(fn () => Route::post('monolith/appearance', SaveAppearanceController::class)->name('monolith.appearance'));

        if ($this->hasTopbarBreadcrumbs) {
            $panel->renderHook(PanelsRenderHook::TOPBAR_LOGO_AFTER, fn (): View => view('monolith::topbar-breadcrumbs'));
        }

        if ($this->hasBrandMonogram) {
            $panel
                ->renderHook(PanelsRenderHook::SIDEBAR_LOGO_BEFORE, fn (): View => view('monolith::brand-monogram'))
                ->renderHook(PanelsRenderHook::TOPBAR_LOGO_BEFORE, fn (): View => view('monolith::brand-monogram'));
        }

        if ($this->shouldConfigurePanelColors) {
            $panel->colors(fn (): array => [
                'primary' => $this->getPrimaryPalette(),
                'gray' => Color::Zinc,
            ]);
        }

        if ($this->hasCommandPalette && $panel->getGlobalSearchKeyBindings() === []) {
            $panel->globalSearchKeyBindings(['mod+k']);
        }

        if ($this->hasUserMenuInSidebar) {
            $panel->userMenu(position: UserMenuPosition::Sidebar);
        }

        $panel
            ->renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER, fn (): ?View => $this->hasCustomizer() ? view('monolith::customizer-trigger') : null)
            ->renderHook(PanelsRenderHook::BODY_END, fn (): ?View => filament()->auth()->check() && $this->hasCustomizer() ? view('monolith::customizer') : null)
            ->userMenuItems([
                'monolith-appearance' => Action::make('monolithAppearance')
                    ->label(fn (): string => __('monolith::customizer.title'))
                    ->icon(Heroicon::OutlinedSwatch)
                    ->visible(fn (): bool => $this->hasCustomizer())
                    ->alpineClickHandler("\$dispatch('monolith-customizer-open')"),
            ]);
    }

    public function boot(Panel $panel): void {}

    /**
     * @return array<int | string, string>
     */
    protected function getPrimaryPalette(): array
    {
        $accent = $this->evaluate($this->accent);

        return match (true) {
            is_array($accent) => $accent,
            is_string($accent) => Color::hex($accent),
            $accent === Accent::Neutral => Color::Zinc,
            default => constant(Color::class.'::'.$accent->name),
        };
    }

    /**
     * @return array{mac: string, other: string}|null
     */
    public function getGlobalSearchHint(): ?array
    {
        $panel = filament()->getCurrentOrDefaultPanel();
        $bindings = $panel?->getGlobalSearchKeyBindings() ?? [];

        if ($bindings === [] || filled($panel?->getGlobalSearchFieldSuffix())) {
            return null;
        }

        $pick = fn (array $modifiers): string => collect($bindings)
            ->first(fn (string $binding): bool => Str::contains($binding, $modifiers)) ?? $bindings[0];

        return [
            'mac' => $this->formatKeyBinding($pick(['command', 'meta', 'mod']), mac: true),
            'other' => $this->formatKeyBinding($pick(['ctrl', 'mod']), mac: false),
        ];
    }

    protected function formatKeyBinding(string $binding, bool $mac): string
    {
        $labels = $mac
            ? ['command' => '⌘', 'meta' => '⌘', 'mod' => '⌘', 'ctrl' => '⌃', 'shift' => '⇧', 'alt' => '⌥', 'option' => '⌥']
            : ['command' => 'Ctrl', 'meta' => 'Ctrl', 'mod' => 'Ctrl', 'ctrl' => 'Ctrl', 'shift' => 'Shift', 'alt' => 'Alt', 'option' => 'Alt'];

        return collect(explode('+', $binding))
            ->map(fn (string $key): string => $labels[$key] ?? Str::upper($key))
            ->implode(' ');
    }

    protected function getCustomAccentForeground(): string
    {
        $shade = $this->getPrimaryPalette()[600] ?? '';

        preg_match('/oklch\(\s*([\d.]+)/', $shade, $matches);

        return (float) ($matches[1] ?? 0) > 0.7 ? 'oklch(0.21 0 0)' : 'oklch(1 0 0)';
    }

    protected function usesSystemFont(): bool
    {
        return $this->getFontFamily() === Font::System->value;
    }

    protected function isArabicScriptLocale(): bool
    {
        return in_array(Str::before(app()->getLocale(), '_'), ['ar', 'fa', 'ur', 'ps', 'ckb', 'sd', 'ug'], true);
    }
}
