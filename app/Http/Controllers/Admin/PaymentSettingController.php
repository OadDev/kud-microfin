<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.payment-settings.edit', [
            'title' => 'Payment Settings', 'active' => 'payment-settings',
            'settings' => PaymentSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'upi_id' => ['nullable', 'string', 'max:255'],
            'upi_holder' => ['nullable', 'string', 'max:255'],
            'qr' => ['nullable', 'image', 'max:4096'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_holder' => ['nullable', 'string', 'max:255'],
            'account_no' => ['nullable', 'string', 'max:64'],
            'ifsc' => ['nullable', 'string', 'max:20'],
            'branch' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'razorpay_key_id' => ['nullable', 'string', 'max:255'],
            'razorpay_key_secret' => ['nullable', 'string', 'max:255'],
            'foreclosure_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'product_emi_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $settings = PaymentSetting::current();

        if ($request->hasFile('qr')) {
            $data['qr_path'] = $request->file('qr')->store('payment-settings', 'public');
        }
        unset($data['qr']);

        $settings->update($data);

        return back()->with('success', 'Payment settings saved successfully.');
    }
}
