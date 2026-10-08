<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Workbench\App\Enums\OrderStatus;
use Workbench\App\Models\Order;

class OrderStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Revenue', '$'.number_format(Order::query()->sum('total') / 100, 2))
                ->description('12% up on February')
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->color('success')
                ->chart([7, 9, 8, 12, 11, 15, 18]),
            Stat::make('Orders', (string) Order::query()->count())
                ->description('3 awaiting fulfilment')
                ->descriptionIcon(Heroicon::Clock),
            Stat::make('Delivered', (string) Order::query()->where('status', OrderStatus::Delivered)->count())
                ->description('2 cancellations')
                ->descriptionIcon(Heroicon::ArrowTrendingDown)
                ->color('danger'),
        ];
    }
}
