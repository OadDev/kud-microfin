<x-app-layout :title="$title" :active="$active">
<div class="mb-3">
  <div class="section-title mb-0">EMI List</div>
  <div class="page-sub">All EMIs across your customers</div>
</div>
<div class="card-flat p-0 table-responsive-fin">
  <table class="table table-fin mb-0">
    <thead><tr><th>Customer</th><th>Loan A/C</th><th>EMI #</th><th>Due Date</th><th>Amount</th><th>Status</th></tr></thead>
    <tbody>
    @forelse($rows as $e)
      <tr>
        <td>{{ $e->loan->customer->user->name }}</td>
        <td>{{ $e->loan->loan_account_no }}</td>
        <td>{{ $e->emi_number }}</td>
        <td>{{ $e->due_date->format('d/m/Y') }}</td>
        <td>₹{{ number_format($e->amount) }}</td>
        <td><x-status-badge :status="$e->displayStatus()" /></td>
      </tr>
    @empty
      <tr><td colspan="6" class="text-center text-muted-fin py-3">No EMIs found.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div class="data-cards">
  @forelse($rows as $e)
    <div class="data-card">
      <div class="dc-head"><div><div class="fw-bold">{{ $e->loan->customer->user->name }}</div><div class="small-note">{{ $e->loan->loan_account_no }} · EMI #{{ $e->emi_number }}</div></div><x-status-badge :status="$e->displayStatus()" /></div>
      <div class="dc-row"><span class="dc-label">Due Date</span><span>{{ $e->due_date->format('d/m/Y') }}</span></div>
      <div class="dc-row"><span class="dc-label">Amount</span><span>₹{{ number_format($e->amount) }}</span></div>
    </div>
  @empty
    <div class="text-center text-muted-fin py-3">No EMIs found.</div>
  @endforelse
</div>
</x-app-layout>
