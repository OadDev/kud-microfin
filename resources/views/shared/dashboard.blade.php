<x-app-layout :title="$title" :active="$active">

@if($isAdmin)
  @php
    $cards = [
      ['label' => 'Total Shop Owners', 'value' => $stats['total_shop_owners'], 'icon' => 'fa-store', 'color' => 'var(--primary)', 'bg' => 'var(--primary-light)'],
      ['label' => 'Pending Shop Owners', 'value' => $stats['pending_shop_owners'], 'icon' => 'fa-hourglass-half', 'color' => 'var(--orange)', 'bg' => 'var(--orange-bg)'],
      ['label' => 'Total Customers', 'value' => $stats['total_customers'], 'icon' => 'fa-users', 'color' => 'var(--primary)', 'bg' => 'var(--primary-light)'],
      ['label' => 'Active Loans', 'value' => $stats['active_loans'], 'icon' => 'fa-file-invoice-dollar', 'color' => 'var(--green)', 'bg' => 'var(--green-bg)'],
      ['label' => 'Total Outstanding', 'value' => '₹'.number_format($stats['total_outstanding']), 'icon' => 'fa-indian-rupee-sign', 'color' => 'var(--red)', 'bg' => 'var(--red-bg)'],
      ['label' => 'EMI Collected', 'value' => '₹'.number_format($stats['emi_collected']), 'icon' => 'fa-sack-dollar', 'color' => 'var(--green)', 'bg' => 'var(--green-bg)'],
      ['label' => "Collected Today", 'value' => '₹'.number_format($stats['emi_collected_today']), 'icon' => 'fa-calendar-check', 'color' => 'var(--green)', 'bg' => 'var(--green-bg)'],
      ['label' => 'Pending Verifications', 'value' => $stats['pending_verifications'], 'icon' => 'fa-magnifying-glass-dollar', 'color' => 'var(--orange)', 'bg' => 'var(--orange-bg)'],
      ['label' => 'Overdue EMIs', 'value' => $stats['overdue_emis'], 'icon' => 'fa-triangle-exclamation', 'color' => 'var(--red)', 'bg' => 'var(--red-bg)'],
    ];
  @endphp
@else
  @php
    $cards = [
      ['label' => 'Total Customers', 'value' => $stats['total_customers'], 'icon' => 'fa-users', 'color' => 'var(--primary)', 'bg' => 'var(--primary-light)'],
      ['label' => 'Awaiting Approval', 'value' => $stats['pending_loan_approvals'], 'icon' => 'fa-hourglass-half', 'color' => 'var(--orange)', 'bg' => 'var(--orange-bg)'],
      ['label' => 'Pending Documents', 'value' => $stats['pending_documents'], 'icon' => 'fa-file-lines', 'color' => 'var(--orange)', 'bg' => 'var(--orange-bg)'],
    ];
  @endphp
@endif

<div class="row g-3 mb-3">
  @foreach($cards as $c)
    <div class="col-6 col-lg-3">
      <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon" style="background:{{ $c['bg'] }}; color:{{ $c['color'] }};"><i class="fa-solid {{ $c['icon'] }}"></i></div>
        <div class="min-w-0">
          <div class="stat-value text-truncate">{{ $c['value'] }}</div>
          <div class="stat-label text-truncate">{{ $c['label'] }}</div>
        </div>
      </div>
    </div>
  @endforeach
</div>

@if(!$isAdmin)
<div class="d-flex justify-content-end mb-3">
  <a class="btn btn-primary-fin btn-sm" href="{{ route('shopowner.customers.create') }}"><i class="fa-solid fa-user-plus me-1"></i>Create New Customer</a>
</div>
@endif

@if($isAdmin)
<div class="card-flat p-3 mb-3">
  <div class="section-title mb-2"><i class="fa-solid fa-chart-column me-2"></i>EMI Collection (Last 6 Months)</div>
  <div style="height:260px;"><canvas id="emiCollectionChart"></canvas></div>
</div>
@endif

