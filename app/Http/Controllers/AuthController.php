<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Login is split into three separate, role-specific screens/URLs -- there
 * is no shared login page. This is deliberate: staff (Admin/Shop Owner)
 * and Customers must never be shown each other's login options, and the
 * customer-facing mobile app's start URL (/login) must never expose a
 * path into the staff panel.
 */
class AuthController extends Controller
{
    public function showAdminLogin(): View
    {
        return view('auth.admin-login');
    }

    public function loginAdmin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('mobile', $data['identifier'])->orWhere('email', $data['identifier'])->first();

        if (! $user || $user->role !== 'admin' || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['identifier' => 'Invalid credentials.'])->onlyInput('identifier');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended($this->homeFor($user));
    }

    public function showShopOwnerLogin(): View
    {
        return view('auth.shop-owner-login');
    }

    public function loginShopOwner(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('mobile', $data['identifier'])->orWhere('email', $data['identifier'])->first();

        if (! $user || $user->role !== 'shop_owner' || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['identifier' => 'Invalid credentials.'])->onlyInput('identifier');
        }

        if ($user->status !== 'approved') {
            return back()->withErrors([
                'identifier' => match ($user->status) {
                    'pending' => 'Your shop owner registration is still pending Admin approval.',
                    'rejected' => 'Your shop owner registration was rejected. Please contact Admin.',
                    'suspended' => 'Your shop owner account has been suspended. Please contact Admin.',
                    default => 'Your account cannot log in right now.',
                },
            ])->onlyInput('identifier');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended($this->homeFor($user));
    }

    /**
     * Customer login -- also the Android/iOS app's start URL. Password
     * based (no SMS gateway is wired up, so OTP would mean showing the
     * code on-screen instead of texting it -- a real account-takeover
     * risk we're not shipping). Offers the Quick PIN / biometric shortcut
     * for a returning, already-identified device.
     */
    public function showCustomerLogin(Request $request): View
    {
        $quickLoginUser = (new QuickLoginController)->identifiedUser($request);

        return view('auth.customer-login', [
            'quickLoginUser' => $quickLoginUser && $quickLoginUser->hasPinEnabled() ? $quickLoginUser : null,
        ]);
    }

    public function loginCustomer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('mobile', $data['identifier'])->orWhere('email', $data['identifier'])->first();

        if (! $user || $user->role !== 'customer' || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['identifier' => 'Invalid mobile number/email or password.'])->onlyInput('identifier');
        }

        Auth::login($user);
        $request->session()->regenerate();
        // Just proved identity with the password -- the app-lock PIN
        // screen (EnsureAppUnlocked) would otherwise immediately challenge
        // this same session again.
        $request->session()->put('customer_app_unlocked', true);

        if (! $user->hasPinEnabled() && ! $request->session()->has('url.intended')) {
            return redirect()->route('quick-login.setup');
        }

        return redirect()->intended($this->homeFor($user));
    }

    /**
     * Quick demo-login shortcuts for local dev/testing only -- no UI button
     * renders anywhere; hit the route directly (e.g. via curl) if needed.
     * Only ever logs into seeded demo accounts, never creates or elevates one.
     */
    public function demoLogin(Request $request, string $role): RedirectResponse
    {
        abort_unless(app()->environment('local'), 404);
        abort_unless(in_array($role, ['admin', 'shop_owner', 'customer'], true), 404);

        $user = User::where('role', $role)->where('status', 'approved')->oldest('id')->first();
        abort_unless($user, 404, 'No demo account seeded for this role.');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->to($this->homeFor($user));
    }

    public function logout(Request $request): RedirectResponse
    {
        $role = $request->user()?->role;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route(match ($role) {
            'admin' => 'admin.login',
            'shop_owner' => 'shopowner.login',
            default => 'login',
        });
    }

    public function homeFor(User $user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'shop_owner' => route('shopowner.dashboard'),
            'customer' => route('customer.home'),
        };
    }
}
