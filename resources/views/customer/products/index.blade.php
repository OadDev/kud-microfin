<x-customer-layout :title="$title" :active="$active">

<div class="card-flat p-3 mb-3">
  <form method="GET" class="row g-2">
    <div class="col-8"><input class="form-control" name="search" value="{{ $search }}" placeholder="Search products..."></div>
    <div class="col-4">
      <select class="form-select" name="category" onchange="this.form.submit()">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
          <option value="{{ $cat->id }}" {{ (string) $selectedCategory === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
      </select>
    </div>
  </form>
</div>

<div class="row g-2">
  @forelse($products as $p)
    <div class="col-6">
      <a href="{{ route('customer.products.show', $p) }}" class="card-flat p-0 d-block overflow-hidden mb-2" style="color:inherit;">
        @if($p->image_path)
          <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($p->image_path) }}" alt="{{ $p->name }}" class="w-100" style="height:110px;object-fit:cover;">
        @else
          <div class="d-flex align-items-center justify-content-center bg-primary-subtle" style="height:110px;"><i class="fa-solid fa-box fa-2x text-primary"></i></div>
        @endif
        <div class="p-2">
          <div class="fw-semibold" style="font-size:.85rem;">{{ $p->name }}</div>
          <div class="fw-bold text-primary">₹{{ number_format($p->price, 2) }}</div>
          @if(! $p->inStock())
            <div class="small-note text-danger">Out of stock</div>
          @endif
        </div>
      </a>
    </div>
  @empty
    <div class="col-12"><div class="card-flat p-4 text-center text-muted-fin">No products available right now.</div></div>
  @endforelse
</div>
</x-customer-layout>
