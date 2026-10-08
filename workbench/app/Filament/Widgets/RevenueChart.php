<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected ?string $heading = 'Revenue';

    protected ?string $description = 'Monthly revenue for the first half of 2026.';

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '260px';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'datasets' => [
                ['label' => 'Online', 'data' => [4200, 5100, 4800, 6300, 7100, 6900], 'fill' => true],
                ['label' => 'In store', 'data' => [2100, 2400, 2300, 2900, 3100, 3600]],
            ],
        ];
    }
}
