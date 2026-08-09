<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Native biometric login (Android BiometricPrompt / iOS LocalAuthentication
 * via a Capacitor plugin) -- for inside the app only. This replaces relying
 * on WebAuthn passkeys there, since Android's embedded WebView doesn't
 * support the passkey ceremony reliably across devices, unlike a real
 * browser tab (see mobile/README.md). Passkeys remain exactly as they are
 * for browser use.
 *
 * The device only ever holds a random opaque token (never the password),
 * stored by the native plugin behind a real OS biometric prompt -- by the
 * time this controller sees the token at all, the biometric check has
 * already happened locally.
 */
class BiometricLoginController extends Controller
{
    public function enable(Request $request): JsonResponse
    {
        abort_unless($request->user()->isCustomer(), 403);

        $token = $request->user()->issueBiometricToken();

        return response()->json(['token' => $token, 'user_id' => $request->user()->id]);
    }

    /**
     * Also reachable while already logged in (the app-lock screen calls
     * this too, see EnsureAppUnlocked) -- Auth::login() on an
     * already-authenticated session is a harmless no-op re-confirmation.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'token' => ['required', 'string'],
        ]);

        $user = User::find($data['user_id']);

        if (! $user || ! $user->isCustomer() || ! $user->verifyBiometricToken($data['token'])) {
            return response()->json(['message' => 'Biometric login failed. Please use your password.'], 422);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('customer_app_unlocked', true);

        return response()->json(['redirect' => (new AuthController)->homeFor($user)]);
    }

    public function disable(Request $request): JsonResponse
    {
        abort_unless($request->user()->isCustomer(), 403);

        $request->user()->revokeBiometric();

        return response()->json(['success' => true]);
    }
}
