<x-customer-layout :title="$title" :active="$active" pageTitle="Favourites" backUrl="{{ route('customer.products.index') }}">

<div class="row g-2">
  @forelse($favourites as $fav)
    <div class="col-6">
      <div class="product-card mb-2">
        <a href="{{ route('customer.products.show', $fav->product) }}" style="color:inherit;">
          @if($fav->product->image_path)
            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($fav->product->image_path) }}" alt="{{ $fav->product->name }}" class="w-100" style="height:110px;object-fit:cover;">
          @else
            <div class="product-thumb-placeholder d-flex align-items-center justify-content-center bg-primary-subtle" style="height:110px;"><i class="fa-solid fa-box fa-2x text-primary"></i></div>
          @endif
          <div class="p-2">
            <div class="fw-semibold" style="font-size:.85rem;">{{ $fav->product->name }}</div>
            <div class="fw-bold text-primary">₹{{ number_format($fav->product->price, 2) }}</div>
          </div>
        </a>
        <form method="POST" action="{{ route('customer.favourites.toggle', $fav->product) }}">
          @csrf
          <button class="fav-toggle-btn" type="submit" title="Remove from favourites"><i class="fa-solid fa-heart text-danger"></i></button>
        </form>
      </div>
    </div>
  @empty
    <div class="col-12"><div class="card-flat p-4 text-center text-muted-fin">No favourites yet. <a href="{{ route('customer.products.index') }}">Browse products</a>.</div></div>
  @endforelse
</div>
</x-customer-layout>
