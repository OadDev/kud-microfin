@php $c = $p->loan->customer; @endphp
<x-guest-layout title="Payment Receipt">
<div style="background:#eef1f5; min-height:100vh; padding:24px;">
  <div class="mx-auto" style="max-width:760px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold brand-text mb-0">Payment Receipt</h5>
      <button class="btn btn-primary-fin btn-sm" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print / Download</button>
    </div>

    <div id="printArea" class="print-area" style="border-radius:10px;">
      @include('documents._letterhead')
      <p>To,<br><strong>{{ $c->user->name }}</strong><br>{{ $c->address }}, {{ $c->city }}, {{ $c->state }} - {{ $c->pin }}</p>
      <p><strong>Customer ID:</strong> {{ $c->customer_code }} &nbsp; | &nbsp; <strong>Loan Account Number:</strong> {{ $p->loan->loan_account_no }}</p>
      <p>This is to confirm receipt of the following payment towards your loan account.</p>
      <table class="table table-sm table-bordered mt-3" style="max-width:520px;">
        <tbody>
          <tr><td>Receipt Number</td><td class="text-end">{{ $p->reference }}</td></tr>
          <tr><td>Payment For</td><td class="text-end">{{ $p->is_foreclosure ? 'Loan Foreclosure' : 'EMI #'.$p->emi->emi_number }}</td></tr>
          <tr><td>Amount Paid</td><td class="text-end">₹{{ number_format((float) $p->paid_amount, 2) }}</td></tr>
          <tr><td>Payment Method</td><td class="text-end">{{ $p->method }}</td></tr>
          <tr><td>Transaction / UTR Reference</td><td class="text-end">{{ $p->txn_reference ?? '-' }}</td></tr>
          <tr><td>Payment Date</td><td class="text-end">{{ $p->submitted_at->format('d/m/Y') }}</td></tr>
          <tr><td>Verified On</td><td class="text-end">{{ $p->verified_at?->format('d/m/Y') ?? '-' }}</td></tr>
        </tbody>
      </table>
      <p class="mt-4" style="font-size:.8rem;color:#666;">This is a computer-generated receipt and does not require a signature. For any queries, please contact your assigned shop owner or our support helpline.</p>
    </div>

    <div class="text-center mt-3"><a href="{{ url()->previous() }}" class="small-note">Close</a></div>
  </div>
</div>
</x-guest-layout>
