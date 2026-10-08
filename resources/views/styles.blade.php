<style>
    @layer properties, theme, base, components, monolith, utilities;
</style>

<link
    rel="stylesheet"
    href="{{ \Filament\Support\Facades\FilamentAsset::getStyleHref('monolith', \HoceineEl\Monolith\MonolithTheme::PACKAGE) }}"
    data-navigate-track
/>

@if (filled($tokens))
    <style>
        html.fi:root[data-mn-sidebar] { @foreach ($tokens as $token => $value){{ $token }}: {!! $value !!}; @endforeach }
    </style>
@endif