<div class="row g-3">
  <div class="col-lg-{{ $isAdmin ? 4 : 12 }}">
    <div class="card-flat p-3 h-100">
      <div class="section-title mb-2"><i class="fa-solid fa-users me-2"></i>Recent Customers</div>
      @forelse($customers as $c)
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <div class="min-w-0">
            <div class="fw-semibold text-truncate">{{ $c->user->name }}</div>
            @if($isAdmin)
              <div class="small-note">{{ $c->currentLoan()?->loan_account_no }} · ₹{{ number_format($c->currentLoan()?->principal ?? 0) }}</div>
            @else
              <div class="small-note">{{ $c->customer_code }}</div>
            @endif
          </div>
          @if($isAdmin)
            <x-status-badge :status="ucfirst($c->currentLoan()?->status ?? 'active')" />
          @else
            <a class="btn btn-sm btn-outline-fin" href="{{ route('customers.show', $c) }}">View</a>
          @endif
        </div>
      @empty
        <div class="small-note">No customers yet.</div>
      @endforelse
    </div>
  </div>
  @if($isAdmin)
  <div class="col-lg-4">
    <div class="card-flat p-3 h-100">
      <div class="section-title mb-2"><i class="fa-solid fa-calendar-day me-2"></i>Upcoming EMI Due</div>
      @forelse($upcomingEmis as $row)
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <div class="min-w-0">
            <div class="fw-semibold text-truncate">{{ $row->loan->customer->user->name }}</div>
            <div class="small-note">₹{{ number_format($row->emi->amount) }} · Due {{ $row->emi->due_date->format('d/m/Y') }}</div>
          </div>
          <x-status-badge :status="$row->emi->displayStatus()" />
        </div>
      @empty
        <div class="small-note">No upcoming EMIs.</div>
      @endforelse
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card-flat p-3 h-100">
      <div class="section-title mb-2"><i class="fa-solid fa-magnifying-glass-dollar me-2"></i>Pending Payment Verification</div>
      @forelse($pendingVerifications as $p)
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <div class="min-w-0">
            <div class="fw-semibold text-truncate">{{ $p->customer->user->name }}</div>
            <div class="small-note">{{ $p->is_foreclosure ? 'Loan Foreclosure' : ($p->emi_id ? 'EMI #'.$p->emi->emi_number : '') }} · ₹{{ number_format($p->paid_amount) }}</div>
          </div>
          <a class="btn btn-sm btn-outline-fin" href="{{ route('admin.payment-verification.show', $p) }}">View</a>
        </div>
      @empty
        <div class="small-note">No pending verifications.</div>
      @endforelse
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card-flat p-3 h-100">
      <div class="section-title mb-2 d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-calendar-check me-2"></i>EMI Collections Today</span>
        <span class="fw-bold text-primary">₹{{ number_format($stats['emi_collected_today']) }}</span>
      </div>
      @forelse($emisCollectedToday as $row)
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <div class="min-w-0">
            <div class="fw-semibold text-truncate">{{ $row->loan->customer->user->name }}</div>
            <div class="small-note">EMI #{{ $row->emi->emi_number }} · {{ $row->loan->loan_account_no }} · {{ $row->emi->updated_at->format('h:i A') }}</div>
          </div>
          <span class="fw-semibold">₹{{ number_format($row->emi->amount) }}</span>
        </div>
      @empty
        <div class="small-note">No EMIs collected yet today.</div>
      @endforelse
    </div>
  </div>
  @endif
</div>

@if($isAdmin)
@push('scripts')
<script>
new Chart(document.getElementById('emiCollectionChart'), {
  type: 'bar',
  data: {
    labels: @json($chartLabels),
    datasets: [{ label: 'EMI Collected (₹)', data: @json($chartData), backgroundColor: '#0b5cc5', borderRadius: 6, maxBarThickness: 38 }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { callback: v => '₹' + (v/1000) + 'k' } } }
  }
});
</script>
@endpush
@endif
</x-app-layout>
