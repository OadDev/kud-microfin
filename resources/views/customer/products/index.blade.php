<x-customer-layout :title="$title" :active="$active">

<div class="d-flex justify-content-between align-items-center mb-2">
  <div class="section-title mb-0">Shop</div>
  <div class="d-flex gap-2">
    <a href="{{ route('customer.favourites.index') }}" class="btn btn-sm btn-outline-fin"><i class="fa-solid fa-heart"></i></a>
    <a href="{{ route('customer.cart.index') }}" class="btn btn-sm btn-outline-fin"><i class="fa-solid fa-cart-shopping"></i></a>
  </div>
</div>

<div class="card-flat p-3 mb-3">
  <form method="GET" class="row g-2">
    <div class="col-12"><input class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search products..."></div>

    <div class="col-6">
      <select class="form-select" name="category">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
          <option value="{{ $cat->id }}" {{ (string) ($filters['category'] ?? '') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-6">
      <select class="form-select" name="brand">
        <option value="">All Brands</option>
        @foreach($brands as $b)
          <option value="{{ $b }}" {{ ($filters['brand'] ?? '') === $b ? 'selected' : '' }}>{{ $b }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-6">
      <select class="form-select" name="storage">
        <option value="">Any Storage</option>
        @foreach($storages as $s)
          <option value="{{ $s }}" {{ ($filters['storage'] ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-6">
      <select class="form-select" name="ram">
        <option value="">Any RAM</option>
        @foreach($rams as $r)
          <option value="{{ $r }}" {{ ($filters['ram'] ?? '') === $r ? 'selected' : '' }}>{{ $r }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-6">
      <select class="form-select" name="network">
        <option value="">Any Network</option>
        @foreach(['3G'=>'3G','4G'=>'4G','5G'=>'5G','4G_5G'=>'4G & 5G'] as $val=>$label)
          <option value="{{ $val }}" {{ ($filters['network'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-6"><input type="number" class="form-control" name="price_max" value="{{ $filters['price_max'] ?? '' }}" placeholder="Max Price (₹)"></div>

    <div class="col-6"><input type="number" class="form-control" name="down_payment_max" value="{{ $filters['down_payment_max'] ?? '' }}" placeholder="Max Down Payment (₹)"></div>
    <div class="col-6"><input type="number" class="form-control" name="emi_max" value="{{ $filters['emi_max'] ?? '' }}" placeholder="Max Monthly EMI (₹)"></div>

    <div class="col-12 form-check">
      <input class="form-check-input" type="checkbox" name="financeable" value="1" id="filterFinanceable" {{ ! empty($filters['financeable']) ? 'checked' : '' }}>
      <label class="form-check-label" for="filterFinanceable">EMI financing available only</label>
    </div>

    <div class="col-12 d-flex gap-2">
      <button class="btn btn-primary-fin flex-grow-1" type="submit"><i class="fa-solid fa-filter me-1"></i>Apply Filters</button>
      <a href="{{ route('customer.products.index') }}" class="btn btn-outline-fin">Reset</a>
    </div>
  </form>
</div>

<div class="row g-2">
  @forelse($products as $p)
    <div class="col-6">
      <div class="card-flat p-0 overflow-hidden mb-2 position-relative">
        <a href="{{ route('customer.products.show', $p) }}" style="color:inherit;">
          @if($p->image_path)
            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($p->image_path) }}" alt="{{ $p->name }}" class="w-100" style="height:110px;object-fit:cover;">
          @else
            <div class="d-flex align-items-center justify-content-center bg-primary-subtle" style="height:110px;"><i class="fa-solid fa-box fa-2x text-primary"></i></div>
          @endif
          <div class="p-2">
            <div class="small-note">{{ $p->brand }}</div>
            <div class="fw-semibold" style="font-size:.85rem;">{{ $p->name }}</div>
            <div class="fw-bold text-primary">₹{{ number_format($p->price, 2) }}</div>
            @if($p->financeable())
              <div class="small-note text-success">EMI from ₹{{ number_format($p->referenceMonthlyEmi(), 0) }}/mo</div>
            @endif
            @if(! $p->inStock())
              <div class="small-note text-danger">Out of stock</div>
            @endif
          </div>
        </a>
        <form method="POST" action="{{ route('customer.favourites.toggle', $p) }}" class="position-absolute top-0 end-0 m-1">
          @csrf
          <button class="btn btn-sm btn-light rounded-circle shadow-sm" type="submit" title="Toggle favourite"><i class="fa-solid fa-heart {{ in_array($p->id, $favouriteIds ?? []) ? 'text-danger' : 'text-muted' }}"></i></button>
        </form>
      </div>
    </div>
  @empty
    <div class="col-12"><div class="card-flat p-4 text-center text-muted-fin">No products match your filters.</div></div>
  @endforelse
</div>
</x-customer-layout>
