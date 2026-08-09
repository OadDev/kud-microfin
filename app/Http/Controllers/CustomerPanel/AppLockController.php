<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * The "reopen the app -> re-enter your PIN" lock screen. See
 * EnsureAppUnlocked for how/why this gets triggered.
 */
class AppLockController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPinEnabled()) {
            return redirect()->to($this->safeNext($request));
        }

        // Landing on this screen always means "not unlocked" -- defends
        // against a stale/racy session flag independently of whatever
        // cleared (or failed to clear) it on the way in.
        $request->session()->put('customer_app_unlocked', false);

        return view('customer.app-lock', [
            'user' => $user,
            'next' => $this->safeNext($request),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'string'],
            'next' => ['nullable', 'string'],
        ]);

        if (! $request->user()->verifyPin($data['pin'])) {
            return back()->withErrors(['pin' => 'Incorrect PIN.'])->withInput(['next' => $data['next'] ?? null]);
        }

        $request->session()->put('customer_app_unlocked', true);

        return redirect()->to($this->safeNext($request, $data['next'] ?? null));
    }

    /**
     * Beacon endpoint: the app-lock bridge script calls this the moment
     * the app backgrounds, so a fully-killed-and-relaunched app is locked
     * on its very next request (a merely-backgrounded, still-alive WebView
     * is handled client-side instead -- see the bridge script).
     */
    public function lockNow(Request $request): \Illuminate\Http\Response
    {
        $request->session()->put('customer_app_unlocked', false);

        return response()->noContent();
    }

    private function safeNext(Request $request, ?string $next = null): string
    {
        $next ??= $request->query('next');

        // Only ever a same-app relative path -- never follow an absolute
        // URL here, since "next" round-trips through user-facing requests.
        if ($next && Str::startsWith($next, '/') && ! Str::startsWith($next, '//')) {
            return $next;
        }

        return route('customer.home');
    }
}
