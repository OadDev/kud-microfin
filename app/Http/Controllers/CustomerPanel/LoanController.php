<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer()->with('loans.emis')->first();
        $loan = $customer->currentLoan();

        return view('customer.loan', [
            'title' => 'Loan', 'active' => 'loan',
            'customer' => $customer, 'loan' => $loan,
        ]);
    }
}
