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

  @foreach($order->items as $item)
    <div class="dc-row"><span class="text-muted-fin">{{ $item->product->name ?? 'Product' }}</span><span>{{ $item->quantity }} × ₹{{ number_format($item->unit_price, 2) }}</span></div>
  @endforeach
  @if($order->down_payment_amount)
    <div class="dc-row"><span class="text-muted-fin">Down Payment</span><span>₹{{ number_format($order->down_payment_amount, 2) }}</span></div>
  @endif
  <div class="dc-row"><span class="text-muted-fin fw-semibold">Total Amount</span><span class="fw-bold">₹{{ number_format($order->total_amount, 2) }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Payment Method</span><span>{{ $order->payment_method === 'emi_financing' ? 'EMI Financing' : strtoupper($order->payment_method) }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Payment Status</span><x-status-badge :status="ucfirst($order->payment_status)" /></div>
  <div class="dc-row"><span class="text-muted-fin">Delivery Address</span><span class="text-end" style="max-width:60%;">{{ $order->delivery_address }}</span></div>

  @if($order->payment_method === 'razorpay' && $order->payment_status === 'pending')
    <a href="{{ route('customer.orders.pay', $order) }}" class="btn btn-primary-fin w-100 mt-3"><i class="fa-solid fa-lock me-1"></i>Complete Payment</a>
  @endif

  @if($order->loan)
    <hr>
    <div class="section-title mb-2">EMI Financing</div>
    <div class="dc-row"><span class="text-muted-fin">Loan Status</span><x-status-badge :status="ucfirst($order->loan->status)" /></div>
    @if($order->loan->status === 'pending')
      <div class="small-note text-warning">Your financing application is awaiting Admin approval.</div>
    @elseif($order->loan->status === 'rejected')
      <div class="small-note text-danger">Financing was rejected. {{ $order->loan->reject_reason }}</div>
    @else
      <a href="{{ route('customer.loan') }}" class="btn btn-outline-fin w-100 mt-2">View Loan / EMI Schedule</a>
    @endif
  @endif
</div>
</x-customer-layout>
