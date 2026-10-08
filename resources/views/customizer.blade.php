@php
    use Filament\Support\Icons\Heroicon;
    use HoceineEl\Monolith\Enums\Accent;
    use HoceineEl\Monolith\Enums\BadgeStyle;
    use HoceineEl\Monolith\Enums\BaseColor;
    use HoceineEl\Monolith\Enums\CustomizerSection;
    use HoceineEl\Monolith\Enums\Density;
    use HoceineEl\Monolith\Enums\Font;
    use HoceineEl\Monolith\Enums\Radius;
    use HoceineEl\Monolith\Enums\SidebarStyle;
    use HoceineEl\Monolith\MonolithTheme;

    use function Filament\Support\generate_icon_html;

    $radiusLabels = ['none' => '0', 'sm' => '0.375', 'md' => '0.625', 'lg' => '0.875', 'xl' => '1.125'];

    $theme = MonolithTheme::get();

    $fonts = $theme->getFonts();

    $defaultAccent = $theme->getAccent();

    $fontCases = collect(Font::cases())->mapWithKeys(fn (Font $font): array => [MonolithTheme::resolveFontFamily($font) => $font->name]);

    $enumNames = collect([
        'sidebar' => SidebarStyle::class,
        'density' => Density::class,
        'radius' => Radius::class,
        'accent' => Accent::class,
        'base' => BaseColor::class,
        'badges' => BadgeStyle::class,
    ])->map(fn (string $enum): array => [
        class_basename($enum),
        collect($enum::cases())->mapWithKeys(fn (BackedEnum $case): array => [$case->value => $case->name]),
    ]);

    $switches = [
        'connected' => ['method' => 'connectedNavigation', 'label' => __('monolith::customizer.switches.connected'), 'hint' => __('monolith::customizer.switches.connected_hint')],
        'sticky' => ['method' => 'stickyActions', 'label' => __('monolith::customizer.switches.sticky'), 'hint' => __('monolith::customizer.switches.sticky_hint')],
    ];
@endphp

<div
    x-data="monolithCustomizer({ enumNames: @js($enumNames), switches: @js(collect($switches)->map(fn (array $switch): string => $switch['method'])), defaultMode: @js(filament()->getDefaultThemeMode()->value), fonts: @js($fonts), fontCases: @js($fontCases) })"
    x-on:monolith-customizer-open.window="show()"
    x-on:keydown.escape.window="open = false"
    class="mn-customizer"
