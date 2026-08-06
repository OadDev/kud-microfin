<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use App\Models\PaymentSubmission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer()->with('loans.emis')->first();
        $loan = $customer->currentLoan();

        $pendingForeclosure = $loan
            ? PaymentSubmission::where('loan_id', $loan->id)
                ->where('is_foreclosure', true)
                ->where('status', 'under_verification')
                ->latest('submitted_at')->first()
            : null;

        return view('customer.loan', [
            'title' => 'Loan', 'active' => 'loan',
            'customer' => $customer, 'loan' => $loan,
            'pendingForeclosure' => $pendingForeclosure,
        ]);
    }
}
