@php
    $brandName = trim(strip_tags((string) filament()->getBrandName()));
    $monogram = str($brandName)->squish()->explode(' ')->take(2)->map(fn (string $word): string => mb_substr($word, 0, 1))->implode('');
@endphp

@if (blank(filament()->getBrandLogo()) && filled($monogram))
    <a
        {{ \Filament\Support\generate_href_html(filament()->getHomeUrl() ?? '/') }}
        class="mn-brand-monogram"
        aria-hidden="true"
        tabindex="-1"
    >
        {{ mb_strtoupper($monogram) }}
    </a>
@endif
