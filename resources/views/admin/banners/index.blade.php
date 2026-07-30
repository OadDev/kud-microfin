<x-app-layout :title="$title" :active="$active">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <div class="section-title mb-0">Home Banners</div>
    <div class="page-sub">Shown as a carousel at the top of the Customer Home screen</div>
  </div>
  <button class="btn btn-primary-fin btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddBanner"><i class="fa-solid fa-plus me-1"></i>Add Banner</button>
</div>

<div class="row g-3">
  @forelse($banners as $banner)
    <div class="col-md-6 col-lg-4">
      <div class="card-flat p-0 overflow-hidden">
        @if($banner->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($banner->image_path))
          <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_path) }}" alt="{{ $banner->title }}" class="w-100" style="height:140px;object-fit:cover;">
        @else
          <div class="d-flex align-items-center justify-content-center text-white fw-semibold" style="height:140px;background:linear-gradient(135deg,var(--primary),var(--accent));">{{ $banner->title ?: 'No image' }}</div>
        @endif
        <div class="p-3">
          <div class="d-flex justify-content-between align-items-start mb-1">
            <div class="fw-semibold">{{ $banner->title ?: '(No title)' }}</div>
            <x-status-badge :status="$banner->is_active ? 'Active' : 'Inactive'" />
          </div>
          <div class="small-note mb-2">Order: {{ $banner->sort_order }} @if($banner->link_url) &middot; <a href="{{ $banner->link_url }}" target="_blank">link</a> @endif</div>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-fin flex-fill" data-bs-toggle="modal" data-bs-target="#modalEditBanner{{ $banner->id }}"><i class="fa-solid fa-pen"></i> Edit</button>
            <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Remove this banner?" data-confirm-class="btn-danger"><i class="fa-solid fa-trash"></i></button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modalEditBanner{{ $banner->id }}" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Edit Banner</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <div class="mb-2"><label class="form-label">Title</label><input class="form-control" name="title" value="{{ $banner->title }}"></div>
              <div class="mb-2"><label class="form-label">Image (leave blank to keep current)</label><input type="file" class="form-control" name="image" accept="image/*"></div>
              <div class="mb-2"><label class="form-label">Link URL (optional)</label><input class="form-control" name="link_url" value="{{ $banner->link_url }}" placeholder="https://..."></div>
              <div class="mb-2"><label class="form-label">Sort Order</label><input type="number" class="form-control" name="sort_order" value="{{ $banner->sort_order }}" min="0"></div>
              <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="active{{ $banner->id }}" {{ $banner->is_active ? 'checked' : '' }}><label class="form-check-label" for="active{{ $banner->id }}">Active</label></div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
              <button class="btn btn-primary-fin" type="submit"><i class="fa-solid fa-check me-1"></i>Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12"><div class="card-flat p-4 text-center text-muted-fin">No banners yet. Add one to show it on the Customer Home screen.</div></div>
  @endforelse
</div>

<div class="modal fade" id="modalAddBanner" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header"><h5 class="modal-title"><i class="fa-solid fa-images me-2"></i>Add Banner</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-2"><label class="form-label">Title (optional)</label><input class="form-control" name="title" value="{{ old('title') }}"></div>
          <div class="mb-2"><label class="form-label">Image</label><input type="file" class="form-control" name="image" accept="image/*" required></div>
          <div class="mb-2"><label class="form-label">Link URL (optional)</label><input class="form-control" name="link_url" value="{{ old('link_url') }}" placeholder="https://..."></div>
          <div class="mb-2"><label class="form-label">Sort Order</label><input type="number" class="form-control" name="sort_order" value="0" min="0"></div>
          <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeNew" checked><label class="form-check-label" for="activeNew">Active</label></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary-fin" type="submit"><i class="fa-solid fa-check me-1"></i>Save Banner</button>
        </div>
      </form>
    </div>
  </div>
</div>
</x-app-layout>
