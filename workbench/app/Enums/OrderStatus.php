<?php

declare(strict_types=1);

namespace Workbench\App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Processing => 'warning',
            self::Shipped => 'info',
            self::Delivered => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Pending => Heroicon::OutlinedClock,
            self::Processing => Heroicon::OutlinedArrowPath,
            self::Shipped => Heroicon::OutlinedTruck,
            self::Delivered => Heroicon::OutlinedCheckCircle,
            self::Cancelled => Heroicon::OutlinedXCircle,
        };
    }
}
