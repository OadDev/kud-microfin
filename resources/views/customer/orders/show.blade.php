<x-customer-layout :title="$title" active="products">
<a class="btn btn-sm btn-outline-fin mb-3" href="{{ route('customer.orders.index') }}"><i class="fa-solid fa-arrow-left me-1"></i>Back to My Orders</a>

<div class="card-flat p-3">
  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <div class="fw-bold">{{ $order->order_no }}</div>
      <div class="small-note">{{ $order->created_at->format('d/m/Y H:i') }}</div>
    </div>
    <x-status-badge :status="ucfirst($order->status)" />
  </div>

  <div class="dc-row"><span class="text-muted-fin">Product</span><span>{{ $order->product->name }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Quantity</span><span>{{ $order->quantity }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Unit Price</span><span>₹{{ number_format($order->unit_price, 2) }}</span></div>
  <div class="dc-row"><span class="text-muted-fin fw-semibold">Total Amount</span><span class="fw-bold">₹{{ number_format($order->total_amount, 2) }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Payment Method</span><span>{{ strtoupper($order->payment_method) }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Payment Status</span><x-status-badge :status="ucfirst($order->payment_status)" /></div>
  <div class="dc-row"><span class="text-muted-fin">Delivery Address</span><span class="text-end" style="max-width:60%;">{{ $order->delivery_address }}</span></div>

  @if($order->payment_method === 'razorpay' && $order->payment_status === 'pending')
    <a href="{{ route('customer.orders.pay', $order) }}" class="btn btn-primary-fin w-100 mt-3"><i class="fa-solid fa-lock me-1"></i>Complete Payment</a>
  @endif
</div>
</x-customer-layout>
