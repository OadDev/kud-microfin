<x-customer-layout :title="$title" active="products" pageTitle="{{ $product->name }}" backUrl="{{ route('customer.products.index') }}">

<div class="card-flat p-0 overflow-hidden mb-3">
  @if($product->images->isNotEmpty())
    <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        @foreach($product->images as $i => $img)
          <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($img->path) }}" class="w-100" style="max-height:260px;object-fit:cover;" alt="{{ $product->name }}">
          </div>
        @endforeach
      </div>
      @if($product->images->count() > 1)
        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
      @endif
    </div>
  @elseif($product->image_path)
    <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}" class="w-100" style="max-height:220px;object-fit:cover;">
  @else
    <div class="d-flex align-items-center justify-content-center bg-primary-subtle" style="height:180px;"><i class="fa-solid fa-box fa-3x text-primary"></i></div>
  @endif
  <div class="p-3">
    <div class="small-note">{{ $product->category?->name }} @if($product->brand) · {{ $product->brand }} @endif</div>
    <div class="fw-bold fs-5">{{ $product->name }}</div>
    <div class="fw-bold text-primary fs-5 mb-2">₹{{ number_format($product->price, 2) }}</div>

    <div class="d-flex flex-wrap gap-2 mb-2">
      @if($product->storage)<span class="badge text-bg-light border">{{ $product->storage }}</span>@endif
      @if($product->ram)<span class="badge text-bg-light border">{{ $product->ram }} RAM</span>@endif
      @if($product->network_type)<span class="badge text-bg-light border">{{ str_replace('_', ' & ', $product->network_type) }}</span>@endif
    </div>

    @if($product->description)
      <div class="small-note">{{ $product->description }}</div>
    @endif

    @if($product->video_path)
      <video controls preload="metadata" class="w-100 mt-2" style="max-height:260px;border-radius:10px;background:#000;">
        <source src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($product->video_path) }}">
        Your browser doesn't support embedded video.
      </video>
    @elseif($product->video_url)
      <a href="{{ $product->video_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-fin mt-2"><i class="fa-solid fa-circle-play me-1"></i>Watch Video</a>
    @endif

    <div class="mt-2">
      @if($product->inStock())
        <x-status-badge status="In Stock" />
      @else
        <x-status-badge status="Out of Stock" />
      @endif
    </div>

    @if($product->financeable())
      <div class="alert alert-success mt-2 mb-0" style="font-size:.85rem;">
        <i class="fa-solid fa-file-invoice-dollar me-1"></i>EMI available — from ₹{{ number_format($product->down_payment, 2) }} down payment, ~₹{{ number_format($product->referenceMonthlyEmi(), 0) }}/month.
      </div>
    @endif
  </div>
</div>

@if($product->inStock())
<div class="card-flat p-3 d-flex flex-row gap-2">
  <form method="POST" action="{{ route('customer.cart.add', $product) }}" class="flex-grow-1">
    @csrf
    <button class="btn btn-primary-fin w-100" type="submit">
      <i class="fa-solid fa-cart-plus me-1"></i>{{ $inCart ? 'Add Another' : 'Add to Cart' }}
    </button>
  </form>
  <form method="POST" action="{{ route('customer.favourites.toggle', $product) }}">
    @csrf
    <button class="btn btn-outline-fin" type="submit" title="Toggle favourite">
      <i class="fa-solid fa-heart {{ $isFavourite ? 'text-danger' : '' }}"></i>
    </button>
  </form>
</div>
@if($inCart)
  <div class="text-center small-note mt-2">Already in your cart. <a href="{{ route('customer.cart.index') }}">View Cart</a></div>
@endif
@endif
</x-customer-layout>
