<?php

namespace App\Http\Middleware;

use App\Services\AuthGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePassword
{
    public function __construct(private readonly AuthGate $gate)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName() ?? '';
        $isAuthRoute = str_starts_with($routeName, 'auth.');
        $isHealthCheck = $request->is('up');

        if ($isHealthCheck) {
            return $next($request);
        }

        if (!$this->gate->hasPassword()) {
            if ($routeName === 'auth.setup' || $routeName === 'auth.setup.store') {
                return $next($request);
            }
            return redirect()->route('auth.setup');
        }

        if ($request->session()->get(AuthGate::SESSION_FLAG) === true) {
            if ($routeName === 'auth.setup' || $routeName === 'auth.setup.store') {
                return redirect()->route('home');
            }
            return $next($request);
        }

        if ($isAuthRoute && $routeName !== 'auth.setup' && $routeName !== 'auth.setup.store') {
            return $next($request);
        }

        if ($request->isMethod('GET') && !$request->ajax() && !$request->expectsJson()) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return redirect()->route('auth.login');
    }
}
