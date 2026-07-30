@php
    $c = $loan->customer;
    $sanctionNo = 'BPF/SL/'.$loan->created_at->year.'/'.str_pad($loan->id, 6, '0', STR_PAD_LEFT);
@endphp
<x-guest-layout title="Loan Sanction Letter">
<div style="background:#eef1f5; min-height:100vh; padding:24px;">
  <div class="mx-auto" style="max-width:760px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold brand-text mb-0">Loan Sanction Letter</h5>
      <div class="d-flex gap-2">
        @if($canUploadSigned)
          <button class="btn btn-outline-fin btn-sm" data-bs-toggle="modal" data-bs-target="#modalUploadSigned"><i class="fa-solid fa-upload me-1"></i>Upload Signed Copy</button>
        @endif
        <button class="btn btn-primary-fin btn-sm" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print</button>
      </div>
    </div>

    <div id="printArea" class="print-area" style="border-radius:10px;">
      @include('documents._letterhead')
      <h5 class="text-center fw-bold mt-2" style="color:#0c2053;">LOAN SANCTION LETTER</h5>
      <p class="text-center small-note" style="font-size:.8rem;">Sanction Letter No: {{ $sanctionNo }}</p>
      <p><strong>Customer Name:</strong> {{ $c->user->name }}<br>
         <strong>Customer ID:</strong> {{ $c->customer_code }} &nbsp; | &nbsp; <strong>Loan Account Number:</strong> {{ $loan->loan_account_no }}<br>
         <strong>Address:</strong> {{ $c->address }}, {{ $c->city }}, {{ $c->state }} - {{ $c->pin }}</p>
      <p>Dear {{ explode(' ', $c->user->name)[0] }}, we are pleased to inform you that your loan request has been sanctioned on the following terms:</p>
      <table class="table table-sm table-bordered mt-2" style="max-width:560px;">
        <tbody>
          <tr><td>Loan Purpose</td><td class="text-end">{{ $loan->purpose }}</td></tr>
          <tr><td>Loan Amount (Principal)</td><td class="text-end">₹{{ number_format($loan->principal) }}</td></tr>
          <tr><td>Total Interest</td><td class="text-end">₹{{ number_format($loan->interest) }}</td></tr>
          <tr><td>Processing Fee</td><td class="text-end">₹{{ number_format($loan->processing_fee) }}</td></tr>
          <tr><td><strong>Total Payable</strong></td><td class="text-end"><strong>₹{{ number_format($loan->total_payable) }}</strong></td></tr>
          <tr><td>EMI Amount</td><td class="text-end">₹{{ number_format($loan->emi_amount) }}</td></tr>
          <tr><td>Number of EMIs</td><td class="text-end">{{ $loan->num_emis }} ({{ $loan->frequency }})</td></tr>
          <tr><td>First EMI Due Date</td><td class="text-end">{{ $loan->first_due_date->format('d/m/Y') }}</td></tr>
        </tbody>
      </table>
      <p class="fw-bold mb-1" style="font-size:.85rem;">Terms &amp; Conditions</p>
      <ol style="font-size:.78rem; color:#444;">
        <li>EMIs must be paid on or before the due date via UPI, QR code or bank transfer as per Admin instructions.</li>
        <li>A late fee of ₹{{ number_format($loan->late_fee) }} is applicable per EMI on delayed payment.</li>
        <li>Payments are manually verified by BluePeak Fintech Admin within 24 hours of screenshot submission.</li>
        <li>Foreclosure and part-payment requests may be raised through the assigned shop owner.</li>
        <li>This sanction letter is issued for the purpose stated above and is non-transferable.</li>
      </ol>
      <div class="row mt-4">
        <div class="col-4"><div class="sign-box">Customer Signature<br><span style="font-size:.75rem;">{{ $c->user->name }}</span></div></div>
        <div class="col-4"><div class="sign-box">Shop Owner Signature<br><span style="font-size:.75rem;">{{ $loan->shopOwner->user->name ?? '-' }}</span></div></div>
        <div class="col-4"><div class="sign-box">Authorised Signatory<br><span style="font-size:.75rem;">BluePeak Fintech</span></div></div>
      </div>
    </div>

    <div class="text-center mt-3"><a href="{{ url()->previous() }}" class="small-note">Close</a></div>
  </div>
</div>

@if($canUploadSigned)
  @include('documents._upload-signed-modal', ['loan' => $loan, 'type' => 'sanction_letter'])
@endif
</x-guest-layout>
