<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        $quickLoginUser = (new QuickLoginController)->identifiedUser($request);

        return view('auth.login', [
            'quickLoginUser' => $quickLoginUser && $quickLoginUser->hasPinEnabled() ? $quickLoginUser : null,
        ]);
    }

    /**
     * Password login shared by Admin and Shop Owner. Accepts a mobile
     * number or an email address in the same field, matching the UI.
     */
    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('mobile', $data['identifier'])
            ->orWhere('email', $data['identifier'])
            ->first();

        if (! $user || ! in_array($user->role, ['admin', 'shop_owner'], true) || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['identifier' => 'Invalid credentials.'])->onlyInput('identifier');
        }

        if ($user->role === 'shop_owner' && $user->status !== 'approved') {
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

        if (! $user->hasPinEnabled() && ! $request->session()->has('url.intended')) {
            return redirect()->route('quick-login.setup');
        }

        return redirect()->intended($this->homeFor($user));
    }

    /**
     * Quick demo-login buttons, mirroring the prototype's "Login as Admin /
     * Shop Owner / Customer" shortcuts. Only ever logs into seeded demo
     * accounts — never creates or elevates a user.
     */
    public function demoLogin(Request $request, string $role): RedirectResponse
    {
        abort_unless(in_array($role, ['admin', 'shop_owner', 'customer'], true), 404);

        $user = User::where('role', $role)->where('status', 'approved')->oldest('id')->first();
        abort_unless($user, 404, 'No demo account seeded for this role.');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->to($this->homeFor($user));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function homeFor(User $user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'shop_owner' => route('shopowner.customers.index'),
            'customer' => route('customer.home'),
        };
    }
}
