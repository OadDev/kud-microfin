@php
    $outstanding = $loan ? $loan->outstanding() : 0;
    $next = $loan?->emis->first(fn($e) => $e->status !== 'paid');
    $progress = $loan ? round(($loan->amountPaid() / $loan->total_payable) * 100) : 0;
@endphp
<x-customer-layout :title="$title" :active="$active">

@include('partials.banner-carousel', ['banners' => $banners, 'carouselId' => 'homeBannerCarousel', 'bannerHeight' => '140px'])

<div class="card-flat p-3 mb-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div>
      <div class="small-note">Loan Account</div>
      <div class="fw-bold">{{ $loan?->loan_account_no ?? '-' }}</div>
    </div>
    <x-status-badge :status="ucfirst($loan?->status ?? 'active')" />
  </div>
  <div class="row text-center g-2 mb-2">
    <div class="col-6"><div class="small-note">Total Payable</div><div class="fw-bold">₹{{ number_format($loan?->total_payable ?? 0) }}</div></div>
    <div class="col-6"><div class="small-note">Outstanding</div><div class="fw-bold text-danger">₹{{ number_format($outstanding) }}</div></div>
    <div class="col-6"><div class="small-note">Amount Paid</div><div class="fw-bold text-success">₹{{ number_format($loan?->amountPaid() ?? 0) }}</div></div>
    <div class="col-6"><div class="small-note">EMI Amount</div><div class="fw-bold">₹{{ number_format($loan?->emi_amount ?? 0) }}</div></div>
  </div>
  <div class="d-flex justify-content-between small-note mb-1"><span>Loan Progress</span><span>{{ $progress }}%</span></div>
  <div class="progress progress-fin"><div class="progress-bar bg-success" style="width:{{ $progress }}%;"></div></div>
  <div class="small-note mt-2"><i class="fa-solid fa-calendar-day me-1"></i>Next EMI Due: <strong>{{ $next?->due_date->format('d/m/Y') ?? 'No dues' }}</strong></div>
</div>

<div class="row g-2 mb-3">
  <div class="col-3"><a class="qtile" href="{{ route('customer.pay') }}"><i class="fa-solid fa-indian-rupee-sign"></i><span>Pay Now</span></a></div>
  <div class="col-3"><a class="qtile" href="{{ route('customer.loan') }}"><i class="fa-solid fa-calendar-check"></i><span>EMI Schedule</span></a></div>
  @if($loan)
    <div class="col-3"><a class="qtile" href="{{ route('documents.show', [$loan, 'sanction_letter']) }}" target="_blank"><i class="fa-solid fa-file-signature"></i><span>Sanction Letter</span></a></div>
  @else
    <div class="col-3"><span class="qtile text-muted-fin"><i class="fa-solid fa-file-signature"></i><span>Sanction Letter</span></span></div>
  @endif
  <div class="col-3"><a class="qtile" href="{{ route('customer.documents') }}"><i class="fa-solid fa-file-lines"></i><span>Statement</span></a></div>
</div>

<div class="card-flat p-3 mb-3">
  <div class="section-title mb-2">Recent Payments</div>
  @forelse($recentPayments as $p)
    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
      <div><div class="fw-semibold">{{ $p->is_foreclosure ? 'Loan Foreclosure' : 'EMI #'.$p->emi->emi_number }}</div><div class="small-note">{{ $p->submitted_at->format('d/m/Y') }} · {{ $p->method }}</div></div>
      <div class="text-end"><div class="fw-semibold">₹{{ number_format($p->paid_amount) }}</div><x-status-badge :status="ucfirst(str_replace('_',' ',$p->status))" /></div>
    </div>
  @empty
    <div class="small-note">No payments yet.</div>
  @endforelse
</div>
</x-customer-layout>
