@php $outstanding = $loan ? $loan->outstanding() : 0; @endphp
<x-customer-layout :title="$title" :active="$active" pageTitle="My Loan">

@if($loan?->status === 'pending')
<div class="card-flat p-4 text-center mb-3">
  <i class="fa-solid fa-hourglass-half text-warning" style="font-size:2.4rem;"></i>
  <h6 class="fw-bold mt-3">Loan Application Under Review</h6>
  <div class="small-note">Your loan is being reviewed by our team. You'll be notified once it's approved.</div>
</div>
@elseif($loan?->status === 'rejected')
<div class="card-flat p-4 text-center mb-3">
  <i class="fa-solid fa-circle-xmark text-danger" style="font-size:2.4rem;"></i>
  <h6 class="fw-bold mt-3">Loan Application Rejected</h6>
  @if($loan->reject_reason)<div class="small-note">Reason: {{ $loan->reject_reason }}</div>@endif
  <div class="small-note mt-1">Contact your Shop Owner or our Helpline for more details.</div>
</div>
@endif

@if($loan && !in_array($loan->status, ['pending', 'rejected']))
@if($loan->status === 'foreclosed')
  <div class="alert alert-info mb-3" style="font-size:.85rem;"><i class="fa-solid fa-circle-check me-1"></i>This loan was foreclosed on {{ $loan->foreclosed_at->format('d/m/Y') }} — fully settled.</div>
@endif
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
@elseif(! $loan)
<div class="card-flat p-4 text-center"><div class="small-note">No loan found on your account.</div></div>
@endif
</x-customer-layout>
