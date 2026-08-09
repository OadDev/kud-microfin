<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Re-locks the customer panel behind the Quick PIN every time the mobile
 * app is actually re-entered -- without this, a Capacitor app just keeps
 * the same logged-in web session (and, usually, the same still-rendered
 * page in memory) across closing and reopening, same as a browser tab you
 * never signed out of, so "PIN enabled" never actually got re-checked.
 *
 * The "unlocked" flag lives in the session and is cleared by two things
 * working together (see partials/app-lock-bridge.blade.php):
 *  - the app sends a beacon to clear it the moment it backgrounds, so a
 *    fully-killed-and-relaunched app is locked on its very next request;
 *  - a still-alive, merely-backgrounded WebView doesn't make any new
 *    request on its own when resumed, so the bridge script forces one by
 *    navigating to the current page, which is what lets this middleware
 *    run again and catch it.
 */
class EnsureAppUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasPinEnabled() && ! $request->session()->get('customer_app_unlocked')) {
            // Path + query only (never an absolute URL) so this can be
            // redirected back to later without any open-redirect risk.
            return redirect()->route('customer.lock.show', ['next' => $request->getRequestUri()]);
        }

        return $next($request);
    }
}
