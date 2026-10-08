<?php

declare(strict_types=1);

namespace HoceineEl\Monolith\Tests\Fixtures;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['monolith_appearance' => 'array'];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
