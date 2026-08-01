<x-app-layout :title="$title" active="loan-approvals">
<a class="btn btn-sm btn-outline-fin mb-3" href="{{ route('admin.loan-approvals.index') }}"><i class="fa-solid fa-arrow-left me-1"></i>Back to Loan Approvals</a>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card-flat p-3 mb-3">
      <div class="section-title mb-2">Loan Details</div>
      <div class="dc-row"><span class="text-muted-fin">Loan A/C No.</span><span class="fw-semibold">{{ $loan->loan_account_no }}</span></div>
      <div class="dc-row"><span class="text-muted-fin">Customer</span><span>{{ $loan->customer->user->name }} ({{ $loan->customer->customer_code }})</span></div>
      <div class="dc-row"><span class="text-muted-fin">Shop Owner</span><span>{{ $loan->shopOwner->user->name }}</span></div>
      <div class="dc-row"><span class="text-muted-fin">Purpose</span><span>{{ $loan->purpose }}</span></div>
      <div class="dc-row"><span class="text-muted-fin">Principal</span><span>₹{{ number_format($loan->principal) }}</span></div>
      <div class="dc-row"><span class="text-muted-fin">Interest</span><span>₹{{ number_format($loan->interest) }}</span></div>
      <div class="dc-row"><span class="text-muted-fin">Processing Fee</span><span>₹{{ number_format($loan->processing_fee) }}</span></div>
      <div class="dc-row"><span class="text-muted-fin fw-semibold">Total Payable</span><span class="fw-semibold">₹{{ number_format($loan->total_payable) }}</span></div>
      <div class="dc-row"><span class="text-muted-fin">EMI Amount</span><span>₹{{ number_format($loan->emi_amount) }} × {{ $loan->num_emis }} ({{ $loan->frequency }})</span></div>
      <div class="dc-row"><span class="text-muted-fin">Start Date</span><span>{{ $loan->start_date->format('d/m/Y') }}</span></div>
      <div class="dc-row"><span class="text-muted-fin">First Due Date</span><span>{{ $loan->first_due_date->format('d/m/Y') }}</span></div>
    </div>

    <div class="card-flat p-3">
      <div class="d-flex gap-2">
        <button class="btn btn-danger flex-fill" type="button" data-bs-toggle="modal" data-bs-target="#modalRejectLoan"><i class="fa-solid fa-xmark me-1"></i>Reject</button>
        <form method="POST" action="{{ route('admin.loan-approvals.approve', $loan) }}" class="flex-fill">
          @csrf
          <button class="btn btn-primary-fin w-100" type="submit"><i class="fa-solid fa-check me-1"></i>Approve</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card-flat p-0 table-responsive-fin">
      <div class="section-title p-3 pb-0">EMI Schedule Preview</div>
      <table class="table table-fin mb-0">
        <thead><tr><th>#</th><th>Due Date</th><th>Amount</th></tr></thead>
        <tbody>
        @foreach($loan->emis as $emi)
          <tr><td>{{ $emi->emi_number }}</td><td>{{ $emi->due_date->format('d/m/Y') }}</td><td>₹{{ number_format($emi->amount) }}</td></tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="modalRejectLoan" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.loan-approvals.reject', $loan) }}">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Reject Loan Application</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <label class="form-label">Reason for Rejection</label>
          <textarea class="form-control" name="reason" rows="3" required></textarea>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-danger" type="submit"><i class="fa-solid fa-xmark me-1"></i>Confirm Reject</button>
        </div>
      </form>
    </div>
  </div>
</div>
</x-app-layout>
