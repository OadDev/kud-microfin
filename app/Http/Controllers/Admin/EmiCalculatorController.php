<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Services\EmiQuoteService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmiCalculatorController extends Controller
{
    public function index(Request $request): View
    {
        $quote = null;
        $settings = PaymentSetting::current();
        $defaultFee = (string) $settings->product_emi_processing_fee;
        $rate = (float) $settings->product_emi_interest_rate;

        $devicePrice = (float) $request->query('device_price', 0);
        $downPayment = (float) $request->query('down_payment', 0);
        $processingFee = (float) $request->query('processing_fee', $defaultFee);
        $numInstallments = (int) $request->query('installments', 0);

        if ($devicePrice > 0 && $numInstallments > 0) {
            $quote = EmiQuoteService::quote($devicePrice, $downPayment, $processingFee, $numInstallments, $rate);
        }

        return view('admin.emi-calculator', [
            'title' => 'EMI Calculator', 'active' => 'emi-calculator',
            'quote' => $quote,
            'input' => [
                'device_price' => $request->query('device_price', ''),
                'down_payment' => $request->query('down_payment', ''),
                'processing_fee' => $request->query('processing_fee', $defaultFee),
                'installments' => $request->query('installments', ''),
            ],
        ]);
    }
}
