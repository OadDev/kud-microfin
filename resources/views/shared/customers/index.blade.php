<x-app-layout :title="$title" :active="$active">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <div class="section-title mb-0">{{ $title }}</div>
    <div class="page-sub">{{ $isAdmin ? 'All registered customers across shop owners' : 'Customers assigned to your shop' }}</div>
  </div>
  <a class="btn btn-primary-fin btn-sm" href="{{ route($isAdmin ? 'admin.customers.create' : 'shopowner.customers.create') }}"><i class="fa-solid fa-user-plus me-1"></i>Create Customer</a>
</div>

<div class="card-flat p-3 mb-3">
  <form method="GET" class="row g-2">
    <div class="col-md-8"><input class="form-control" name="search" value="{{ $search }}" placeholder="Search by name, mobile, customer ID or loan A/C..."></div>
    <div class="col-md-4">
      <select class="form-select" name="status" onchange="this.form.submit()">
        <option value="" {{ $status==='All'?'selected':'' }}>All Statuses</option>
        @foreach(['active','overdue','closed'] as $s)
          <option value="{{ $s }}" {{ $status===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
    </div>
  </form>
</div>

<div class="card-flat p-0 table-responsive-fin">
  <table class="table table-fin mb-0">
    <thead><tr><th>Customer ID</th><th>Name</th><th>Mobile</th><th>Loan A/C No.</th><th>Loan Amount</th><th>EMI Amt</th><th>Next Due</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($customers as $c)
      @php $loan = $c->currentLoan(); $next = $loan?->emis->first(fn($e)=>$e->status!=='paid'); @endphp
      <tr>
        <td class="fw-semibold">{{ $c->customer_code }}</td>
        <td>{{ $c->user->name }}</td>
        <td>{{ $c->user->mobile }}</td>
        <td>{{ $loan?->loan_account_no }}</td>
        <td>₹{{ number_format($loan?->principal ?? 0) }}</td>
        <td>₹{{ number_format($loan?->emi_amount ?? 0) }}</td>
        <td>{{ $next?->due_date->format('d/m/Y') ?? '-' }}</td>
        <td><x-status-badge :status="ucfirst($loan?->status ?? 'active')" /></td>
        <td><a class="btn btn-sm btn-outline-fin" href="{{ route('customers.show', $c) }}"><i class="fa-solid fa-eye"></i></a></td>
      </tr>
    @empty
      <tr><td colspan="9" class="text-center text-muted-fin py-3">No customers found.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="data-cards">
  @forelse($customers as $c)
    @php $loan = $c->currentLoan(); $next = $loan?->emis->first(fn($e)=>$e->status!=='paid'); @endphp
    <div class="data-card">
      <div class="dc-head">
        <div><div class="fw-bold">{{ $c->user->name }}</div><div class="small-note">{{ $c->customer_code }} · {{ $loan?->loan_account_no }}</div></div>
        <x-status-badge :status="ucfirst($loan?->status ?? 'active')" />
      </div>
      <div class="dc-row"><span class="dc-label">Mobile</span><span>{{ $c->user->mobile }}</span></div>
      <div class="dc-row"><span class="dc-label">Loan Amount</span><span>₹{{ number_format($loan?->principal ?? 0) }}</span></div>
      <div class="dc-row"><span class="dc-label">EMI Amount</span><span>₹{{ number_format($loan?->emi_amount ?? 0) }}</span></div>
      <div class="dc-row"><span class="dc-label">Next Due</span><span>{{ $next?->due_date->format('d/m/Y') ?? '-' }}</span></div>
      <a class="btn btn-sm btn-outline-fin w-100 mt-2" href="{{ route('customers.show', $c) }}"><i class="fa-solid fa-eye me-1"></i>View Details</a>
    </div>
  @empty
    <div class="text-center text-muted-fin py-3">No customers found.</div>
  @endforelse
</div>
</x-app-layout>
