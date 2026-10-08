<?php

declare(strict_types=1);

namespace HoceineEl\Monolith\Enums;

enum Accent: string
{
    case Neutral = 'neutral';
    case Blue = 'blue';
    case Indigo = 'indigo';
    case Violet = 'violet';
    case Rose = 'rose';
    case Orange = 'orange';
    case Amber = 'amber';
    case Emerald = 'emerald';
    case Teal = 'teal';
    case Sky = 'sky';

    public function getSwatch(): string
    {
        return match ($this) {
            self::Neutral => 'oklch(0.21 0.006 286)',
            self::Blue => 'oklch(0.546 0.245 262.881)',
            self::Indigo => 'oklch(0.511 0.262 276.966)',
            self::Violet => 'oklch(0.541 0.281 293.009)',
            self::Rose => 'oklch(0.586 0.253 17.585)',
            self::Orange => 'oklch(0.646 0.222 41.116)',
            self::Amber => 'oklch(0.666 0.179 58.318)',
            self::Emerald => 'oklch(0.596 0.145 163.225)',
            self::Teal => 'oklch(0.6 0.118 184.704)',
            self::Sky => 'oklch(0.588 0.158 241.966)',
        };
    }
}
