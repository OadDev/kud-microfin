@php
    $carouselId = $carouselId ?? 'bannerCarousel';
    $bannerHeight = $bannerHeight ?? '140px';
@endphp
@if($banners->isNotEmpty())
<div id="{{ $carouselId }}" class="carousel slide mb-3 rounded overflow-hidden" data-bs-ride="carousel">
  <div class="carousel-inner">
    @foreach($banners as $i => $banner)
      <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
        @php
          $hasImage = $banner->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($banner->image_path);
          $img = $hasImage
            ? '<img src="'.Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_path).'" class="d-block w-100" alt="'.e($banner->title).'" style="height:'.$bannerHeight.';object-fit:cover;">'
            : '<div class="d-flex align-items-center justify-content-center text-white fw-semibold" style="height:'.$bannerHeight.';background:linear-gradient(135deg,var(--primary),var(--accent));">'.e($banner->title ?: 'BluePeak Fintech').'</div>';
        @endphp
        @if($banner->link_url)
          <a href="{{ $banner->link_url }}" target="_blank" rel="noopener">{!! $img !!}</a>
        @else
          {!! $img !!}
        @endif
      </div>
    @endforeach
  </div>
  @if($banners->count() > 1)
    <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
    <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
  @endif
</div>
@endif
