<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Services\CustomerNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Customer-only "forgot password" via emailed OTP -- there is no SMS
 * gateway, so this is the one self-service recovery path available (a
 * customer with no email on file still has to contact Admin/Shop Owner, the
 * same as today). Reuses the Notification Manager's SMTP + template infra,
 * so delivery depends on Admin having configured SMTP there.
 */
class CustomerPasswordResetController extends Controller
{
    protected const OTP_TTL_MINUTES = 10;

    public function showRequest(): View
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $data['email'])->where('role', 'customer')->first();

        if ($user) {
            $code = (string) random_int(100000, 999999);

            PasswordResetOtp::create([
                'email' => $user->email,
                'code' => Hash::make($code),
                'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
            ]);

            $customer = $user->customer;
            if ($customer) {
                CustomerNotifier::send('password_reset_otp', $customer, ['otp_code' => $code]);
            }
        }

        // Same message whether or not the email matched an account, so this
        // can't be used to probe which emails are registered.
        return redirect()->route('password.reset.show', ['email' => $data['email']])
            ->with('status', 'If that email is registered to a customer account, we\'ve sent a 6-digit code. It expires in '.self::OTP_TTL_MINUTES.' minutes.');
    }

    public function showReset(Request $request): View
    {
        return view('auth.reset-password', ['email' => $request->query('email', '')]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::where('email', $data['email'])->where('role', 'customer')->first();

        $otp = PasswordResetOtp::where('email', $data['email'])
            ->whereNull('consumed_at')
            ->where('expires_at', '>=', now())
            ->latest('id')
            ->get()
            ->first(fn (PasswordResetOtp $candidate) => Hash::check($data['code'], $candidate->code));

        if (! $user || ! $otp) {
            return back()->withErrors(['code' => 'That code is invalid or has expired. Please request a new one.'])
                ->withInput(['email' => $data['email']]);
        }

        $otp->forceFill(['consumed_at' => now()])->save();
        $user->forceFill(['password' => Hash::make($data['password'])])->save();

        return redirect()->route('login')->with('status', 'Your password has been reset. Please log in.');
    }
}
