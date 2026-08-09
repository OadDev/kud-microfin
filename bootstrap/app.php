<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'app.unlocked' => \App\Http\Middleware\EnsureAppUnlocked::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\EnsureAppIsInstalled::class,
        ]);

        // Login is 3 separate role-specific screens (no shared page) --
        // without this, Laravel's default `redirectTo` always sends a
        // logged-out visitor to route('login') (Customer), even for a
        // guest hitting an Admin/Shop Owner URL.
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin*')) {
                return route('admin.login');
            }
            if ($request->is('shop-owner*')) {
                return route('shopowner.login');
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
