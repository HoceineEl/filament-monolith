<button
    type="button"
    class="mn-customizer-trigger"
    aria-label="{{ __('monolith::customizer.open') }}"
    aria-haspopup="dialog"
    x-data="{}"
    x-tooltip="{ content: @js(__('monolith::customizer.title')), theme: $store.theme }"
    x-on:click="$dispatch('monolith-customizer-open')"
>
    {{ \Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::OutlinedSwatch) }}
</button>
