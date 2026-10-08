<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\Orders\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Workbench\App\Enums\OrderStatus;
use Workbench\App\Filament\Resources\Orders\OrderResource;
use Workbench\App\Models\Order;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            ...collect([OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Delivered])
                ->mapWithKeys(fn (OrderStatus $status): array => [
                    $status->value => Tab::make($status->getLabel())
                        ->badge(Order::query()->where('status', $status)->count())
                        ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', $status)),
                ])
                ->all(),
        ];
    }
}
