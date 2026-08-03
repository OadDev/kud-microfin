@php
    $lateFee = $emi && $emi->displayStatus() === 'Overdue' ? $loan->late_fee : 0;
    $totalPayable = $emi ? (float) $emi->amount + (float) $lateFee : 0;
@endphp
<x-customer-layout :title="$title" :active="$active" pageTitle="Pay EMI">

@if(!$emi && $pendingVerificationEmi)
  <div class="card-flat p-4 text-center">
    <i class="fa-solid fa-hourglass-half text-warning" style="font-size:2.4rem;"></i>
    <h6 class="fw-bold mt-3">Payment Under Verification</h6>
    <div class="small-note">Your payment for EMI #{{ $pendingVerificationEmi->emi_number }} (₹{{ number_format($pendingVerificationEmi->amount) }}) is being reviewed by Admin. You'll be able to pay your next EMI once this is verified.</div>
  </div>
@elseif(!$emi)
  <div class="card-flat p-4 text-center">
    <i class="fa-solid fa-circle-check text-success" style="font-size:2.4rem;"></i>
    <h6 class="fw-bold mt-3">All EMIs Paid!</h6>
    <div class="small-note">You have no pending EMI payments on this loan.</div>
  </div>
@else
  <div class="card-flat p-3 mb-3">
    <div class="section-title mb-2">Payment Summary</div>
    <div class="dc-row"><span class="text-muted-fin">EMI Number</span><span>#{{ $emi->emi_number }}</span></div>
    <div class="dc-row"><span class="text-muted-fin">Due Date</span><span>{{ $emi->due_date->format('d/m/Y') }}</span></div>
    <div class="dc-row"><span class="text-muted-fin">EMI Amount</span><span>₹{{ number_format($emi->amount) }}</span></div>
    <div class="dc-row"><span class="text-muted-fin">Late Fee</span><span>₹{{ number_format($lateFee) }}</span></div>
    <div class="dc-row"><span class="text-muted-fin fw-semibold">Total Payable</span><span class="fw-bold">₹{{ number_format($totalPayable) }}</span></div>
  </div>

  <div class="card-flat p-3 mb-3">
    <div class="section-title mb-2">Payment Details</div>
    <div class="qr-box mb-3">
      @if($settings->qr_path)
        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($settings->qr_path) }}" alt="Payment QR code">
      @endif
    </div>
    <div class="copy-chip mb-2" onclick="copyToClipboard('{{ $settings->upi_id }}')"><span><i class="fa-solid fa-at me-1"></i>{{ $settings->upi_id }}</span><i class="fa-solid fa-copy"></i></div>
    <div class="small-note mb-3">Account Holder: {{ $settings->upi_holder }}</div>
    <div class="copy-chip mb-2" onclick="copyToClipboard('{{ $settings->bank_name }}')"><span>Bank: {{ $settings->bank_name }}</span><i class="fa-solid fa-copy"></i></div>
    <div class="copy-chip mb-2" onclick="copyToClipboard('{{ $settings->account_no }}')"><span>A/C No: {{ $settings->account_no }}</span><i class="fa-solid fa-copy"></i></div>
    <div class="copy-chip mb-2" onclick="copyToClipboard('{{ $settings->ifsc }}')"><span>IFSC: {{ $settings->ifsc }}</span><i class="fa-solid fa-copy"></i></div>
    <div class="small-note">Branch: {{ $settings->branch }} · Holder: {{ $settings->bank_holder }}</div>
    <div class="alert alert-info mt-3 mb-0" style="font-size:.78rem;"><i class="fa-solid fa-circle-info me-1"></i>This is a manual payment process. Your EMI will be marked as paid only after Admin verification.</div>
  </div>

  @if(session('success') && session('submission_ref'))
    <div class="card-flat p-3 mb-4" style="border-color:var(--green);">
      <div class="text-center mb-2"><i class="fa-solid fa-circle-check text-success" style="font-size:2rem;"></i></div>
      <div class="text-center fw-semibold mb-2">{{ session('success') }}</div>
      <div class="dc-row"><span class="text-muted-fin">Submission Reference</span><span>{{ session('submission_ref') }}</span></div>
      <div class="dc-row"><span class="text-muted-fin">Status</span><x-status-badge status="Under Verification" /></div>
    </div>
  @else
    <div class="card-flat p-3 mb-4">
      <div class="section-title mb-2">Upload Payment Proof</div>
      <form method="POST" action="{{ route('customer.pay.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="emi_id" value="{{ $emi->id }}">
        <div class="mb-2"><label class="form-label">Paid Amount</label><input type="number" class="form-control" name="paid_amount" value="{{ $totalPayable }}"></div>
        <div class="mb-2"><label class="form-label">Payment Date</label><input type="date" class="form-control" name="payment_date" value="{{ now()->format('Y-m-d') }}"></div>
        <div class="mb-2"><label class="form-label">Payment Method</label>
          <select class="form-select" name="method"><option value="UPI">UPI</option><option value="Bank Transfer">Bank Transfer</option></select>
        </div>
        <div class="mb-2"><label class="form-label">Transaction / UTR Number</label><input class="form-control" name="txn_reference" placeholder="e.g. 425678901234"></div>
        <div class="mb-2"><label class="form-label">Upload Payment Screenshot</label><input type="file" class="form-control" name="screenshot" accept="image/*" id="pay_screenshot" onchange="previewPayScreenshot(this)"></div>
        <div id="payScreenshotPreview" class="mb-2"></div>
        <div class="mb-3"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="2" placeholder="Optional"></textarea></div>
        <button class="btn btn-primary-fin w-100" type="submit"><i class="fa-solid fa-paper-plane me-1"></i>Submit Payment for Verification</button>
      </form>
    </div>
  @endif
@endif

@push('scripts')
<script>
function previewPayScreenshot(input){
  const host = document.getElementById('payScreenshotPreview');
  if(input.files && input.files[0]){
    host.innerHTML = '<div class="screenshot-thumb d-flex align-items-center justify-content-center" style="width:80px;height:80px;"><i class="fa-solid fa-image text-primary"></i></div><div class="small-note mt-1">'+input.files[0].name+'</div>';
  } else {
    host.innerHTML = '';
  }
}
</script>
@endpush
</x-customer-layout>
