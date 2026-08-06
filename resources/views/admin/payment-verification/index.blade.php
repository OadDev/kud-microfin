<x-app-layout :title="$title" :active="$active">
<div class="mb-3">
  <div class="section-title mb-0">Payment Verification</div>
  <div class="page-sub">Review and approve manual EMI payment submissions</div>
</div>
<div class="card-flat p-0 table-responsive-fin">
  <table class="table table-fin mb-0">
    <thead><tr><th>Customer</th><th>Loan A/C</th><th>EMI #</th><th>Amount</th><th>Method</th><th>Txn No.</th><th>Screenshot</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($submissions as $p)
      <tr>
        <td>{{ $p->customer->user->name }}</td>
        <td>{{ $p->loan->loan_account_no }}</td>
        <td>@if($p->is_foreclosure)<span class="badge bg-primary">Foreclosure</span>@else EMI #{{ $p->emi->emi_number }}@endif</td>
        <td>₹{{ number_format($p->paid_amount) }}</td>
        <td>{{ $p->method }}</td>
        <td>{{ $p->txn_reference }}</td>
        <td><div class="screenshot-thumb d-inline-flex align-items-center justify-content-center"><i class="fa-solid fa-image text-primary"></i></div></td>
        <td>{{ $p->submitted_at->format('d/m/Y') }}</td>
        <td><x-status-badge :status="ucfirst(str_replace('_',' ',$p->status))" /></td>
        <td><a class="btn btn-sm btn-outline-fin" href="{{ route('admin.payment-verification.show', $p) }}">View</a></td>
      </tr>
    @empty
      <tr><td colspan="10" class="text-center text-muted-fin py-3">No payment submissions.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div class="data-cards">
  @forelse($submissions as $p)
    <div class="data-card">
      <div class="dc-head"><div><div class="fw-bold">{{ $p->customer->user->name }}</div><div class="small-note">{{ $p->loan->loan_account_no }} · {{ $p->is_foreclosure ? 'Loan Foreclosure' : 'EMI #'.$p->emi->emi_number }}</div></div><x-status-badge :status="ucfirst(str_replace('_',' ',$p->status))" /></div>
      <div class="dc-row"><span class="dc-label">Amount</span><span>₹{{ number_format($p->paid_amount) }}</span></div>
      <div class="dc-row"><span class="dc-label">Method</span><span>{{ $p->method }}</span></div>
      <div class="dc-row"><span class="dc-label">Txn No.</span><span>{{ $p->txn_reference }}</span></div>
      <div class="dc-row"><span class="dc-label">Submitted</span><span>{{ $p->submitted_at->format('d/m/Y') }}</span></div>
      <a class="btn btn-sm btn-outline-fin w-100 mt-2" href="{{ route('admin.payment-verification.show', $p) }}">View &amp; Verify</a>
    </div>
  @empty
    <div class="text-center text-muted-fin py-3">No payment submissions.</div>
  @endforelse
</div>
</x-app-layout>
