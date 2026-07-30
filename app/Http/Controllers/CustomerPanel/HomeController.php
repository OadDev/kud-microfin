<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer()->with('loans.emis', 'loans.paymentSubmissions')->first();
        $loan = $customer->currentLoan();

        $recentPayments = $loan
            ? $loan->paymentSubmissions->sortByDesc('id')->take(4)
            : collect();

        return view('customer.home', [
            'title' => 'Home', 'active' => 'home',
            'customer' => $customer, 'loan' => $loan, 'recentPayments' => $recentPayments,
        ]);
    }
}
