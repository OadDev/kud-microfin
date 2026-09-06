@php
    $isAdmin = auth()->user()->isAdmin();
    $backRoute = $isAdmin ? 'admin.customers.index' : 'shopowner.customers.index';
    $paidEmis = $loan?->emis->where('status', 'paid')->count() ?? 0;
    $totalEmis = $loan?->emis->count() ?? 0;
    $outstanding = $loan ? $loan->outstanding() : 0;
    $next = $loan?->emis->first(fn($e) => $e->status !== 'paid');
@endphp
<x-app-layout title="Customer Details" :active="$isAdmin ? 'customers' : 'customers'">

<a class="btn btn-sm btn-outline-fin mb-3" href="{{ route($backRoute) }}"><i class="fa-solid fa-arrow-left me-1"></i>Back to Customers</a>

<div class="card-flat p-3 p-md-4 mb-3">
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div class="d-flex align-items-center gap-3">
      <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:1.6rem;"><i class="fa-solid fa-user"></i></div>
      <div>
        <div class="fw-bold fs-5">{{ $customer->user->name }}</div>
        <div class="small-note">{{ $customer->customer_code }} · Loan A/C {{ $loan?->loan_account_no }}</div>
        <div class="small-note"><i class="fa-solid fa-phone me-1"></i>{{ $customer->user->mobile }}@if($customer->shopOwner) · Shop: {{ $customer->shopOwner->shop_name }}@endif</div>
      </div>
    </div>
    @if($isAdmin)
      <x-status-badge :status="ucfirst($loan?->status ?? 'active')" />
    @endif
  </div>

  @if($isAdmin)
    @if($loan?->status === 'pending')
      <div class="alert alert-warning mt-3 mb-0" style="font-size:.85rem;"><i class="fa-solid fa-hourglass-half me-1"></i>This loan is awaiting Admin approval.</div>
    @elseif($loan?->status === 'rejected')
      <div class="alert alert-danger mt-3 mb-0" style="font-size:.85rem;"><i class="fa-solid fa-xmark me-1"></i>This loan was rejected.@if($loan->reject_reason) Reason: {{ $loan->reject_reason }}@endif</div>
    @elseif($loan?->status === 'foreclosed')
      <div class="alert alert-info mt-3 mb-0" style="font-size:.85rem;"><i class="fa-solid fa-circle-check me-1"></i>This loan was foreclosed on {{ $loan->foreclosed_at->format('d/m/Y') }} (₹{{ number_format($loan->foreclosure_amount, 2) }} settled).</div>
    @endif
  @endif

  @if($isAdmin && $loan && in_array($loan->status, ['active', 'overdue']))
    @php $fc = $loan->foreclosureBreakdown(); @endphp
    <div class="alert alert-light border mt-3 mb-2" style="font-size:.85rem;">
      <div class="fw-semibold mb-1"><i class="fa-solid fa-flag-checkered me-1"></i>Foreclosure Quote</div>
      <div class="dc-row"><span class="text-muted-fin">Remaining Outstanding</span><span>₹{{ number_format($fc['outstanding'], 2) }}</span></div>
      <div class="dc-row"><span class="text-muted-fin">Foreclosure Interest ({{ rtrim(rtrim(number_format($fc['interest_rate'], 2), '0'), '.') }}%)</span><span>₹{{ number_format($fc['interest_amount'], 2) }}</span></div>
      <div class="dc-row"><span class="text-muted-fin fw-semibold">Total Payable to Foreclose</span><span class="fw-bold">₹{{ number_format($fc['total'], 2) }}</span></div>
    </div>
    <form method="POST" action="{{ route('loans.foreclose', $loan) }}">
      @csrf
      <button class="btn btn-outline-fin btn-sm" type="submit" data-confirm="Foreclose this loan? The customer will need to pay ₹{{ number_format($fc['total'], 2) }} now (₹{{ number_format($fc['outstanding'], 2) }} outstanding + ₹{{ number_format($fc['interest_amount'], 2) }} foreclosure interest), and the loan will close immediately." data-confirm-class="btn-primary-fin"><i class="fa-solid fa-flag-checkered me-1"></i>Foreclose Loan (₹{{ number_format($fc['total'], 2) }})</button>
    </form>
  @endif

  @if($isAdmin)
    <div class="row g-3 mt-1">
      <div class="col-6 col-md-3"><div class="small-note">Principal Amount</div><div class="fw-bold">₹{{ number_format($loan?->principal ?? 0) }}</div></div>
      <div class="col-6 col-md-3"><div class="small-note">Total Payable</div><div class="fw-bold">₹{{ number_format($loan?->total_payable ?? 0) }}</div></div>
      <div class="col-6 col-md-3"><div class="small-note">Amount Paid</div><div class="fw-bold text-success">₹{{ number_format($loan?->amountPaid() ?? 0) }}</div></div>
      <div class="col-6 col-md-3"><div class="small-note">Outstanding</div><div class="fw-bold text-danger">₹{{ number_format($outstanding) }}</div></div>
      <div class="col-6 col-md-3"><div class="small-note">EMI Amount</div><div class="fw-bold">₹{{ number_format($loan?->emi_amount ?? 0) }}</div></div>
      <div class="col-6 col-md-3"><div class="small-note">Paid EMIs</div><div class="fw-bold">{{ $paidEmis }} / {{ $totalEmis }}</div></div>
      <div class="col-6 col-md-3"><div class="small-note">Pending EMIs</div><div class="fw-bold">{{ $totalEmis - $paidEmis }}</div></div>
      <div class="col-6 col-md-3"><div class="small-note">Next EMI Due</div><div class="fw-bold">{{ $next?->due_date->format('d/m/Y') ?? '-' }}</div></div>
    </div>
  @endif
