<x-customer-layout :title="$title" active="products">
<a class="btn btn-sm btn-outline-fin mb-3" href="{{ route('customer.products.index') }}"><i class="fa-solid fa-arrow-left me-1"></i>Back to Shop</a>

<div class="card-flat p-0 overflow-hidden mb-3">
  @if($product->image_path)
    <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}" class="w-100" style="max-height:220px;object-fit:cover;">
  @else
    <div class="d-flex align-items-center justify-content-center bg-primary-subtle" style="height:180px;"><i class="fa-solid fa-box fa-3x text-primary"></i></div>
  @endif
  <div class="p-3">
    <div class="small-note">{{ $product->category?->name }}</div>
    <div class="fw-bold fs-5">{{ $product->name }}</div>
    <div class="fw-bold text-primary fs-5 mb-2">₹{{ number_format($product->price, 2) }}</div>
    @if($product->description)
      <div class="small-note">{{ $product->description }}</div>
    @endif
    <div class="mt-2">
      @if($product->inStock())
        <x-status-badge status="In Stock" />
      @else
        <x-status-badge status="Out of Stock" />
      @endif
    </div>
  </div>
</div>

@if($product->inStock())
<div class="card-flat p-3">
  <div class="section-title mb-2">Buy Now</div>
  <form method="POST" action="{{ route('customer.products.buy-now', $product) }}">
    @csrf
    <div class="mb-2">
      <label class="form-label">Quantity</label>
      <input type="number" class="form-control" name="quantity" value="{{ old('quantity', 1) }}" min="1" @if($product->stock_quantity !== null) max="{{ $product->stock_quantity }}" @endif required>
    </div>
    <div class="mb-3">
      <label class="form-label">Delivery Address</label>
      <textarea class="form-control" name="delivery_address" rows="3" required>{{ old('delivery_address') }}</textarea>
    </div>
    <div class="mb-3">
      <label class="form-label d-block">Payment Method</label>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="payment_method" id="payCod" value="cod" checked>
        <label class="form-check-label" for="payCod"><i class="fa-solid fa-money-bill-wave me-1"></i>Cash on Delivery</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="payment_method" id="payRazorpay" value="razorpay" {{ $razorpayEnabled ? '' : 'disabled' }}>
        <label class="form-check-label" for="payRazorpay">
          <i class="fa-solid fa-credit-card me-1"></i>Pay Online (Razorpay)
          @if(! $razorpayEnabled)<span class="small-note">— currently unavailable</span>@endif
        </label>
      </div>
    </div>
    <button class="btn btn-primary-fin w-100" type="submit"><i class="fa-solid fa-bag-shopping me-1"></i>Place Order</button>
  </form>
</div>
@endif
</x-customer-layout>
