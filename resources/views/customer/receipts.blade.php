<x-customer-layout :title="$title" :active="$active" pageTitle="Payment Receipts">

@forelse($payments as $p)
  <div class="data-card mb-2">
    <div class="dc-head">
      <div class="fw-bold">{{ $p->is_foreclosure ? 'Loan Foreclosure' : 'EMI #'.$p->emi->emi_number }}</div>
      <span class="fw-bold text-primary">₹{{ number_format((float) $p->paid_amount, 2) }}</span>
    </div>
    <div class="dc-row"><span class="dc-label">Receipt No.</span><span>{{ $p->reference }}</span></div>
    <div class="dc-row"><span class="dc-label">Method</span><span>{{ $p->method }}</span></div>
    <div class="dc-row"><span class="dc-label">Date</span><span>{{ $p->submitted_at->format('d/m/Y') }}</span></div>
    <a class="btn btn-sm btn-outline-fin mt-2" href="{{ route('payments.receipt', $p) }}" target="_blank"><i class="fa-solid fa-receipt me-1"></i>View / Download Receipt</a>
  </div>
@empty
  <div class="card-flat p-4 text-center">
    <i class="fa-solid fa-receipt text-muted-fin" style="font-size:2.4rem;"></i>
    <div class="small-note mt-3">No approved payments yet. Once your EMI payment is verified, its receipt will appear here.</div>
  </div>
@endforelse

</x-customer-layout>
