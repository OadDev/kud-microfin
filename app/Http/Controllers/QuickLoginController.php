<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

/**
 * Quick PIN login: after a full login, a user can opt in to a 4-6 digit PIN
 * tied to that one device (identified by a long-lived signed cookie), so
 * they don't have to re-enter mobile+password/OTP every time on it.
 */
class QuickLoginController extends Controller
{
    const COOKIE = 'qlt';

    public function setupPrompt(Request $request): View
    {
        return view('auth.pin-setup', ['user' => $request->user()]);
    }

    public function storePin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'digits_between:4,6', 'confirmed'],
        ]);

        $user = $request->user();
        $user->setPin($data['pin']);
        $token = $user->issueQuickLoginToken();

        Cookie::queue(Cookie::make(self::COOKIE, $token, 60 * 24 * 365, httpOnly: true, sameSite: 'lax'));

        return redirect()->to((new AuthController)->homeFor($user))
            ->with('success', 'Quick PIN login is set up on this device.');
    }

    public function skip(Request $request): RedirectResponse
    {
        return redirect()->to((new AuthController)->homeFor($request->user()));
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->user()->revokeQuickLogin();
        Cookie::queue(Cookie::forget(self::COOKIE));

        return back()->with('success', 'Quick login has been disabled on this device.');
    }

    public function verifyPin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'pin' => ['required', 'string'],
        ]);

        $user = $this->identifiedUser($request);

        if (! $user || $user->id !== (int) $data['user_id'] || ! $user->verifyPin($data['pin'])) {
            return back()->withErrors(['pin' => 'Incorrect PIN.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->to((new AuthController)->homeFor($user));
    }

    /**
     * "Not you?" — drop this device's quick-login link so the full login
     * form shows again.
     */
    public function forget(): RedirectResponse
    {
        Cookie::queue(Cookie::forget(self::COOKIE));

        return redirect()->route('login');
    }

    /**
     * Resolves which user (if any) this device's quick-login cookie
     * identifies. Used by the login screen to decide whether to show the
     * PIN-entry shortcut instead of the full form.
     */
    public function identifiedUser(Request $request): ?User
    {
        $token = $request->cookie(self::COOKIE);
        if (! $token) {
            return null;
        }

        return User::where('quick_login_token_hash', hash('sha256', $token))->first();
    }
}
