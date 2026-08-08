<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends visitors to the /install wizard until the app has a database
 * configured (installed.lock present). Lets shared hosting deploys that
 * ship without a pre-filled .env still boot straight into a usable setup
 * flow instead of a raw "connection refused" error page.
 */
class EnsureAppIsInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        // Local/testing environments configure their database by hand
        // (.env + `php artisan migrate`) and never go through the wizard.
        if ($request->is('up') || app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        $installed = file_exists(storage_path('app/installed.lock'));

        if (! $installed && ! $request->routeIs('install.*')) {
            return redirect()->route('install.show');
        }

        if ($installed && $request->routeIs('install.*')) {
            return redirect()->route('marketing.home');
        }

        return $next($request);
    }
}
