<?php

declare(strict_types=1);

namespace HoceineEl\Monolith\Enums;

enum CustomizerSection: string
{
    case Mode = 'mode';
    case Sidebar = 'sidebar';
    case Accent = 'accent';
    case Base = 'base';
    case Radius = 'radius';
    case Density = 'density';
    case Font = 'font';
    case Badges = 'badges';
    case Behaviour = 'behaviour';

    /**
     * @return array<string>
     */
    public function getAppearanceKeys(): array
    {
        return match ($this) {
            self::Mode => [],
            self::Behaviour => ['connected', 'sticky'],
            default => [$this->value],
        };
    }
}
