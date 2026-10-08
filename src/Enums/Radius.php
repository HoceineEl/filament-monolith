<?php

declare(strict_types=1);

namespace HoceineEl\Monolith\Enums;

enum Radius: string
{
    case None = 'none';
    case Small = 'sm';
    case Medium = 'md';
    case Large = 'lg';
    case ExtraLarge = 'xl';
}
