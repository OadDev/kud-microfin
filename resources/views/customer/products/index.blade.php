@php
  $activeFilterCount = collect($filters)->except('search')->filter(fn ($v) => $v !== null && $v !== '')->count();
@endphp
<x-customer-layout :title="$title" :active="$active" pageTitle="Shop">
  <x-slot:pageActions>
    <a href="{{ route('customer.favourites.index') }}" class="icon-btn" title="Favourites"><i class="fa-solid fa-heart"></i></a>
  </x-slot:pageActions>

<form method="GET" id="filterForm" action="{{ route('customer.products.index') }}">
  <div class="card-flat p-2 mb-3 d-flex flex-row gap-2 align-items-center">
    <input class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search products...">
    <button type="submit" class="btn btn-outline-fin flex-shrink-0"><i class="fa-solid fa-magnifying-glass"></i></button>
    <button type="button" class="btn btn-outline-fin flex-shrink-0 position-relative" onclick="openFilters()">
      <i class="fa-solid fa-sliders"></i> Filters
      @if($activeFilterCount)
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">{{ $activeFilterCount }}</span>
      @endif
    </button>
  </div>

  <div id="filtersOverlay" class="filters-overlay">
    <div class="filters-header">
      <span class="fw-bold">Filters</span>
      <button type="button" class="btn btn-link btn-sm text-danger p-0" onclick="clearAllFilters()">CLEAR ALL</button>
    </div>
    <div class="filters-body">
      <div class="filters-nav">
        <div class="filters-nav-item active" data-target="panel-quick" onclick="showFilterPanel('panel-quick', this)">Quick Filters</div>
        <div class="filters-nav-item" data-target="panel-category" onclick="showFilterPanel('panel-category', this)">Category</div>
        <div class="filters-nav-item" data-target="panel-brand" onclick="showFilterPanel('panel-brand', this)">Brand</div>
        <div class="filters-nav-item" data-target="panel-storage" onclick="showFilterPanel('panel-storage', this)">Storage</div>
        <div class="filters-nav-item" data-target="panel-ram" onclick="showFilterPanel('panel-ram', this)">RAM</div>
        <div class="filters-nav-item" data-target="panel-network" onclick="showFilterPanel('panel-network', this)">Network</div>
        <div class="filters-nav-item" data-target="panel-price" onclick="showFilterPanel('panel-price', this)">Price Range</div>
        <div class="filters-nav-item" data-target="panel-down" onclick="showFilterPanel('panel-down', this)">Down Payment</div>
        <div class="filters-nav-item" data-target="panel-emi" onclick="showFilterPanel('panel-emi', this)">Monthly EMI</div>
      </div>
      <div class="filters-options">

        <div id="panel-quick" class="filter-panel">
          <label class="filter-option">
            <input type="checkbox" name="financeable" value="1" {{ ! empty($filters['financeable']) ? 'checked' : '' }}>
            <i class="fa-solid fa-check filter-check"></i>
            <span class="filter-label">EMI Financing Available</span>
          </label>
        </div>

        <div id="panel-category" class="filter-panel d-none">
          <label class="filter-option">
            <input type="radio" name="category" value="" {{ empty($filters['category']) ? 'checked' : '' }}>
            <i class="fa-solid fa-check filter-check"></i>
            <span class="filter-label">All Categories</span>
          </label>
          @foreach($categories as $cat)
            <label class="filter-option">
              <input type="radio" name="category" value="{{ $cat->id }}" {{ (string) ($filters['category'] ?? '') === (string) $cat->id ? 'checked' : '' }}>
              <i class="fa-solid fa-check filter-check"></i>
              <span class="filter-label">{{ $cat->name }}</span>
            </label>
          @endforeach
        </div>

        <div id="panel-brand" class="filter-panel d-none">
          <label class="filter-option">
            <input type="radio" name="brand" value="" {{ empty($filters['brand']) ? 'checked' : '' }}>
            <i class="fa-solid fa-check filter-check"></i>
            <span class="filter-label">All Brands</span>
          </label>
          @foreach($brands as $b)
            <label class="filter-option">
              <input type="radio" name="brand" value="{{ $b }}" {{ ($filters['brand'] ?? '') === $b ? 'checked' : '' }}>
              <i class="fa-solid fa-check filter-check"></i>
              <span class="filter-label">{{ $b }}</span>
            </label>
          @endforeach
        </div>

        <div id="panel-storage" class="filter-panel d-none">
          <label class="filter-option">
            <input type="radio" name="storage" value="" {{ empty($filters['storage']) ? 'checked' : '' }}>
            <i class="fa-solid fa-check filter-check"></i>
            <span class="filter-label">Any Storage</span>
          </label>
          @foreach($storages as $s)
            <label class="filter-option">
              <input type="radio" name="storage" value="{{ $s }}" {{ ($filters['storage'] ?? '') === $s ? 'checked' : '' }}>
              <i class="fa-solid fa-check filter-check"></i>
              <span class="filter-label">{{ $s }}</span>
            </label>
          @endforeach
        </div>

        <div id="panel-ram" class="filter-panel d-none">
          <label class="filter-option">
            <input type="radio" name="ram" value="" {{ empty($filters['ram']) ? 'checked' : '' }}>
            <i class="fa-solid fa-check filter-check"></i>
            <span class="filter-label">Any RAM</span>
          </label>
          @foreach($rams as $r)
            <label class="filter-option">
              <input type="radio" name="ram" value="{{ $r }}" {{ ($filters['ram'] ?? '') === $r ? 'checked' : '' }}>
              <i class="fa-solid fa-check filter-check"></i>
              <span class="filter-label">{{ $r }}</span>
            </label>
          @endforeach
        </div>

        <div id="panel-network" class="filter-panel d-none">
          <label class="filter-option">
            <input type="radio" name="network" value="" {{ empty($filters['network']) ? 'checked' : '' }}>
            <i class="fa-solid fa-check filter-check"></i>
            <span class="filter-label">Any Network</span>
          </label>
          @foreach(['3G'=>'3G','4G'=>'4G','5G'=>'5G','4G_5G'=>'4G & 5G'] as $val=>$label)
            <label class="filter-option">
              <input type="radio" name="network" value="{{ $val }}" {{ ($filters['network'] ?? '') === $val ? 'checked' : '' }}>
              <i class="fa-solid fa-check filter-check"></i>
              <span class="filter-label">{{ $label }}</span>
            </label>
          @endforeach
        </div>

        <div id="panel-price" class="filter-panel d-none">
          <div class="small-note mb-2 mt-2">Maximum price</div>
          <input type="number" min="0" class="form-control" name="price_max" value="{{ $filters['price_max'] ?? '' }}" placeholder="e.g. 20000">
        </div>

        <div id="panel-down" class="filter-panel d-none">
          <div class="small-note mb-2 mt-2">Maximum down payment</div>
          <input type="number" min="0" class="form-control" name="down_payment_max" value="{{ $filters['down_payment_max'] ?? '' }}" placeholder="e.g. 5000">
        </div>

        <div id="panel-emi" class="filter-panel d-none">
          <div class="small-note mb-2 mt-2">Maximum monthly EMI</div>
          <input type="number" min="0" class="form-control" name="emi_max" value="{{ $filters['emi_max'] ?? '' }}" placeholder="e.g. 2000">
        </div>

      </div>
    </div>
    <div class="filters-footer">
      <button type="button" class="btn-close-filters" onclick="closeFilters()">CLOSE</button>
      <button type="submit" class="btn-apply-filters">APPLY</button>
    </div>
  </div>
