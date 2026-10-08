<?php

declare(strict_types=1);

namespace HoceineEl\Monolith\Http\Controllers;

use HoceineEl\Monolith\MonolithTheme;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class SaveAppearanceController
{
    public function __invoke(Request $request): Response
    {
        $theme = MonolithTheme::get();
        $user = $request->user();

        abort_unless($theme->isAppearanceStoredOnServer(), 404);
        abort_unless($user !== null, 401);

        $validated = $request->validate([
            'appearance' => ['present', 'array'],
            'appearance.*' => ['string', 'max:100'],
        ]);

        $theme->saveAppearance(Arr::only($validated['appearance'], $theme->getCustomizableKeys()), $user);

        return response()->noContent();
    }
}
