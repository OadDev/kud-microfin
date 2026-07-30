@php $c = $loan->customer; @endphp
<x-guest-layout title="Welcome Letter">
<div style="background:#eef1f5; min-height:100vh; padding:24px;">
  <div class="mx-auto" style="max-width:760px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold brand-text mb-0">Welcome Letter</h5>
      <div class="d-flex gap-2">
        @if($canUploadSigned)
          <button class="btn btn-outline-fin btn-sm" data-bs-toggle="modal" data-bs-target="#modalUploadSigned"><i class="fa-solid fa-upload me-1"></i>Upload Signed Copy</button>
        @endif
        <button class="btn btn-primary-fin btn-sm" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print</button>
      </div>
    </div>

    <div id="printArea" class="print-area" style="border-radius:10px;">
      @include('documents._letterhead')
      <p>To,<br><strong>{{ $c->user->name }}</strong><br>{{ $c->address }}, {{ $c->city }}, {{ $c->state }} - {{ $c->pin }}</p>
      <p><strong>Customer ID:</strong> {{ $c->customer_code }} &nbsp; | &nbsp; <strong>Loan Account Number:</strong> {{ $loan->loan_account_no }}</p>
      <p>Dear {{ explode(' ', $c->user->name)[0] }},</p>
      <p>Welcome to BluePeak Fintech! We are pleased to inform you that your loan application has been processed and your loan account has been successfully created. We thank you for choosing us as your trusted microfinance partner.</p>
      <table class="table table-sm table-bordered mt-3" style="max-width:520px;">
        <tbody>
          <tr><td>Loan Amount (Principal)</td><td class="text-end">₹{{ number_format($loan->principal) }}</td></tr>
          <tr><td>Total Payable</td><td class="text-end">₹{{ number_format($loan->total_payable) }}</td></tr>
          <tr><td>EMI Amount</td><td class="text-end">₹{{ number_format($loan->emi_amount) }}</td></tr>
          <tr><td>Number of EMIs</td><td class="text-end">{{ $loan->num_emis }} ({{ $loan->frequency }})</td></tr>
          <tr><td>First EMI Due Date</td><td class="text-end">{{ $loan->first_due_date->format('d/m/Y') }}</td></tr>
        </tbody>
      </table>
      <p>Please ensure timely payment of your EMIs to maintain a good credit history. For any queries, feel free to reach out to your assigned shop owner or our support team.</p>
      <div class="row mt-5">
        <div class="col-6"><div class="sign-box">Authorised Signatory<br><span style="font-size:.75rem;">BluePeak Fintech</span></div></div>
        <div class="col-6"><div class="sign-box">Customer Signature<br><span style="font-size:.75rem;">{{ $c->user->name }}</span></div></div>
      </div>
    </div>

    <div class="text-center mt-3"><a href="{{ url()->previous() }}" class="small-note">Close</a></div>
  </div>
</div>

@if($canUploadSigned)
  @include('documents._upload-signed-modal', ['loan' => $loan, 'type' => 'welcome_letter'])
@endif
</x-guest-layout>
