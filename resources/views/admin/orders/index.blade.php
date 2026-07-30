<x-app-layout :title="$title" :active="$active">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <div class="section-title mb-0">Orders</div>
    <div class="page-sub">Product purchases placed by customers (COD &amp; Razorpay)</div>
  </div>
</div>

<div class="card-flat p-3 mb-3">
  <form method="GET" class="row g-2">
    <div class="col-md-4">
      <select class="form-select" name="status" onchange="this.form.submit()">
        <option value="" {{ $status==='All'?'selected':'' }}>All Statuses</option>
        @foreach(['pending','confirmed','shipped','delivered','cancelled'] as $s)
          <option value="{{ $s }}" {{ $status===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
    </div>
  </form>
</div>

<div class="card-flat p-0 table-responsive-fin">
  <table class="table table-fin mb-0">
    <thead><tr><th>Order No.</th><th>Customer</th><th>Product</th><th>Qty</th><th>Amount</th><th>Payment</th><th>Status</th><th>Placed</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($orders as $o)
      <tr>
        <td class="fw-semibold">{{ $o->order_no }}</td>
        <td>{{ $o->customer->user->name }}</td>
        <td>{{ $o->product->name }}</td>
        <td>{{ $o->quantity }}</td>
        <td>₹{{ number_format($o->total_amount, 2) }}</td>
        <td>
          {{ strtoupper($o->payment_method) }}
          <x-status-badge :status="ucfirst($o->payment_status)" />
        </td>
        <td><x-status-badge :status="ucfirst($o->status)" /></td>
        <td>{{ $o->created_at->format('d/m/Y') }}</td>
        <td><a class="btn btn-sm btn-outline-fin" href="{{ route('admin.orders.show', $o) }}"><i class="fa-solid fa-eye"></i></a></td>
      </tr>
    @empty
      <tr><td colspan="9" class="text-center text-muted-fin py-3">No orders yet.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="data-cards">
  @forelse($orders as $o)
    <div class="data-card">
      <div class="dc-head"><div><div class="fw-bold">{{ $o->order_no }}</div><div class="small-note">{{ $o->customer->user->name }}</div></div><x-status-badge :status="ucfirst($o->status)" /></div>
      <div class="dc-row"><span class="dc-label">Product</span><span>{{ $o->product->name }} × {{ $o->quantity }}</span></div>
      <div class="dc-row"><span class="dc-label">Amount</span><span>₹{{ number_format($o->total_amount, 2) }}</span></div>
      <div class="dc-row"><span class="dc-label">Payment</span><span>{{ strtoupper($o->payment_method) }} · {{ ucfirst($o->payment_status) }}</span></div>
      <a class="btn btn-sm btn-outline-fin w-100 mt-2" href="{{ route('admin.orders.show', $o) }}">View &amp; Update</a>
    </div>
  @empty
    <div class="text-center text-muted-fin py-3">No orders yet.</div>
  @endforelse
</div>
</x-app-layout>
