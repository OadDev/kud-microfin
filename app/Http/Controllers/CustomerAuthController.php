<?php

namespace App\Http\Controllers;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAuthController extends Controller
{
    /**
     * Generate an OTP for a registered customer mobile number.
     *
     * Real SMS delivery is intentionally not wired up yet — the code is
     * flashed back to the login page so the demo/UAT flow works without an
     * SMS provider. Swapping in a real gateway later only means replacing
     * the flash-to-session call below with an actual send.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mobile' => ['required', 'digits:10'],
        ]);

        $user = User::where('mobile', $data['mobile'])->where('role', 'customer')->first();

        if (! $user) {
            return back()->withErrors(['mobile' => 'No customer account found with this mobile number.'])->onlyInput('mobile');
        }

        $otp = OtpCode::generateFor($data['mobile']);

        return back()
            ->with('otp_mobile', $data['mobile'])
            ->with('otp_demo_code', $otp->code);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mobile' => ['required', 'digits:10'],
            'otp' => ['required', 'digits:6'],
        ]);

        if (! OtpCode::verify($data['mobile'], $data['otp'])) {
            return back()->withErrors(['otp' => 'Incorrect or expired OTP.'])->with('otp_mobile', $data['mobile']);
        }

        $user = User::where('mobile', $data['mobile'])->where('role', 'customer')->first();

        if (! $user) {
            return back()->withErrors(['mobile' => 'No customer account found with this mobile number.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.home');
    }
}
