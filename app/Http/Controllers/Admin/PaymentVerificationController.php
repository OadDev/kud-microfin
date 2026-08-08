<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSubmission;
use App\Services\CustomerNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentVerificationController extends Controller
{
    public function index(): View
    {
        $submissions = PaymentSubmission::with(['customer.user', 'loan', 'emi'])
            ->latest('submitted_at')->get();

        return view('admin.payment-verification.index', [
            'title' => 'Payment Verification', 'active' => 'payment-verification',
            'submissions' => $submissions,
        ]);
    }

    public function show(PaymentSubmission $paymentSubmission): View
    {
        $paymentSubmission->load(['customer.user', 'loan', 'emi']);

        return view('admin.payment-verification.show', [
            'title' => 'Payment Submission',
            'p' => $paymentSubmission,
        ]);
    }

    public function approve(Request $request, PaymentSubmission $paymentSubmission): RedirectResponse
    {
        if ($paymentSubmission->status !== 'under_verification') {
            return back()->with('warning', 'This payment has already been processed.');
        }

        $paymentSubmission->update([
            'status' => 'approved',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        if ($paymentSubmission->is_foreclosure) {
            $paymentSubmission->loan->foreclose();

            CustomerNotifier::send('foreclosure_approved', $paymentSubmission->customer, [
                'loan_account_no' => $paymentSubmission->loan->loan_account_no,
                'amount' => number_format((float) $paymentSubmission->paid_amount, 2),
            ]);

            return redirect()->route('admin.payment-verification.index')
                ->with('success', "Payment approved. Loan {$paymentSubmission->loan->loan_account_no} has been foreclosed.");
        }

        $emi = $paymentSubmission->emi;
        $emi->update(['status' => 'paid', 'payment_date' => now()]);

        $paymentSubmission->loan->refreshStatus();

        CustomerNotifier::send('payment_approved', $paymentSubmission->customer, [
            'emi_number' => $emi->emi_number,
            'amount' => number_format((float) $paymentSubmission->paid_amount, 2),
        ]);

        return redirect()->route('admin.payment-verification.index')
            ->with('success', "Payment approved. EMI #{$emi->emi_number} marked as Paid.");
    }

    public function reject(Request $request, PaymentSubmission $paymentSubmission): RedirectResponse
    {
        if ($paymentSubmission->status !== 'under_verification') {
            return back()->with('warning', 'This payment has already been processed.');
        }

        $data = $request->validate(['reason' => ['required', 'string']]);

        $paymentSubmission->update([
            'status' => 'rejected',
            'reject_reason' => $data['reason'],
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        if (! $paymentSubmission->is_foreclosure) {
            $emi = $paymentSubmission->emi;
            $emi->update(['status' => 'pending']);

            $paymentSubmission->loan->refreshStatus();

            CustomerNotifier::send('payment_rejected', $paymentSubmission->customer, [
                'emi_number' => $emi->emi_number,
                'reason' => $data['reason'],
            ]);
        }

        return redirect()->route('admin.payment-verification.index')
            ->with('success', "Payment for {$paymentSubmission->customer->user->name} was rejected. Reason: {$data['reason']}");
    }
}
