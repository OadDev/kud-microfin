<x-app-layout :title="$title" :active="$active">
<div class="mb-3">
  <div class="section-title mb-0">Payment Settings</div>
  <div class="page-sub">Configure the UPI, QR &amp; bank details shown to customers for manual payment</div>
</div>
<div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
  <i class="fa-solid fa-triangle-exclamation"></i>
  <div>No online payment gateway is connected. All EMI payments are manually verified.</div>
</div>
<form method="POST" action="{{ route('admin.payment-settings.update') }}" enctype="multipart/form-data">
  @csrf
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card-flat p-3 mb-3">
        <div class="section-title mb-2"><i class="fa-solid fa-qrcode me-2"></i>UPI Details</div>
        <div class="mb-2"><label class="form-label">UPI ID</label><input class="form-control" name="upi_id" value="{{ $settings->upi_id }}"></div>
        <div class="mb-2"><label class="form-label">Account Holder Name</label><input class="form-control" name="upi_holder" value="{{ $settings->upi_holder }}"></div>
        <div class="mb-2"><label class="form-label">QR Code Upload</label><input type="file" class="form-control" name="qr" accept="image/*"></div>
        <div class="qr-box">
          @if($settings->qr_path)
            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($settings->qr_path) }}" alt="Payment QR code">
          @endif
        </div>
      </div>
      <div class="card-flat p-3">
        <div class="section-title mb-2"><i class="fa-solid fa-university me-2"></i>Bank Details</div>
        <div class="mb-2"><label class="form-label">Bank Name</label><input class="form-control" name="bank_name" value="{{ $settings->bank_name }}"></div>
        <div class="mb-2"><label class="form-label">Account Holder Name</label><input class="form-control" name="bank_holder" value="{{ $settings->bank_holder }}"></div>
        <div class="mb-2"><label class="form-label">Account Number</label><input class="form-control" name="account_no" value="{{ $settings->account_no }}"></div>
        <div class="mb-2"><label class="form-label">IFSC Code</label><input class="form-control" name="ifsc" value="{{ $settings->ifsc }}"></div>
        <div class="mb-2"><label class="form-label">Branch Name</label><input class="form-control" name="branch" value="{{ $settings->branch }}"></div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card-flat p-3">
        <div class="section-title mb-2"><i class="fa-solid fa-message me-2"></i>Payment Instructions</div>
        <textarea class="form-control" name="instructions" rows="6">{{ $settings->instructions }}</textarea>
        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-primary-fin" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Save Settings</button>
          <a class="btn btn-outline-fin" href="{{ route('customer.pay') }}" target="_blank"><i class="fa-solid fa-eye me-1"></i>Preview Customer Screen</a>
        </div>
      </div>
    </div>
  </div>
</form>
</x-app-layout>
