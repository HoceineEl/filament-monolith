<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\Products\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Workbench\App\Filament\Resources\Products\ProductResource;

class ManageProducts extends ManageRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->slideOver()];
    }
}
