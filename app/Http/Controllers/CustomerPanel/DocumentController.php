<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
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
}
