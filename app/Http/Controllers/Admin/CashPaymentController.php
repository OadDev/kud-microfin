<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emi;
use App\Models\PaymentSubmission;
use App\Services\CustomerNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Lets Admin record an EMI as paid in cash directly -- no screenshot/UPI
 * reference to upload or verify, since the money already changed hands in
 * person. Still creates a PaymentSubmission (method "Cash", auto-approved)
 * so cash payments show up in the same payment history as digital ones,
 * rather than being an invisible side-channel update to the EMI row alone.
 *
 * Supports partial cash payments: an EMI can be paid off across more than
 * one visit (e.g. ₹1,500 now, the remaining ₹500 later) -- amount_paid
 * accumulates on the Emi and it only flips to 'paid' once that reaches the
 * full amount. Until then it stays exactly as it would have been anyway
 * (still 'pending', still shows Upcoming/Due Today/Overdue by due date),
 * so a partially-paid-but-overdue EMI correctly keeps showing as overdue.
 */
class CashPaymentController extends Controller
{
    public function store(Request $request, Emi $emi): RedirectResponse
    {
        abort_if($emi->status === 'paid', 400, 'This EMI is already marked as paid.');

        $remaining = $emi->remainingAmount();

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$remaining],
        ]);
        $amountPaidNow = (float) $data['amount'];

        $loan = $emi->loan;
        $customer = $loan->customer;

        // An existing digital submission for this EMI (customer uploaded a
        // screenshot that's still awaiting review) is superseded, not left
        // dangling under verification once cash changes hands for it instead.
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
            'paid_amount' => $amountPaidNow,
            'method' => 'Cash',
            'txn_reference' => null,
            'screenshot_path' => null,
            'remarks' => 'Marked as paid in cash by admin.',
            'status' => 'approved',
            'submitted_at' => now(),
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $newAmountPaid = round((float) $emi->amount_paid + $amountPaidNow, 2);
        $isFullyPaid = $newAmountPaid >= (float) $emi->amount;

        $emi->update($isFullyPaid
            ? ['status' => 'paid', 'amount_paid' => $emi->amount, 'payment_date' => now()]
            : ['amount_paid' => $newAmountPaid]);

        $loan->refreshStatus();

        CustomerNotifier::send('payment_approved', $customer, [
            'emi_number' => $emi->emi_number,
            'amount' => number_format($amountPaidNow, 2),
        ]);

        if ($isFullyPaid) {
            return back()->with('success', "EMI #{$emi->emi_number} marked as fully paid (cash).");
        }

        $stillDue = number_format($emi->remainingAmount(), 2);

        return back()->with('success', "₹".number_format($amountPaidNow, 2)." recorded for EMI #{$emi->emi_number}. ₹{$stillDue} still due.");
    }
}