>
    <div
        x-cloak
        x-show="open"
        x-on:click="open = false"
        x-transition:enter="mn-fade-enter"
        x-transition:enter-start="mn-fade-enter-start"
        x-transition:leave="mn-fade-leave"
        x-transition:leave-end="mn-fade-leave-end"
        class="mn-sheet-overlay"
        aria-hidden="true"
    ></div>

    <section
        x-cloak
        x-show="open"
        x-trap.inert.noscroll="open"
        x-transition:enter="mn-sheet-enter"
        x-transition:enter-start="mn-sheet-enter-start"
        x-transition:leave="mn-sheet-leave"
        x-transition:leave-end="mn-sheet-leave-end"
        role="dialog"
        aria-modal="true"
        aria-labelledby="mn-customizer-title"
        aria-describedby="mn-customizer-description"
        class="mn-sheet"
    >
        <header class="mn-sheet-header">
            <div>
                <h2 id="mn-customizer-title" class="mn-sheet-title">{{ __('monolith::customizer.title') }}</h2>
                <p id="mn-customizer-description" class="mn-sheet-description">{{ __('monolith::customizer.description') }}</p>
            </div>

            <x-filament::icon-button
                :icon="Heroicon::OutlinedXMark"
                color="gray"
                size="sm"
                :label="__('monolith::customizer.close')"
                x-on:click="open = false"
                class="mn-sheet-close"
            />
        </header>

        <div
            class="mn-sheet-body"
            x-on:keydown="moveRadio($event)"
            x-effect="appearance; mode; $nextTick(() => syncRadios())"
        >
            @if ($theme->hasCustomizerSection(CustomizerSection::Mode))
                <div class="mn-field" role="radiogroup" aria-labelledby="mn-label-mode">
                    <span id="mn-label-mode" class="mn-field-label">{{ __('monolith::customizer.mode') }}</span>
                    <div class="mn-segmented">
                        @foreach (['light' => Heroicon::OutlinedSun, 'dark' => Heroicon::OutlinedMoon, 'system' => Heroicon::OutlinedComputerDesktop] as $mode => $icon)
                            <button
                                type="button"
                                role="radio"
                                x-bind:aria-checked="mode === @js($mode) ? 'true' : 'false'"
                                x-on:click="setMode(@js($mode))"
                            >
                                {{ generate_icon_html($icon) }}
                                {{ __("monolith::customizer.modes.{$mode}") }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($theme->hasCustomizerSection(CustomizerSection::Sidebar))
                <div class="mn-field" role="radiogroup" aria-labelledby="mn-label-sidebar">
                    <span id="mn-label-sidebar" class="mn-field-label">{{ __('monolith::customizer.sidebar') }}</span>
                    <div class="mn-tiles">
                        @foreach (SidebarStyle::cases() as $style)
                            <button
                                type="button"
                                role="radio"
                                class="mn-tile"
                                x-bind:aria-checked="appearance.sidebar === @js($style->value) ? 'true' : 'false'"
                                x-on:click="set('sidebar', @js($style->value))"
                            >
                                <span class="mn-tile-preview" data-variant="{{ $style->value }}" aria-hidden="true">
                                    <i class="mn-p-sidebar"></i>
                                    <i class="mn-p-main"></i>
                                </span>
                                <span>{{ __("monolith::customizer.sidebars.{$style->value}") }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($theme->hasCustomizerSection(CustomizerSection::Accent))
                <div class="mn-field" role="radiogroup" aria-labelledby="mn-label-accent">
                    <span id="mn-label-accent" class="mn-field-label">
                        {{ __('monolith::customizer.accent') }}
                        <span class="mn-field-value" x-text="@js(__('monolith::customizer.accents'))[appearance.accent]"></span>
                    </span>
                    <div class="mn-swatches">
                        @if ($defaultAccent === MonolithTheme::CUSTOM_ACCENT)
                            <button
                                type="button"
                                role="radio"
                                class="mn-swatch"
                                x-bind:aria-checked="appearance.accent === 'custom' ? 'true' : 'false'"
                                x-on:click="set('accent', 'custom')"
                            >
                                <span class="mn-swatch-dot" style="--mn-dot: {{ $theme->getAccentSwatch() }}" aria-hidden="true"></span>
                                {{ __('monolith::customizer.accents.custom') }}
                            </button>
                        @endif

                        @foreach ($theme->getAccents() as $accent)
                            <button
                                type="button"
                                role="radio"
                                class="mn-swatch"
                                x-bind:aria-checked="appearance.accent === @js($accent->value) ? 'true' : 'false'"
                                x-on:click="set('accent', @js($accent->value))"
                            >
                                <span class="mn-swatch-dot" style="--mn-dot: {{ $accent->getSwatch() }}" aria-hidden="true"></span>
                                {{ __("monolith::customizer.accents.{$accent->value}") }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($theme->hasCustomizerSection(CustomizerSection::Base))
                <div class="mn-field" role="radiogroup" aria-labelledby="mn-label-base">
                    <span id="mn-label-base" class="mn-field-label">
                        {{ __('monolith::customizer.base') }}
                        <span class="mn-field-value" x-text="@js(__('monolith::customizer.bases'))[appearance.base]"></span>
                    </span>
                    <div class="mn-swatches">
                        @foreach ($theme->getBaseColors() as $base)
                            <button
                                type="button"
                                role="radio"
                                class="mn-swatch"
                                x-bind:aria-checked="appearance.base === @js($base->value) ? 'true' : 'false'"
                                x-on:click="set('base', @js($base->value))"
                            >
                                <span class="mn-swatch-dot" style="--mn-dot: {{ $base->getSwatch() }}" aria-hidden="true"></span>
                                {{ __("monolith::customizer.bases.{$base->value}") }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($theme->hasCustomizerSection(CustomizerSection::Radius))
                <div class="mn-field" role="radiogroup" aria-labelledby="mn-label-radius">
                    <span id="mn-label-radius" class="mn-field-label">
                        {{ __('monolith::customizer.radius') }}
                        <span class="mn-field-value mn-field-value-plain">rem</span>
                    </span>
                    <div class="mn-segmented">
                        @foreach ($radiusLabels as $radius => $label)
                            <button
                                type="button"
                                role="radio"
                                x-bind:aria-checked="appearance.radius === @js($radius) ? 'true' : 'false'"
                                x-on:click="set('radius', @js($radius))"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($theme->hasCustomizerSection(CustomizerSection::Density))
                <div class="mn-field" role="radiogroup" aria-labelledby="mn-label-density">
                    <span id="mn-label-density" class="mn-field-label">{{ __('monolith::customizer.density') }}</span>
                    <div class="mn-segmented">
                        @foreach (Density::cases() as $density)
                            <button
                                type="button"
                                role="radio"
                                x-bind:aria-checked="appearance.density === @js($density->value) ? 'true' : 'false'"
                                x-on:click="set('density', @js($density->value))"
                            >
                                {{ __("monolith::customizer.densities.{$density->value}") }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($theme->hasCustomizerSection(CustomizerSection::Font))
                <div class="mn-field">
                    <span id="mn-label-font" class="mn-field-label">
                        {{ __('monolith::customizer.font') }}
                        <span class="mn-field-value mn-field-value-plain" x-text="appearance.font === 'system' ? @js(__('monolith::customizer.system_font')) : appearance.font"></span>
                    </span>

                    <div class="mn-fonts" role="radiogroup" aria-labelledby="mn-label-font">
                        @foreach ($fonts as $font)
                            <button
                                type="button"
                                role="radio"
                                class="mn-font"
                                @if ($font !== Font::System->value) style="font-family: '{{ $font }}', var(--font-family)" @endif
                                x-bind:aria-checked="appearance.font === @js($font) ? 'true' : 'false'"
                                x-on:click="set('font', @js($font))"
                            >
                                <span class="mn-font-sample" aria-hidden="true">{{ __('monolith::customizer.font_sample') }}</span>
                                <span class="mn-font-name">{{ $font === Font::System->value ? __('monolith::customizer.system_font') : $font }}</span>
                            </button>
                        @endforeach

                        <template x-if="isCustomFont()">
                            <button
                                type="button"
                                role="radio"
                                class="mn-font"
                                aria-checked="true"
                                x-bind:style="`font-family: '${appearance.font}', var(--font-family)`"
                            >
                                <span class="mn-font-sample" aria-hidden="true">{{ __('monolith::customizer.font_sample') }}</span>
                                <span class="mn-font-name" x-text="appearance.font"></span>
                            </button>
                        </template>
                    </div>

                    @if ($theme->hasFontPicker())
                        <div class="mn-font-picker">
                            <div class="mn-font-search">
                                {{ generate_icon_html(Heroicon::OutlinedMagnifyingGlass) }}
                                <input
                                    type="search"
                                    role="combobox"
                                    aria-autocomplete="list"
                                    aria-controls="mn-font-results"
                                    x-bind:aria-expanded="fontResults.length ? 'true' : 'false'"
                                    x-bind:aria-activedescendant="fontActive >= 0 ? `mn-font-option-${fontActive}` : null"
                                    placeholder="{{ __('monolith::customizer.font_search') }}"
                                    aria-label="{{ __('monolith::customizer.font_search') }}"
                                    x-model="fontQuery"
                                    x-on:focus="loadFontCatalog()"
                                    x-on:input.debounce.120ms="searchFonts()"
                                    x-on:keydown.arrow-down.prevent="moveFont(1)"
                                    x-on:keydown.arrow-up.prevent="moveFont(-1)"
                                    x-on:keydown.enter.prevent="pickFont()"
                                />
                            </div>

                            <label class="mn-font-filter">
                                <input type="checkbox" x-model="arabicOnly" x-on:change="searchFonts()" />
                                {{ __('monolith::customizer.font_arabic_only') }}
                            </label>

                            <p x-cloak x-show="fontCatalogFailed" class="mn-font-empty">{{ __('monolith::customizer.font_unavailable') }}</p>
                            <p x-cloak x-show="fontCatalog && ! fontResults.length" class="mn-font-empty">{{ __('monolith::customizer.font_no_results') }}</p>

                            <ul
                                x-cloak
                                x-show="fontResults.length"
                                x-ref="fontResults"
                                id="mn-font-results"
                                role="listbox"
                                aria-labelledby="mn-label-font"
                                class="mn-font-results"
                            >
                                <template x-for="([family, category, arabic], index) in fontResults" x-bind:key="family">
                                    <li
                                        role="option"
                                        x-bind:id="`mn-font-option-${index}`"
                                        x-bind:aria-selected="appearance.font === family ? 'true' : 'false'"
                                        x-bind:data-active="fontActive === index"
                                        x-on:mouseenter="previewFont(index)"
                                        x-on:click="pickFont(index)"
                                    >
                                        <span class="mn-font-result-name" x-bind:style="`font-family: '${family}', var(--font-family)`" x-text="family"></span>
                                        <span class="mn-font-result-meta">
                                            <span x-show="arabic" class="mn-font-tag">{{ __('monolith::customizer.font_arabic') }}</span>
                                            <span x-text="category"></span>
                                        </span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

            @if ($theme->hasCustomizerSection(CustomizerSection::Badges))
                <div class="mn-field" role="radiogroup" aria-labelledby="mn-label-badges">
                    <span id="mn-label-badges" class="mn-field-label">{{ __('monolith::customizer.badges') }}</span>
                    <div class="mn-segmented">
                        @foreach (BadgeStyle::cases() as $style)
                            <button
                                type="button"
                                role="radio"
                                x-bind:aria-checked="appearance.badges === @js($style->value) ? 'true' : 'false'"
                                x-on:click="set('badges', @js($style->value))"
                            >
                                {{ __("monolith::customizer.badge_styles.{$style->value}") }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($theme->hasCustomizerSection(CustomizerSection::Behaviour))
                <div class="mn-field">
                    <span class="mn-field-label">{{ __('monolith::customizer.behaviour') }}</span>
                    <div class="mn-switches">
                        @foreach ($switches as $name => $switch)
                            <button
                                type="button"
                                role="switch"
                                class="mn-switch-row"
                                x-bind:aria-checked="appearance.{{ $name }} === 'on' ? 'true' : 'false'"
                                x-on:click="toggle(@js($name))"
                            >
                                <span class="mn-switch-text">
                                    <span>{{ $switch['label'] }}</span>
                                    <span>{{ $switch['hint'] }}</span>
                                </span>
                                <span class="mn-switch" aria-hidden="true"><span></span></span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <footer class="mn-sheet-footer">
            <x-filament::button color="gray" x-on:click="reset()">
                {{ __('monolith::customizer.reset') }}
            </x-filament::button>

            <x-filament::button x-on:click="copy()" :icon="Heroicon::OutlinedClipboardDocument">
                <span x-show="! copied">{{ __('monolith::customizer.copy') }}</span>
                <span x-cloak x-show="copied">{{ __('monolith::customizer.copied') }}</span>
            </x-filament::button>
        </footer>
    </section>
</div>
