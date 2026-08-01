<x-app-layout :title="$title" :active="$active">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <div class="section-title mb-0">Products</div>
    <div class="page-sub">Manage what customers can buy in the Shop section</div>
  </div>
  <button class="btn btn-primary-fin btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddProduct"><i class="fa-solid fa-plus me-1"></i>Add Product</button>
</div>

<div class="card-flat p-0 table-responsive-fin">
  <table class="table table-fin mb-0">
    <thead><tr><th></th><th>Name</th><th>Brand</th><th>Category</th><th>Price</th><th>Down Payment</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($products as $p)
      <tr>
        <td>
          @if($p->image_path)
            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($p->image_path) }}" class="screenshot-thumb" alt="{{ $p->name }}">
          @else
            <div class="screenshot-thumb d-flex align-items-center justify-content-center"><i class="fa-solid fa-box text-primary"></i></div>
          @endif
        </td>
        <td class="fw-semibold">{{ $p->name }}</td>
        <td>{{ $p->brand ?? '-' }}</td>
        <td>{{ $p->category?->name ?? '-' }}</td>
        <td>₹{{ number_format($p->price, 2) }}</td>
        <td>{{ $p->financeable() ? '₹'.number_format($p->down_payment, 2) : '-' }}</td>
        <td>{{ $p->stock_quantity === null ? 'Unlimited' : $p->stock_quantity }}</td>
        <td><x-status-badge :status="$p->is_active ? 'Active' : 'Inactive'" /></td>
        <td class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-fin" data-bs-toggle="modal" data-bs-target="#modalEditProduct{{ $p->id }}"><i class="fa-solid fa-pen"></i></button>
          <form method="POST" action="{{ route('admin.products.destroy', $p) }}">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Remove product '{{ $p->name }}'?" data-confirm-class="btn-danger"><i class="fa-solid fa-trash"></i></button>
          </form>
        </td>
      </tr>

      <div class="modal fade" id="modalEditProduct{{ $p->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <form method="POST" action="{{ route('admin.products.update', $p) }}" enctype="multipart/form-data">
              @csrf
              <div class="modal-header"><h5 class="modal-title">Edit Product</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <div class="row g-3">
                  <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ $p->name }}" required></div>
                  <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id">
                      <option value="">— None —</option>
                      @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $p->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Brand</label>
                    <select class="form-select" name="brand">
                      <option value="">— None —</option>
                      @foreach($brands as $b)
                        <option value="{{ $b }}" {{ $p->brand === $b ? 'selected' : '' }}>{{ $b }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Network</label>
                    <select class="form-select" name="network_type">
                      <option value="">— None —</option>
                      @foreach(['3G'=>'3G','4G'=>'4G','5G'=>'5G','4G_5G'=>'4G & 5G'] as $val=>$label)
                        <option value="{{ $val }}" {{ $p->network_type === $val ? 'selected' : '' }}>{{ $label }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4"><label class="form-label">Storage</label><input class="form-control" name="storage" value="{{ $p->storage }}" placeholder="e.g. 128GB"></div>
                  <div class="col-md-4"><label class="form-label">RAM</label><input class="form-control" name="ram" value="{{ $p->ram }}" placeholder="e.g. 6GB"></div>
                  <div class="col-md-4"><label class="form-label">Stock Quantity (blank = unlimited)</label><input type="number" min="0" class="form-control" name="stock_quantity" value="{{ $p->stock_quantity }}"></div>
                  <div class="col-md-6"><label class="form-label">Price (₹)</label><input type="number" step="0.01" min="0" class="form-control" name="price" value="{{ $p->price }}" required></div>
                  <div class="col-md-6"><label class="form-label">Down Payment (₹, optional — enables EMI financing)</label><input type="number" step="0.01" min="0" class="form-control" name="down_payment" value="{{ $p->down_payment }}"></div>
                  <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3">{{ $p->description }}</textarea></div>
                  <div class="col-md-8">
                    <label class="form-label">Photos (up to 4, leave blank to keep current)</label>
                    <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
                    @if($p->images->isNotEmpty())
                      <div class="d-flex gap-1 mt-2">
                        @foreach($p->images as $img)
                          <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($img->path) }}" class="screenshot-thumb" alt="">
                        @endforeach
                      </div>
                    @endif
                  </div>
                  <div class="col-md-4 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="prodActive{{ $p->id }}" {{ $p->is_active ? 'checked' : '' }}><label class="form-check-label" for="prodActive{{ $p->id }}">Active</label></div></div>
                  <div class="col-12"><label class="form-label">Video Link (optional — YouTube, etc.)</label><input type="url" class="form-control" name="video_url" value="{{ $p->video_url }}" placeholder="https://..."></div>
                </div>
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
      <tr><td colspan="9" class="text-center text-muted-fin py-3">No products yet.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="modal fade" id="modalAddProduct" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header"><h5 class="modal-title"><i class="fa-solid fa-box me-2"></i>Add Product</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name') }}" required></div>
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <select class="form-select" name="category_id">
                <option value="">— None —</option>
                @foreach($categories as $cat)
                  <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Brand</label>
              <select class="form-select" name="brand">
                <option value="">— None —</option>
                @foreach($brands as $b)
                  <option value="{{ $b }}">{{ $b }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Network</label>
              <select class="form-select" name="network_type">
                <option value="">— None —</option>
                @foreach(['3G'=>'3G','4G'=>'4G','5G'=>'5G','4G_5G'=>'4G & 5G'] as $val=>$label)
                  <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4"><label class="form-label">Storage</label><input class="form-control" name="storage" value="{{ old('storage') }}" placeholder="e.g. 128GB"></div>
            <div class="col-md-4"><label class="form-label">RAM</label><input class="form-control" name="ram" value="{{ old('ram') }}" placeholder="e.g. 6GB"></div>
            <div class="col-md-4"><label class="form-label">Stock Quantity (blank = unlimited)</label><input type="number" min="0" class="form-control" name="stock_quantity" value="{{ old('stock_quantity') }}"></div>
            <div class="col-md-6"><label class="form-label">Price (₹)</label><input type="number" step="0.01" min="0" class="form-control" name="price" value="{{ old('price') }}" required></div>
            <div class="col-md-6"><label class="form-label">Down Payment (₹, optional — enables EMI financing)</label><input type="number" step="0.01" min="0" class="form-control" name="down_payment" value="{{ old('down_payment') }}"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea></div>
            <div class="col-md-8"><label class="form-label">Photos (up to 4)</label><input type="file" class="form-control" name="images[]" accept="image/*" multiple></div>
            <div class="col-md-4 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="prodActiveNew" checked><label class="form-check-label" for="prodActiveNew">Active</label></div></div>
            <div class="col-12"><label class="form-label">Video Link (optional — YouTube, etc.)</label><input type="url" class="form-control" name="video_url" value="{{ old('video_url') }}" placeholder="https://..."></div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary-fin" type="submit"><i class="fa-solid fa-check me-1"></i>Save Product</button>
        </div>
      </form>
    </div>
  </div>
</div>
</x-app-layout>
