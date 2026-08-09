<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emi;
use App\Models\PaymentSubmission;
use App\Services\CustomerNotifier;
use Illuminate\Http\RedirectResponse;

/**
 * Lets Admin record an EMI as paid in cash directly -- no screenshot/UPI
 * reference to upload or verify, since the money already changed hands in
 * person. Still creates a PaymentSubmission (method "Cash", auto-approved)
 * so cash payments show up in the same payment history as digital ones,
 * rather than being an invisible side-channel update to the EMI row alone.
 */
class CashPaymentController extends Controller
{
    public function store(Emi $emi): RedirectResponse
    {
        abort_if($emi->status === 'paid', 400, 'This EMI is already marked as paid.');

        $loan = $emi->loan;
        $customer = $loan->customer;

        // An existing digital submission for this EMI (customer uploaded a
        // screenshot that's still awaiting review) is superseded, not left
        // dangling under verification once the EMI is marked paid another way.
        PaymentSubmission::where('emi_id', $emi->id)
            ->where('status', 'under_verification')
            ->update([
                'status' => 'rejected',
                'reject_reason' => 'Superseded: EMI marked as paid in cash by admin.',
                'verified_at' => now(),
            ]);

        PaymentSubmission::create([
            'reference' => 'CASH'.random_int(100000, 999999),
            'loan_id' => $loan->id,
            'emi_id' => $emi->id,
            'customer_id' => $customer->id,
            'paid_amount' => $emi->amount,
            'method' => 'Cash',
            'txn_reference' => null,
            'screenshot_path' => null,
            'remarks' => 'Marked as paid in cash by admin.',
            'status' => 'approved',
            'submitted_at' => now(),
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $emi->update(['status' => 'paid', 'payment_date' => now()]);
        $loan->refreshStatus();

        CustomerNotifier::send('payment_approved', $customer, [
            'emi_number' => $emi->emi_number,
            'amount' => number_format((float) $emi->amount, 2),
        ]);

        return back()->with('success', "EMI #{$emi->emi_number} marked as paid (cash).");
    }
}
