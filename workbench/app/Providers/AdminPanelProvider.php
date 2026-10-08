<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use HoceineEl\Monolith\MonolithTheme;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Workbench\App\Filament\Resources\Orders\OrderResource;
use Workbench\App\Filament\Resources\Products\ProductResource;
use Workbench\App\Filament\Widgets\OrderStats;
use Workbench\App\Filament\Widgets\RevenueChart;
use Workbench\App\Http\Middleware\AuthenticateWorkbenchUser;
use Workbench\App\Http\Middleware\SetLocale;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->default()
            ->brandName('Northwind')
            ->resources([OrderResource::class, ProductResource::class])
            ->pages([Dashboard::class])
            ->widgets([OrderStats::class, RevenueChart::class])
            ->navigationGroups([NavigationGroup::make('Shop')])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->middleware([SetLocale::class, AuthenticateWorkbenchUser::class], isPersistent: true)
            ->plugin(MonolithTheme::make());
    }
}
