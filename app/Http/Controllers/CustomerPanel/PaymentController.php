<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Models\PaymentSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(Request $request): View
    {
        $customer = $request->user()->customer()->with('loans.emis')->first();
        $loan = $customer->currentLoan();
        // Only offer payment for an EMI that hasn't already been submitted —
        // one under verification must be resolved by Admin before another
        // submission is allowed for it.
        $emi = $loan?->emis->first(fn ($e) => $e->status === 'pending');
        $pendingVerificationEmi = $loan?->emis->first(fn ($e) => $e->status === 'under_verification');

        return view('customer.pay', [
            'title' => 'Pay EMI', 'active' => 'pay',
            'customer' => $customer, 'loan' => $loan, 'emi' => $emi,
            'pendingVerificationEmi' => $pendingVerificationEmi,
            'settings' => PaymentSetting::current(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = $request->user()->customer;
        $loan = $customer->currentLoan();

        $data = $request->validate([
            'emi_id' => ['required', 'exists:emis,id'],
            'paid_amount' => ['required', 'numeric', 'min:1'],
            'payment_date' => ['required', 'date'],
            'method' => ['required', 'in:UPI,Bank Transfer'],
            'txn_reference' => ['required', 'string', 'max:255'],
            'screenshot' => ['required', 'image', 'max:4096'],
            'remarks' => ['nullable', 'string'],
        ]);

        $emi = $loan->emis()->findOrFail($data['emi_id']);
        abort_unless($emi->status === 'pending', 400, 'This EMI is not open for payment right now.');

        $screenshotPath = $request->file('screenshot')->store("payments/{$loan->id}", 'public');

        $submission = PaymentSubmission::create([
            'reference' => 'PAY'.random_int(100000, 999999),
            'loan_id' => $loan->id,
            'emi_id' => $emi->id,
            'customer_id' => $customer->id,
            'paid_amount' => $data['paid_amount'],
            'method' => $data['method'],
            'txn_reference' => $data['txn_reference'],
            'screenshot_path' => $screenshotPath,
            'remarks' => $data['remarks'] ?? null,
            'status' => 'under_verification',
            'submitted_at' => $data['payment_date'],
        ]);

        $emi->update(['status' => 'under_verification']);

        return redirect()->route('customer.pay')
            ->with('success', 'Your payment has been submitted successfully and is under verification.')
            ->with('submission_ref', $submission->reference);
    }

    public function foreclose(Request $request): View
    {
        $customer = $request->user()->customer()->with('loans.emis')->first();
        $loan = $customer->currentLoan();

        abort_unless($loan && in_array($loan->status, ['active', 'overdue'], true), 404);

        $pendingSubmission = PaymentSubmission::where('loan_id', $loan->id)
            ->where('is_foreclosure', true)
            ->where('status', 'under_verification')
            ->latest('submitted_at')->first();

        return view('customer.foreclose', [
            'title' => 'Foreclose Loan', 'active' => 'loan',
            'customer' => $customer, 'loan' => $loan,
            'breakdown' => $loan->foreclosureBreakdown(),
            'pendingSubmission' => $pendingSubmission,
            'settings' => PaymentSetting::current(),
        ]);
    }

    public function forecloseStore(Request $request): RedirectResponse
    {
        $customer = $request->user()->customer;
        $loan = $customer->currentLoan();

        abort_unless($loan && in_array($loan->status, ['active', 'overdue'], true), 400);

        $alreadyPending = PaymentSubmission::where('loan_id', $loan->id)
            ->where('is_foreclosure', true)
            ->where('status', 'under_verification')
            ->exists();
        abort_if($alreadyPending, 400, 'A foreclosure request for this loan is already under verification.');

        $data = $request->validate([
            'payment_date' => ['required', 'date'],
            'method' => ['required', 'in:UPI,Bank Transfer'],
            'txn_reference' => ['required', 'string', 'max:255'],
            'screenshot' => ['required', 'image', 'max:4096'],
            'remarks' => ['nullable', 'string'],
        ]);

        $total = $loan->foreclosureQuote();
        $screenshotPath = $request->file('screenshot')->store("payments/{$loan->id}", 'public');

        $submission = PaymentSubmission::create([
            'reference' => 'FCL'.random_int(100000, 999999),
            'loan_id' => $loan->id,
            'emi_id' => null,
            'is_foreclosure' => true,
            'customer_id' => $customer->id,
            'paid_amount' => $total,
            'method' => $data['method'],
            'txn_reference' => $data['txn_reference'],
            'screenshot_path' => $screenshotPath,
            'remarks' => $data['remarks'] ?? null,
            'status' => 'under_verification',
            'submitted_at' => $data['payment_date'],
        ]);

        return redirect()->route('customer.foreclose')
            ->with('success', 'Your foreclosure payment has been submitted and is under verification. Your loan will be closed once Admin approves it.')
            ->with('submission_ref', $submission->reference);
    }
}
