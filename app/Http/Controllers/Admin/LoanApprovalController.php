<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Services\CustomerNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanApprovalController extends Controller
{
    public function index(): View
    {
        return view('admin.loan-approvals.index', [
            'title' => 'Loan Approvals', 'active' => 'loan-approvals',
            'loans' => Loan::with('customer.user', 'shopOwner.user')
                ->where('status', 'pending')->latest('id')->get(),
        ]);
    }

    public function show(Loan $loan): View
    {
        $loan->load('customer.user', 'shopOwner.user', 'emis');

        return view('admin.loan-approvals.show', [
            'title' => 'Review Loan '.$loan->loan_account_no, 'active' => 'loan-approvals',
            'loan' => $loan,
        ]);
    }

    public function approve(Request $request, Loan $loan): RedirectResponse
    {
        abort_unless($loan->status === 'pending', 400, 'This loan has already been decided.');

        $loan->update([
            'status' => 'active',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);
        $loan->refreshStatus();

        CustomerNotifier::send('loan_approved', $loan->customer, [
            'loan_account_no' => $loan->loan_account_no,
            'emi_amount' => number_format((float) $loan->emi_amount, 2),
            'num_emis' => $loan->num_emis,
        ]);

        return redirect()->route('admin.loan-approvals.index')
            ->with('success', "Loan {$loan->loan_account_no} approved and is now active.");
    }

    public function reject(Request $request, Loan $loan): RedirectResponse
    {
        abort_unless($loan->status === 'pending', 400, 'This loan has already been decided.');

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $loan->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'reject_reason' => $data['reason'],
        ]);

        CustomerNotifier::send('loan_rejected', $loan->customer, [
            'loan_account_no' => $loan->loan_account_no,
            'reason' => $data['reason'],
        ]);

        return redirect()->route('admin.loan-approvals.index')
            ->with('success', "Loan {$loan->loan_account_no} rejected.");
    }
}
