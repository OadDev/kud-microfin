<x-app-layout :title="$title" :active="$active">
<div class="mb-3">
  <div class="section-title mb-0">EMI Calculator</div>
  <div class="page-sub">Quote a financed device purchase for a customer</div>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <form method="GET" class="card-flat p-3">
      <div class="mb-2"><label class="form-label">Device Price (₹)</label><input type="number" step="0.01" min="0" class="form-control" name="device_price" value="{{ $input['device_price'] }}" required></div>
      <div class="mb-2"><label class="form-label">Down Payment (₹)</label><input type="number" step="0.01" min="0" class="form-control" name="down_payment" value="{{ $input['down_payment'] }}" required></div>
      <div class="mb-2"><label class="form-label">Processing Fee (₹)</label><input type="number" step="0.01" min="0" class="form-control" name="processing_fee" value="{{ $input['processing_fee'] }}"></div>
      <div class="mb-3"><label class="form-label">Number of Installments</label><input type="number" min="1" class="form-control" name="installments" value="{{ $input['installments'] }}" required></div>
      <button class="btn btn-primary-fin w-100" type="submit"><i class="fa-solid fa-calculator me-1"></i>Calculate</button>
    </form>
  </div>

  <div class="col-lg-7">
    @if($quote)
      <div class="card-flat p-3">
        <div class="section-title mb-3">Quote Summary</div>
        <div class="dc-row"><span class="text-muted-fin">Device Price</span><span>₹{{ number_format($quote['device_price'], 2) }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Down Payment</span><span>₹{{ number_format($quote['down_payment'], 2) }}</span></div>
        <div class="dc-row"><span class="text-muted-fin fw-semibold">Loan Amount</span><span class="fw-semibold">₹{{ number_format($quote['loan_amount'], 2) }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Processing Fee</span><span>₹{{ number_format($quote['processing_fee'], 2) }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Total Payable</span><span>₹{{ number_format($quote['total_payable'], 2) }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Number of Installments</span><span>{{ $quote['num_installments'] }}</span></div>
        <hr>
        <div class="d-flex justify-content-between align-items-center">
          <span class="fw-bold">Monthly Installment</span>
          <span class="fw-bold text-primary fs-5">₹{{ number_format($quote['installment'], 2) }}</span>
        </div>
      </div>
    @else
      <div class="card-flat p-4 text-center text-muted-fin">Enter the device price, down payment and number of installments to see the quote.</div>
    @endif
  </div>
</div>
</x-app-layout>
