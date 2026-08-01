<x-customer-layout :title="$title" :active="$active">
<a class="btn btn-sm btn-outline-fin mb-3" href="{{ route('customer.products.index') }}"><i class="fa-solid fa-arrow-left me-1"></i>Continue Shopping</a>

@forelse($items as $item)
  <div class="card-flat p-3 mb-2 d-flex flex-row gap-3 align-items-center">
    @if($item->product->image_path)
      <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($item->product->image_path) }}" alt="{{ $item->product->name }}" style="width:64px;height:64px;object-fit:cover;border-radius:8px;">
    @else
      <div class="d-flex align-items-center justify-content-center bg-primary-subtle" style="width:64px;height:64px;border-radius:8px;"><i class="fa-solid fa-box text-primary"></i></div>
    @endif
    <div class="flex-grow-1">
      <div class="fw-semibold" style="font-size:.9rem;">{{ $item->product->name }}</div>
      <div class="small-note">₹{{ number_format($item->product->price, 2) }} each</div>
      <div class="d-flex align-items-center gap-2 mt-1">
        <form method="POST" action="{{ route('customer.cart.update', $item) }}" class="d-flex align-items-center gap-1">
          @csrf
          <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="form-control form-control-sm" style="width:60px;" onchange="this.form.submit()">
        </form>
        <form method="POST" action="{{ route('customer.cart.remove', $item) }}">
          @csrf @method('DELETE')
          <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
        </form>
      </div>
    </div>
    <div class="fw-bold">₹{{ number_format($item->lineTotal(), 2) }}</div>
  </div>
@empty
  <div class="card-flat p-4 text-center text-muted-fin">Your cart is empty. <a href="{{ route('customer.products.index') }}">Browse products</a>.</div>
@endforelse

@if($items->isNotEmpty())
  <div class="card-flat p-3 mb-3 d-flex justify-content-between align-items-center">
    <span class="text-muted-fin fw-semibold">Total</span>
    <span class="fw-bold fs-5">₹{{ number_format($total, 2) }}</span>
  </div>

  <div class="card-flat p-3">
    <div class="section-title mb-2">Checkout</div>
    <form method="POST" action="{{ route('customer.cart.checkout') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Delivery Address</label>
        <textarea class="form-control" name="delivery_address" rows="3" required>{{ old('delivery_address') }}</textarea>
      </div>
      <div class="mb-3">
        <label class="form-label d-block">Payment Method</label>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="payment_method" id="payCod" value="cod" checked onchange="toggleEmiFields()">
          <label class="form-check-label" for="payCod"><i class="fa-solid fa-money-bill-wave me-1"></i>Cash on Delivery</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="payment_method" id="payRazorpay" value="razorpay" {{ $razorpayEnabled ? '' : 'disabled' }} onchange="toggleEmiFields()">
          <label class="form-check-label" for="payRazorpay">
            <i class="fa-solid fa-credit-card me-1"></i>Pay Online (Razorpay)
            @if(! $razorpayEnabled)<span class="small-note">— currently unavailable</span>@endif
          </label>
        </div>
        @if($financeableSingleItem)
          <div class="form-check">
            <input class="form-check-input" type="radio" name="payment_method" id="payEmi" value="emi_financing" onchange="toggleEmiFields()">
            <label class="form-check-label" for="payEmi"><i class="fa-solid fa-file-invoice-dollar me-1"></i>EMI Financing</label>
          </div>
        @endif
      </div>

      @if($financeableSingleItem)
        <div id="emiFields" class="d-none mb-3 p-2" style="background:var(--bs-light);border-radius:8px;">
          <div class="mb-2">
            <label class="form-label">Down Payment (min ₹{{ number_format($financeableSingleItem->product->down_payment, 2) }})</label>
            <input type="number" step="0.01" class="form-control" name="down_payment" value="{{ old('down_payment', $financeableSingleItem->product->down_payment) }}" min="{{ $financeableSingleItem->product->down_payment }}">
          </div>
          <div class="mb-0">
            <label class="form-label">Number of Installments</label>
            <select class="form-select" name="num_installments">
              @foreach([3,6,9,12,18,24] as $n)
                <option value="{{ $n }}" {{ (int) old('num_installments', 6) === $n ? 'selected' : '' }}>{{ $n }} months</option>
              @endforeach
            </select>
          </div>
          <div class="small-note mt-2">Your financing application will be reviewed by Admin before approval.</div>
        </div>
      @endif

      <button class="btn btn-primary-fin w-100" type="submit"><i class="fa-solid fa-bag-shopping me-1"></i>Place Order</button>
    </form>
  </div>
@endif

@push('scripts')
<script>
function toggleEmiFields(){
  var emiRadio = document.getElementById('payEmi');
  var fields = document.getElementById('emiFields');
  if(!fields) return;
  fields.classList.toggle('d-none', !(emiRadio && emiRadio.checked));
}
</script>
@endpush
</x-customer-layout>
