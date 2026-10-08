<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Filament\Models\Contracts\HasAvatar;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements HasAvatar
{
    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    public function getFilamentAvatarUrl(): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" fill="#71717a"/><text x="32" y="41" font-family="sans-serif" font-size="24" font-weight="600" fill="#fff" text-anchor="middle">AL</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
