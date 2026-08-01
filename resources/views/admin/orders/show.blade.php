<x-app-layout :title="$title" active="orders">
<a class="btn btn-sm btn-outline-fin mb-3" href="{{ route('admin.orders.index') }}"><i class="fa-solid fa-arrow-left me-1"></i>Back to Orders</a>

<div class="card-flat p-3 p-md-4" style="max-width:640px;">
  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <div class="fw-bold fs-5">{{ $order->order_no }}</div>
      <div class="small-note">Placed {{ $order->created_at->format('d/m/Y H:i') }}</div>
    </div>
    <x-status-badge :status="ucfirst($order->status)" />
  </div>

  <div class="dc-row"><span class="text-muted-fin">Customer</span><span>{{ $order->customer->user->name }} ({{ $order->customer->user->mobile }})</span></div>
  @foreach($order->items as $item)
    <div class="dc-row"><span class="text-muted-fin">{{ $item->product->name ?? 'Product' }}</span><span>{{ $item->quantity }} × ₹{{ number_format($item->unit_price, 2) }}</span></div>
  @endforeach
  @if($order->down_payment_amount)
    <div class="dc-row"><span class="text-muted-fin">Down Payment</span><span>₹{{ number_format($order->down_payment_amount, 2) }}</span></div>
  @endif
  <div class="dc-row"><span class="text-muted-fin">Total Amount</span><span class="fw-bold">₹{{ number_format($order->total_amount, 2) }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Payment Method</span><span>{{ $order->payment_method === 'emi_financing' ? 'EMI Financing' : strtoupper($order->payment_method) }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Payment Status</span><x-status-badge :status="ucfirst($order->payment_status)" /></div>
  <div class="dc-row"><span class="text-muted-fin">Delivery Address</span><span class="text-end" style="max-width:60%;">{{ $order->delivery_address }}</span></div>
  @if($order->razorpay_payment_id)
    <div class="dc-row"><span class="text-muted-fin">Razorpay Payment ID</span><span>{{ $order->razorpay_payment_id }}</span></div>
  @endif

  @if($order->loan)
    <div class="dc-row"><span class="text-muted-fin">Financing Loan</span><span><a href="{{ route('admin.loan-approvals.show', $order->loan) }}">{{ $order->loan->loan_account_no }} — {{ ucfirst($order->loan->status) }}</a></span></div>
  @endif

  <hr>

  <div class="row g-2">
    <div class="col-md-8">
      <label class="form-label">Update Order Status</label>
      <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="d-flex gap-2">
        @csrf
        <select class="form-select" name="status">
          @foreach(['pending','confirmed','shipped','delivered','cancelled'] as $s)
            <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
        <button class="btn btn-primary-fin" type="submit"><i class="fa-solid fa-check"></i></button>
      </form>
    </div>
    @if($order->payment_method === 'cod' && $order->payment_status === 'pending')
    <div class="col-md-4 d-flex align-items-end">
      <form method="POST" action="{{ route('admin.orders.mark-cod-paid', $order) }}" class="w-100">
        @csrf
        <button class="btn btn-outline-fin w-100" type="submit" data-confirm="Mark this COD order as paid (cash collected)?"><i class="fa-solid fa-indian-rupee-sign me-1"></i>Mark Cash Collected</button>
      </form>
    </div>
    @endif
  </div>
</div>
</x-app-layout>
