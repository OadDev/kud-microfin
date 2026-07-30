@php $outstanding = $loan ? $loan->outstanding() : 0; @endphp
<x-customer-layout :title="$title" :active="$active">

@if($loan)
<div class="card-flat p-3 mb-3">
  <div class="section-title mb-2">Loan Details</div>
  <div class="dc-row"><span class="text-muted-fin">Loan Account No.</span><span class="fw-semibold">{{ $loan->loan_account_no }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Loan Purpose</span><span>{{ $loan->purpose }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Principal Amount</span><span>₹{{ number_format($loan->principal) }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Total Interest</span><span>₹{{ number_format($loan->interest) }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Processing Fee</span><span>₹{{ number_format($loan->processing_fee) }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Total Payable</span><span class="fw-semibold">₹{{ number_format($loan->total_payable) }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">EMI Amount</span><span>₹{{ number_format($loan->emi_amount) }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Number of EMIs</span><span>{{ $loan->num_emis }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">EMI Frequency</span><span>{{ $loan->frequency }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Loan Start Date</span><span>{{ $loan->start_date->format('d/m/Y') }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Outstanding Balance</span><span class="fw-semibold text-danger">₹{{ number_format($outstanding) }}</span></div>
</div>
<div class="card-flat p-3 mb-3">
  <div class="section-title mb-2">EMI Schedule</div>
  @foreach($loan->emis as $e)
    <div class="data-card">
      <div class="dc-head"><div class="fw-bold">EMI #{{ $e->emi_number }}</div><x-status-badge :status="$e->displayStatus()" /></div>
      <div class="dc-row"><span class="dc-label">Due Date</span><span>{{ $e->due_date->format('d/m/Y') }}</span></div>
      <div class="dc-row"><span class="dc-label">Amount</span><span>₹{{ number_format($e->amount) }}</span></div>
      <div class="dc-row"><span class="dc-label">Payment Date</span><span>{{ $e->payment_date?->format('d/m/Y') ?? '-' }}</span></div>
    </div>
  @endforeach
</div>
@else
<div class="card-flat p-4 text-center"><div class="small-note">No loan found on your account.</div></div>
@endif
</x-customer-layout>
