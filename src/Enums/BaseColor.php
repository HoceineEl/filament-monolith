<?php

declare(strict_types=1);

namespace HoceineEl\Monolith\Enums;

enum BaseColor: string
{
    case Neutral = 'neutral';
    case Zinc = 'zinc';
    case Slate = 'slate';
    case Gray = 'gray';
    case Stone = 'stone';
    case Mauve = 'mauve';
    case Olive = 'olive';
    case Sage = 'sage';

    public function getSwatch(): string
    {
        return match ($this) {
            self::Neutral => 'oklch(0.552 0 0)',
            self::Zinc => 'oklch(0.552 0.016 286)',
            self::Slate => 'oklch(0.552 0.046 257)',
            self::Gray => 'oklch(0.552 0.028 264)',
            self::Stone => 'oklch(0.552 0.013 58)',
            self::Mauve => 'oklch(0.552 0.022 325)',
            self::Olive => 'oklch(0.552 0.02 110)',
            self::Sage => 'oklch(0.552 0.018 155)',
        };
    }
}
