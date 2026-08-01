<?php
$c = $loan->customer;
$nocNo = 'BPF/NOC/'.now()->year.'/'.str_pad($loan->id, 6, '0', STR_PAD_LEFT);
$closedOn = $loan->status === 'foreclosed' ? $loan->foreclosed_at : $loan->updated_at;
?>
<x-guest-layout title="No Objection Certificate">
<div style="background:#eef1f5; min-height:100vh; padding:24px;">
  <div class="mx-auto" style="max-width:760px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold brand-text mb-0">No Objection Certificate</h5>
      <button class="btn btn-primary-fin btn-sm" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print</button>
    </div>

    <div id="printArea" class="print-area" style="border-radius:10px;">
      @include('documents._letterhead')
      <h5 class="text-center fw-bold mt-2" style="color:#0c2053;">NO OBJECTION CERTIFICATE</h5>
      <p class="text-center small-note" style="font-size:.8rem;">NOC No: {{ $nocNo }}</p>

      <p>This is to certify that <strong>{{ $c->user->name }}</strong> (Customer ID: <strong>{{ $c->customer_code }}</strong>), residing at {{ $c->address }}, {{ $c->city }}, {{ $c->state }} - {{ $c->pin }}, had availed a loan bearing Loan Account Number <strong>{{ $loan->loan_account_no }}</strong> for the purpose of {{ $loan->purpose }}.</p>

      <table class="table table-sm table-bordered mt-2" style="max-width:560px;">
        <tbody>
          <tr><td>Loan Amount (Principal)</td><td class="text-end">₹{{ number_format($loan->principal) }}</td></tr>
          <tr><td>Total Payable</td><td class="text-end">₹{{ number_format($loan->total_payable) }}</td></tr>
          <tr><td>Total Amount Paid</td><td class="text-end">₹{{ number_format($loan->amountPaid()) }}</td></tr>
          <tr><td>Loan Closure Type</td><td class="text-end">{{ $loan->status === 'foreclosed' ? 'Foreclosed (early settlement)' : 'Closed (regular EMI completion)' }}</td></tr>
          <tr><td>Closure Date</td><td class="text-end">{{ $closedOn->format('d/m/Y') }}</td></tr>
        </tbody>
      </table>

      <p>We hereby confirm that the above-mentioned loan account has been <strong>fully repaid</strong> and there is <strong>no outstanding balance</strong> due from the customer as on the date of this certificate. BluePeak Fintech Private Limited has <strong>no objection</strong> to the closure of this loan account and confirms that all dues have been settled in full.</p>

      <p style="font-size:.78rem; color:#444;">This certificate is issued for the customer's records and may be used as proof of loan closure. It does not constitute a No Dues Certificate for any other loan or facility the customer may hold with BluePeak Fintech or any other institution.</p>

      <div class="row mt-4">
        <div class="col-6"></div>
        <div class="col-6"><div class="sign-box">Authorised Signatory<br><span style="font-size:.75rem;">BluePeak Fintech Private Limited</span></div></div>
      </div>
    </div>

    <div class="text-center mt-3"><a href="{{ url()->previous() }}" class="small-note">Close</a></div>
  </div>
</div>
</x-guest-layout>
