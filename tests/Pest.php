<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use HoceineEl\Monolith\MonolithTheme;
use HoceineEl\Monolith\Tests\Fixtures\User;
use HoceineEl\Monolith\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class)->in(__DIR__);

function theme(): MonolithTheme
{
    /** @var MonolithTheme $theme */
    $theme = Filament::getPanel('admin')->getPlugin('monolith');

    return $theme;
}

function user(string $name = 'Ada'): User
{
    return User::create(['name' => $name, 'email' => strtolower($name).'@example.com', 'password' => 'secret']);
}
