<div class="mn-mobile-brand">
    @include('monolith::brand-monogram')

    <a {{ \Filament\Support\generate_href_html(filament()->getHomeUrl() ?? '/') }}>
        <x-filament-panels::logo />
    </a>
</div>
