<x-app-layout title="Payment Submission" active="payment-verification">
<a class="btn btn-sm btn-outline-fin mb-3" href="{{ route('admin.payment-verification.index') }}"><i class="fa-solid fa-arrow-left me-1"></i>Back to Payment Verification</a>

<div class="card-flat p-3 p-md-4" style="max-width:560px;">
  <div class="text-center mb-3">
    @if(\Illuminate\Support\Facades\Storage::disk('public')->exists($p->screenshot_path))
      <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($p->screenshot_path) }}" alt="Payment screenshot" class="img-fluid rounded" style="max-height:280px;">
    @else
      <div class="screenshot-thumb mx-auto d-flex align-items-center justify-content-center" style="width:120px;height:120px;">
        <i class="fa-solid fa-image fa-2x text-primary"></i>
      </div>
      <div class="small-note mt-1">Simulated payment screenshot preview</div>
    @endif
  </div>
  <div class="dc-row"><span class="text-muted-fin">Customer</span><span>{{ $p->customer->user->name }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Loan A/C No.</span><span>{{ $p->loan->loan_account_no }}</span></div>
  @if($p->is_foreclosure)
    <div class="dc-row"><span class="text-muted-fin">Type</span><span><span class="badge bg-primary">Loan Foreclosure</span></span></div>
  @else
    <div class="dc-row"><span class="text-muted-fin">EMI Number</span><span>#{{ $p->emi->emi_number }}</span></div>
    <div class="dc-row"><span class="text-muted-fin">EMI Amount</span><span>₹{{ number_format($p->emi->amount) }}</span></div>
  @endif
  <div class="dc-row"><span class="text-muted-fin">Paid Amount</span><span>₹{{ number_format($p->paid_amount) }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Payment Method</span><span>{{ $p->method }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Transaction No.</span><span>{{ $p->txn_reference }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Submitted On</span><span>{{ $p->submitted_at->format('d/m/Y') }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Status</span><x-status-badge :status="ucfirst(str_replace('_',' ',$p->status))" /></div>
  @if($p->remarks)
    <div class="mt-2 small-note"><strong>Customer Remarks:</strong> {{ $p->remarks }}</div>
  @endif
  @if($p->status === 'rejected' && $p->reject_reason)
    <div class="alert alert-danger mt-3 mb-0" style="font-size:.85rem;"><strong>Rejection Reason:</strong> {{ $p->reject_reason }}</div>
  @endif

  @if($p->status === 'under_verification')
  <div class="d-flex gap-2 mt-4">
    <button class="btn btn-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalRejectPayment"><i class="fa-solid fa-xmark me-1"></i>Reject</button>
    <form method="POST" action="{{ route('admin.payment-verification.approve', $p) }}" class="flex-fill">
      @csrf
      <button class="btn btn-primary-fin btn-sm w-100" type="submit"><i class="fa-solid fa-check me-1"></i>Approve</button>
    </form>
  </div>
  @endif
</div>

@if($p->status === 'under_verification')
<div class="modal fade" id="modalRejectPayment" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.payment-verification.reject', $p) }}">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Reject Payment Submission</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
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
@endif
</x-app-layout>
