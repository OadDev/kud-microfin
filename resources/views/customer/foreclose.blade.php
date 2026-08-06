<x-customer-layout :title="$title" :active="$active" pageTitle="Foreclose Loan">

<a href="{{ route('customer.loan') }}" class="btn btn-sm btn-outline-fin mb-3"><i class="fa-solid fa-arrow-left me-1"></i>Back to Loan</a>

@if($pendingSubmission)
  <div class="card-flat p-4 text-center">
    <i class="fa-solid fa-hourglass-half text-warning" style="font-size:2.4rem;"></i>
    <h6 class="fw-bold mt-3">Foreclosure Under Verification</h6>
    <div class="small-note">Your foreclosure payment of ₹{{ number_format($pendingSubmission->paid_amount, 2) }} is being reviewed by Admin. Your loan will close once it's approved.</div>
  </div>
@else
  <div class="card-flat p-3 mb-3">
    <div class="section-title mb-2">Foreclosure Summary</div>
    <div class="dc-row"><span class="text-muted-fin">Loan Account No.</span><span class="fw-semibold">{{ $loan->loan_account_no }}</span></div>
    <div class="dc-row"><span class="text-muted-fin">Remaining Outstanding</span><span>₹{{ number_format($breakdown['outstanding'], 2) }}</span></div>
    <div class="dc-row"><span class="text-muted-fin">Foreclosure Interest ({{ rtrim(rtrim(number_format($breakdown['interest_rate'], 2), '0'), '.') }}%)</span><span>₹{{ number_format($breakdown['interest_amount'], 2) }}</span></div>
    <div class="dc-row"><span class="text-muted-fin fw-semibold">Total Due Amount</span><span class="fw-bold">₹{{ number_format($breakdown['total'], 2) }}</span></div>
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
    <div class="alert alert-info mt-3 mb-0" style="font-size:.78rem;"><i class="fa-solid fa-circle-info me-1"></i>This is a manual payment process. Your loan will be foreclosed only after Admin verifies this payment.</div>
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
      <form method="POST" action="{{ route('customer.foreclose.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-2"><label class="form-label">Amount Paid</label><input type="text" class="form-control" value="₹{{ number_format($breakdown['total'], 2) }}" disabled></div>
        <div class="mb-2"><label class="form-label">Payment Date</label><input type="date" class="form-control" name="payment_date" value="{{ now()->format('Y-m-d') }}"></div>
        <div class="mb-2"><label class="form-label">Payment Method</label>
          <select class="form-select" name="method"><option value="UPI">UPI</option><option value="Bank Transfer">Bank Transfer</option></select>
        </div>
        <div class="mb-2"><label class="form-label">Transaction / UTR Number</label><input class="form-control" name="txn_reference" placeholder="e.g. 425678901234"></div>
        <div class="mb-2"><label class="form-label">Upload Payment Screenshot</label><input type="file" class="form-control" name="screenshot" accept="image/*" id="fcScreenshot" onchange="previewFcScreenshot(this)"></div>
        <div id="fcScreenshotPreview" class="mb-2"></div>
        <div class="mb-3"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="2" placeholder="Optional"></textarea></div>
        <button class="btn btn-primary-fin w-100" type="submit"><i class="fa-solid fa-paper-plane me-1"></i>Submit Foreclosure Payment</button>
      </form>
    </div>
  @endif
@endif

@push('scripts')
<script>
function previewFcScreenshot(input){
  const host = document.getElementById('fcScreenshotPreview');
  if(input.files && input.files[0]){
    host.innerHTML = '<div class="screenshot-thumb d-flex align-items-center justify-content-center" style="width:80px;height:80px;"><i class="fa-solid fa-image text-primary"></i></div><div class="small-note mt-1">'+input.files[0].name+'</div>';
  } else {
    host.innerHTML = '';
  }
}
</script>
@endpush
</x-customer-layout>
