<x-customer-layout :title="$title" active="products" pageTitle="My Orders" backUrl="{{ route('customer.products.index') }}">

@forelse($orders as $o)
  <a href="{{ route('customer.orders.show', $o) }}" class="card-flat p-3 mb-2 d-block" style="color:inherit;">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <div class="fw-semibold">{{ $o->items->pluck('product.name')->filter()->join(', ') ?: 'Order' }}</div>
        <div class="small-note">{{ $o->order_no }} · {{ $o->created_at->format('d/m/Y') }}</div>
      </div>
      <x-status-badge :status="ucfirst($o->status)" />
    </div>
    <div class="d-flex justify-content-between mt-2">
      <span class="small-note">{{ $o->items->sum('quantity') }} item(s) · {{ $o->payment_method === 'emi_financing' ? 'EMI FINANCING' : strtoupper($o->payment_method) }}</span>
      <span class="fw-bold">₹{{ number_format($o->total_amount, 2) }}</span>
    </div>
  </a>
@empty
  <div class="card-flat p-4 text-center text-muted-fin">You haven't placed any orders yet.</div>
@endforelse
</x-customer-layout>