</div>

<ul class="nav nav-pills flex-nowrap overflow-auto mb-3" style="gap:6px;">
  @foreach(($isAdmin ? ['overview'=>'Overview','emi'=>'EMI Schedule','payments'=>'Payments','docs'=>'Documents'] : ['overview'=>'Overview','docs'=>'Documents']) as $key=>$label)
    <li class="nav-item">
      <a class="nav-link {{ $tab===$key?'active':'' }}" style="{{ $tab===$key ? 'background:var(--primary);color:#fff;' : 'color:var(--primary);' }}" href="{{ route('customers.show', ['customer'=>$customer,'tab'=>$key]) }}">{{ $label }}</a>
    </li>
  @endforeach
</ul>

@if($tab === 'overview')
  <div class="row g-3">
    <div class="col-md-6">
      <div class="card-flat p-3">
        <div class="section-title mb-2">Personal Details</div>
        <div class="dc-row"><span class="text-muted-fin">Father's/Guardian's Name</span><span>{{ $customer->father_name ?? '-' }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Gender</span><span>{{ $customer->gender ?? '-' }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Date of Birth</span><span>{{ $customer->dob?->format('d/m/Y') ?? '-' }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Alternate Mobile</span><span>{{ $customer->alt_mobile ?? '-' }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Email</span><span>{{ $customer->user->email ?? '-' }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Address</span><span class="text-end">{{ $customer->address }}, {{ $customer->city }}, {{ $customer->state }} - {{ $customer->pin }}</span></div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card-flat p-3">
        <div class="section-title mb-2">{{ $isAdmin ? 'Identity & Loan Info' : 'Identity Details' }}</div>
        <div class="dc-row"><span class="text-muted-fin">PAN Number</span><span>{{ $customer->pan }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Aadhaar Number</span><span>{{ $customer->aadhaar }}</span></div>
        @if($isAdmin)
          <div class="dc-row"><span class="text-muted-fin">Loan Purpose</span><span>{{ $loan?->purpose }}</span></div>
          <div class="dc-row"><span class="text-muted-fin">EMI Frequency</span><span>{{ $loan?->frequency }}</span></div>
          <div class="dc-row"><span class="text-muted-fin">Loan Start Date</span><span>{{ $loan?->start_date->format('d/m/Y') }}</span></div>
          <div class="dc-row"><span class="text-muted-fin">Late Fee / EMI</span><span>₹{{ number_format($loan?->late_fee ?? 0) }}</span></div>
        @endif
      </div>
    </div>
  </div>
@elseif($isAdmin && $tab === 'emi')
  <div class="card-flat p-0 table-responsive-fin">
    <table class="table table-fin mb-0">
      <thead><tr><th>EMI #</th><th>Due Date</th><th>Amount</th><th>Status</th><th>Payment Date</th><th></th></tr></thead>
      <tbody>
      @foreach($loan->emis as $e)
        <tr>
          <td>{{ $e->emi_number }}</td><td>{{ $e->due_date->format('d/m/Y') }}</td>
          <td>
            ₹{{ number_format($e->amount) }}
            @if($e->amount_paid > 0 && $e->status !== 'paid')
              <div class="small-note text-success">₹{{ number_format($e->amount_paid, 2) }} paid, ₹{{ number_format($e->remainingAmount(), 2) }} due</div>
            @endif
          </td>
          <td><x-status-badge :status="$e->displayStatus()" /></td><td>{{ $e->payment_date?->format('d/m/Y') ?? '-' }}</td>
          <td>
            @if($e->status !== 'paid')
              <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="modal" data-bs-target="#modalCashEmi{{ $e->id }}"><i class="fa-solid fa-money-bill-wave me-1"></i>Mark Paid (Cash)</button>
            @endif
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  <div class="data-cards">
    @foreach($loan->emis as $e)
      <div class="data-card">
        <div class="dc-head"><div class="fw-bold">EMI #{{ $e->emi_number }}</div><x-status-badge :status="$e->displayStatus()" /></div>
        <div class="dc-row"><span class="dc-label">Due Date</span><span>{{ $e->due_date->format('d/m/Y') }}</span></div>
        <div class="dc-row"><span class="dc-label">Amount</span><span>₹{{ number_format($e->amount) }}</span></div>
        @if($e->amount_paid > 0 && $e->status !== 'paid')
          <div class="dc-row"><span class="dc-label">Paid / Due</span><span class="text-success">₹{{ number_format($e->amount_paid, 2) }} / ₹{{ number_format($e->remainingAmount(), 2) }}</span></div>
        @endif
        <div class="dc-row"><span class="dc-label">Payment Date</span><span>{{ $e->payment_date?->format('d/m/Y') ?? '-' }}</span></div>
        @if($e->status !== 'paid')
          <button class="btn btn-sm btn-outline-success w-100 mt-2" type="button" data-bs-toggle="modal" data-bs-target="#modalCashEmi{{ $e->id }}"><i class="fa-solid fa-money-bill-wave me-1"></i>Mark Paid (Cash)</button>
        @endif
      </div>
    @endforeach
  </div>

  @foreach($loan->emis as $e)
    @if($e->status !== 'paid')
      <div class="modal fade" id="modalCashEmi{{ $e->id }}" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST" action="{{ route('admin.emis.mark-paid-cash', $e) }}">
              @csrf
              <div class="modal-header">
                <h5 class="modal-title">Mark EMI #{{ $e->emi_number }} Paid (Cash)</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="small-note mb-2">₹{{ number_format($e->remainingAmount(), 2) }} due from {{ $customer->user->name }}.</div>
                <label class="form-label">Amount Paid</label>
                <input type="number" step="0.01" min="0.01" max="{{ $e->remainingAmount() }}" class="form-control" name="amount" value="{{ $e->remainingAmount() }}" required>
                <div class="small-note mt-1">Leave the full due amount, or lower it if the customer only paid part -- the remainder stays due and can be marked paid later.</div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary-fin" type="submit"><i class="fa-solid fa-check me-1"></i>Record Payment</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    @endif
  @endforeach
@elseif($isAdmin && $tab === 'payments')
  @php $payments = $loan->paymentSubmissions->sortByDesc('id'); @endphp
  <div class="card-flat p-0 table-responsive-fin">
    <table class="table table-fin mb-0">
      <thead><tr><th>Ref</th><th>EMI #</th><th>Amount</th><th>Method</th><th>Txn No.</th><th>Date</th><th>Status</th><th></th></tr></thead>
      <tbody>
      @forelse($payments as $p)
        <tr><td>{{ $p->reference }}</td><td>{{ $p->is_foreclosure ? 'Foreclosure' : 'EMI #'.$p->emi->emi_number }}</td><td>₹{{ number_format($p->paid_amount) }}</td><td>{{ $p->method }}</td><td>{{ $p->txn_reference ?? '-' }}</td><td>{{ $p->submitted_at->format('d/m/Y') }}</td><td><x-status-badge :status="ucfirst(str_replace('_',' ',$p->status))" /></td>
          <td>@if($p->status === 'approved')<a class="btn btn-sm btn-outline-fin" href="{{ route('payments.receipt', $p) }}" target="_blank"><i class="fa-solid fa-receipt me-1"></i>Receipt</a>@endif</td>
        </tr>
      @empty
        <tr><td colspan="8" class="text-center text-muted-fin py-3">No payments yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div class="data-cards">
    @forelse($payments as $p)
      <div class="data-card">
        <div class="dc-head"><div class="fw-bold">{{ $p->is_foreclosure ? 'Foreclosure' : 'EMI #'.$p->emi->emi_number }}</div><x-status-badge :status="ucfirst(str_replace('_',' ',$p->status))" /></div>
        <div class="dc-row"><span class="dc-label">Amount</span><span>₹{{ number_format($p->paid_amount) }}</span></div>
        <div class="dc-row"><span class="dc-label">Method</span><span>{{ $p->method }}</span></div>
        <div class="dc-row"><span class="dc-label">Txn No.</span><span>{{ $p->txn_reference ?? '-' }}</span></div>
        <div class="dc-row"><span class="dc-label">Date</span><span>{{ $p->submitted_at->format('d/m/Y') }}</span></div>
        @if($p->status === 'approved')
          <a class="btn btn-sm btn-outline-fin mt-2" href="{{ route('payments.receipt', $p) }}" target="_blank"><i class="fa-solid fa-receipt me-1"></i>Receipt</a>
        @endif
      </div>
    @empty
      <div class="text-center text-muted-fin py-3">No payments yet.</div>
    @endforelse
  </div>
@elseif($tab === 'docs')
  <div class="row g-3">
    <x-document-card label="Welcome Letter" icon="fa-envelope-open-text" :loan="$loan" type="welcome_letter" :previewable="true" />
    <x-document-card label="Loan Sanction Letter" icon="fa-file-signature" :loan="$loan" type="sanction_letter" :previewable="true" />
    @if($loan && in_array($loan->status, ['closed', 'foreclosed']))
      <x-document-card label="No Objection Certificate (NOC)" icon="fa-file-shield" :loan="$loan" type="noc" :previewable="true" />
    @else
      <x-document-card label="No Objection Certificate (NOC)" icon="fa-file-shield" />
    @endif
    <x-document-card label="EMI Schedule" icon="fa-calendar-check" />
    <x-document-card label="Payment Receipts" icon="fa-receipt" />
    <x-document-card label="Customer Statement" icon="fa-file-lines" />
  </div>
@endif

</x-app-layout>
