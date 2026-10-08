<?php

declare(strict_types=1);

use HoceineEl\Monolith\MonolithTheme;

it('keeps valid tokens', function (): void {
    expect(MonolithTheme::make()->tokens(['--mn-radius' => '0.5rem', '--mn_card-2' => 'red'])->getTokens())
        ->toBe(['--mn-radius' => '0.5rem', '--mn_card-2' => 'red']);
});

it('drops invalid token names', function (): void {
    expect(MonolithTheme::make()->tokens([
        'color' => 'red',
        '--' => 'red',
        '--bad name' => 'red',
        '--bad;name' => 'red',
        '--good' => 'blue',
    ])->getTokens())->toBe(['--good' => 'blue']);
});

it('strips characters that could break out of the style tag', function (): void {
    expect(MonolithTheme::make()->tokens(['--mn-x' => 'red;} </style><script>{x}\\'])->getTokens())
        ->toBe(['--mn-x' => 'red /stylescriptx']);
});

it('evaluates a tokens closure', function (): void {
    expect(MonolithTheme::make()->tokens(fn (): array => ['--mn-x' => '1px'])->getTokens())->toBe(['--mn-x' => '1px']);
});

it('lets tokens override the custom accent foreground', function (): void {
    expect(MonolithTheme::make()->accent('#0f766e')->tokens(['--mn-primary-fg' => 'white'])->getTokens())
        ->toBe(['--mn-primary-fg' => 'white']);
});

it('ignores list entries and non-scalar values', function (): void {
    expect(MonolithTheme::make()->tokens(['red', '--mn-x' => ['nested'], '--mn-y' => 2])->getTokens())
        ->toBe(['--mn-y' => '2']);
});
