<x-app-layout :title="$title" :active="$active">
<div class="mb-3">
  <div class="section-title mb-0">Active Loans</div>
  <div class="page-sub">Loans currently being repaid</div>
</div>
<div class="card-flat p-0 table-responsive-fin">
  <table class="table table-fin mb-0">
    <thead><tr><th>Customer</th><th>Loan A/C No.</th><th>Principal</th><th>Total Payable</th><th>Paid</th><th>Outstanding</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($loans as $loan)
      <tr>
        <td>{{ $loan->customer->user->name }}</td>
        <td>{{ $loan->loan_account_no }}</td>
        <td>₹{{ number_format($loan->principal) }}</td>
        <td>₹{{ number_format($loan->total_payable) }}</td>
        <td>₹{{ number_format($loan->amountPaid()) }}</td>
        <td>₹{{ number_format($loan->outstanding()) }}</td>
        <td><x-status-badge :status="ucfirst($loan->status)" /></td>
        <td><a class="btn btn-sm btn-outline-fin" href="{{ route('customers.show', $loan->customer_id) }}">View</a></td>
      </tr>
    @empty
      <tr><td colspan="8" class="text-center text-muted-fin py-3">No active loans.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div class="data-cards">
  @forelse($loans as $loan)
    <div class="data-card">
      <div class="dc-head"><div class="fw-bold">{{ $loan->customer->user->name }}</div><x-status-badge :status="ucfirst($loan->status)" /></div>
      <div class="dc-row"><span class="dc-label">Loan A/C</span><span>{{ $loan->loan_account_no }}</span></div>
      <div class="dc-row"><span class="dc-label">Total Payable</span><span>₹{{ number_format($loan->total_payable) }}</span></div>
      <div class="dc-row"><span class="dc-label">Paid</span><span>₹{{ number_format($loan->amountPaid()) }}</span></div>
      <div class="dc-row"><span class="dc-label">Outstanding</span><span>₹{{ number_format($loan->outstanding()) }}</span></div>
      <a class="btn btn-sm btn-outline-fin w-100 mt-2" href="{{ route('customers.show', $loan->customer_id) }}">View Details</a>
    </div>
  @empty
    <div class="text-center text-muted-fin py-3">No active loans.</div>
  @endforelse
</div>
</x-app-layout>
