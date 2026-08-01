<x-app-layout :title="$title" :active="$active">
<div class="mb-3">
  <div class="section-title mb-0">Loan Approvals</div>
  <div class="page-sub">Review and approve or reject loan applications submitted by Shop Owners</div>
</div>
<div class="card-flat p-0 table-responsive-fin">
  <table class="table table-fin mb-0">
    <thead><tr><th>Loan A/C</th><th>Customer</th><th>Shop Owner</th><th>Principal</th><th>Total Payable</th><th>EMIs</th><th>Submitted</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($loans as $loan)
      <tr>
        <td class="fw-semibold">{{ $loan->loan_account_no }}</td>
        <td>{{ $loan->customer->user->name }}</td>
        <td>{{ $loan->shopOwner->user->name }}</td>
        <td>₹{{ number_format($loan->principal) }}</td>
        <td>₹{{ number_format($loan->total_payable) }}</td>
        <td>{{ $loan->num_emis }} × {{ $loan->frequency }}</td>
        <td>{{ $loan->created_at->format('d/m/Y') }}</td>
        <td><a class="btn btn-sm btn-outline-fin" href="{{ route('admin.loan-approvals.show', $loan) }}">Review</a></td>
      </tr>
    @empty
      <tr><td colspan="8" class="text-center text-muted-fin py-3">No loan applications waiting for approval.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div class="data-cards">
  @forelse($loans as $loan)
    <div class="data-card">
      <div class="dc-head"><div><div class="fw-bold">{{ $loan->loan_account_no }}</div><div class="small-note">{{ $loan->customer->user->name }} · {{ $loan->shopOwner->user->name }}</div></div><x-status-badge status="Pending" /></div>
      <div class="dc-row"><span class="dc-label">Principal</span><span>₹{{ number_format($loan->principal) }}</span></div>
      <div class="dc-row"><span class="dc-label">Total Payable</span><span>₹{{ number_format($loan->total_payable) }}</span></div>
      <a class="btn btn-sm btn-outline-fin w-100 mt-2" href="{{ route('admin.loan-approvals.show', $loan) }}">Review</a>
    </div>
  @empty
    <div class="text-center text-muted-fin py-3">No loan applications waiting for approval.</div>
  @endforelse
</div>
</x-app-layout>
