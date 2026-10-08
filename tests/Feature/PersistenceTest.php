<?php

declare(strict_types=1);

use HoceineEl\Monolith\Enums\CustomizerSection;
use HoceineEl\Monolith\Tests\Fixtures\User;
use Illuminate\Contracts\Auth\Authenticatable;

function appearanceUrl(): string
{
    return filament()->getPanel('admin')->route('monolith.appearance');
}

/**
 * @return ArrayObject<string, mixed>
 */
function persistInMemory(): ArrayObject
{
    $store = new ArrayObject;

    theme()->persistAppearanceUsing(
        load: fn (): array => $store['appearance'] ?? [],
        save: function (array $appearance, ?Authenticatable $user) use ($store): void {
            $store['appearance'] = $appearance;
            $store['user'] = $user;
        },
    );

    return $store;
}

it('saves only the customizable keys', function (): void {
    $store = persistInMemory();
    $user = user();

    $this->actingAs($user)
        ->postJson(appearanceUrl(), ['appearance' => ['accent' => 'rose', 'sidebar' => 'floating', 'evil' => 'x', 'crumbs' => 'page']])
        ->assertNoContent();

    expect($store['appearance'])->toBe(['accent' => 'rose', 'sidebar' => 'floating'])
        ->and($store['user']->is($user))->toBeTrue();
});

it('respects the customizer sections when saving', function (): void {
    $store = persistInMemory();
    theme()->customizerSections([CustomizerSection::Behaviour]);

    $this->actingAs(user())
        ->postJson(appearanceUrl(), ['appearance' => ['accent' => 'rose', 'sticky' => 'off']])
        ->assertNoContent();

    expect($store['appearance'])->toBe(['sticky' => 'off']);
});

it('accepts an empty appearance', function (): void {
    $store = persistInMemory();

    $this->actingAs(user())
        ->postJson(appearanceUrl(), ['appearance' => []])
        ->assertNoContent();

    expect($store['appearance'])->toBe([]);
});

it('rejects invalid appearance values', function (array $payload): void {
    $store = persistInMemory();

    $this->actingAs(user())
        ->postJson(appearanceUrl(), $payload)
        ->assertUnprocessable();

    expect($store)->not->toHaveKey('appearance');
})->with([
    'missing' => [[]],
    'not an array' => [['appearance' => 'rose']],
    'nested value' => [['appearance' => ['accent' => ['rose']]]],
    'number' => [['appearance' => ['accent' => 5]]],
    'too long' => [['appearance' => ['font' => str_repeat('a', 101)]]],
]);

it('returns 404 without server persistence', function (): void {
    $this->actingAs(user())
        ->postJson(appearanceUrl(), ['appearance' => ['accent' => 'rose']])
        ->assertNotFound();
});

it('does not let guests save', function (): void {
    $store = persistInMemory();

    $this->postJson(appearanceUrl(), ['appearance' => ['accent' => 'rose']])->assertUnauthorized();
    $this->post(appearanceUrl(), ['appearance' => ['accent' => 'rose']])->assertRedirect(filament()->getPanel('admin')->getLoginUrl());

    expect($store)->not->toHaveKey('appearance');
});

it('loads only customizable string values', function (): void {
    theme()->persistAppearanceUsing(
        load: fn (): array => ['accent' => 'rose', 'crumbs' => 'page', 'sidebar' => ['x'], 'evil' => 'x'],
        save: fn (): null => null,
    );

    expect(theme()->loadAppearance())->toBe(['accent' => 'rose']);
});

it('loads nothing without server persistence', function (): void {
    expect(theme()->isAppearanceStoredOnServer())->toBeFalse()
        ->and(theme()->loadAppearance())->toBeNull();
});

it('persists the appearance on the user', function (): void {
    theme()->persistAppearanceOnUser();
    $user = user();

    $this->actingAs($user)
        ->postJson(appearanceUrl(), ['appearance' => ['accent' => 'teal', 'density' => 'compact', 'evil' => 'x']])
        ->assertNoContent();

    expect(User::find($user->id)->monolith_appearance)->toBe(['accent' => 'teal', 'density' => 'compact'])
        ->and(theme()->loadAppearance())->toBe(['accent' => 'teal', 'density' => 'compact']);
});

it('loads an empty appearance for a user without one', function (): void {
    theme()->persistAppearanceOnUser();

    $this->actingAs(user());

    expect(theme()->loadAppearance())->toBe([]);
});