</form>

<div class="row g-2">
  @forelse($products as $p)
    <div class="col-6">
      <div class="product-card mb-2">
        <a href="{{ route('customer.products.show', $p) }}" style="color:inherit;">
          @if($p->image_path)
            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($p->image_path) }}" alt="{{ $p->name }}" class="w-100" style="height:110px;object-fit:cover;">
          @else
            <div class="product-thumb-placeholder d-flex align-items-center justify-content-center bg-primary-subtle" style="height:110px;"><i class="fa-solid fa-box fa-2x text-primary"></i></div>
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
        <form method="POST" action="{{ route('customer.favourites.toggle', $p) }}">
          @csrf
          <button class="fav-toggle-btn" type="submit" title="Toggle favourite"><i class="fa-solid fa-heart {{ in_array($p->id, $favouriteIds ?? []) ? 'text-danger' : '' }}"></i></button>
        </form>
      </div>
    </div>
  @empty
    <div class="col-12"><div class="card-flat p-4 text-center text-muted-fin">No products match your filters.</div></div>
  @endforelse
</div>

@push('scripts')
<script>
function openFilters(){
  document.getElementById('filtersOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeFilters(){
  document.getElementById('filtersOverlay').classList.remove('open');
  document.body.style.overflow = '';
}
function showFilterPanel(id, el){
  document.querySelectorAll('.filter-panel').forEach(function(p){ p.classList.add('d-none'); });
  document.getElementById(id).classList.remove('d-none');
  document.querySelectorAll('.filters-nav-item').forEach(function(n){ n.classList.remove('active'); });
  el.classList.add('active');
}
function clearAllFilters(){
  document.querySelectorAll('#filtersOverlay input[type=radio]').forEach(function(r){ r.checked = (r.value === ''); });
  document.querySelectorAll('#filtersOverlay input[type=checkbox]').forEach(function(c){ c.checked = false; });
  document.querySelectorAll('#filtersOverlay input[type=number]').forEach(function(n){ n.value = ''; });
}
</script>
@endpush
</x-customer-layout>
