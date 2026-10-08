<?php

declare(strict_types=1);

namespace HoceineEl\Monolith\Enums;

enum Font: string
{
    case Geist = 'geist';
    case Inter = 'inter';
    case Figtree = 'figtree';
    case IbmPlexSans = 'ibm-plex-sans';
    case IbmPlexSansArabic = 'ibm-plex-sans-arabic';
    case Tajawal = 'tajawal';
    case Cairo = 'cairo';
    case System = 'system';

    public function getFamily(): ?string
    {
        return match ($this) {
            self::Geist => 'Geist',
            self::Inter => 'Inter',
            self::Figtree => 'Figtree',
            self::IbmPlexSans => 'IBM Plex Sans',
            self::IbmPlexSansArabic => 'IBM Plex Sans Arabic',
            self::Tajawal => 'Tajawal',
            self::Cairo => 'Cairo',
            self::System => null,
        };
    }
}
