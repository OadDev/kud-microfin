<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use App\Models\PaymentSubmission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer()->with('loans')->first();
        $loan = $customer->currentLoan();

        return view('customer.documents', [
            'title' => 'Documents', 'active' => 'documents',
            'loan' => $loan,
        ]);
    }

    public function receipts(Request $request): View
    {
        $customer = $request->user()->customer;

        $payments = PaymentSubmission::where('customer_id', $customer->id)
            ->where('status', 'approved')
            ->with('emi')
            ->latest('verified_at')
            ->get();

        return view('customer.receipts', [
            'title' => 'Payment Receipts', 'active' => 'documents',
            'payments' => $payments,
        ]);
    }
}
