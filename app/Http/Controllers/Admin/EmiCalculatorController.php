<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EmiQuoteService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmiCalculatorController extends Controller
{
    public function index(Request $request): View
    {
        $quote = null;

        $devicePrice = (float) $request->query('device_price', 0);
        $downPayment = (float) $request->query('down_payment', 0);
        $processingFee = (float) $request->query('processing_fee', 0);
        $numInstallments = (int) $request->query('installments', 0);

        if ($devicePrice > 0 && $numInstallments > 0) {
            $quote = EmiQuoteService::quote($devicePrice, $downPayment, $processingFee, $numInstallments);
        }

        return view('admin.emi-calculator', [
            'title' => 'EMI Calculator', 'active' => 'emi-calculator',
            'quote' => $quote,
            'input' => [
                'device_price' => $request->query('device_price', ''),
                'down_payment' => $request->query('down_payment', ''),
                'processing_fee' => $request->query('processing_fee', ''),
                'installments' => $request->query('installments', ''),
            ],
        ]);
    }
}
