<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
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

    /**
     * Early full settlement -- pays off the remaining EMIs at once and
     * closes the loan. Admin/Shop Owner only (mirrors every other loan
     * action in this app being staff-initiated, not self-service).
     */
    public function foreclose(Request $request, Loan $loan): RedirectResponse
    {
        $user = $request->user();
        $allowed = $user->isAdmin() || ($user->isShopOwner() && $loan->shop_owner_id === $user->shopOwner->id);
        abort_unless($allowed, 403);
        abort_unless(in_array($loan->status, ['active', 'overdue'], true), 400, 'Only an active or overdue loan can be foreclosed.');

        $loan->foreclose();

        return back()->with('success', "Loan {$loan->loan_account_no} foreclosed successfully.");
    }
}
