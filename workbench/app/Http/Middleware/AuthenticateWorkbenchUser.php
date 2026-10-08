<?php

declare(strict_types=1);

namespace Workbench\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Workbench\App\Models\User;

class AuthenticateWorkbenchUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guest() && ($user = User::query()->first())) {
            Auth::login($user);
        }

        return $next($request);
    }
}
