<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $query = Loan::with(['customer.user', 'emis'])->whereIn('status', ['active', 'overdue']);
        if (! $isAdmin) {
            $query->where('shop_owner_id', $user->shopOwner->id);
        }

        return view('shared.loans.index', [
            'title' => 'Active Loans', 'active' => 'active-loans',
            'loans' => $query->get(),
        ]);
    }
}
