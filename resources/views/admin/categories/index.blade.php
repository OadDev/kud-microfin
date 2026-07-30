<x-app-layout :title="$title" :active="$active">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <div class="section-title mb-0">Categories</div>
    <div class="page-sub">Organize products for the Customer Shop section</div>
  </div>
  <button class="btn btn-primary-fin btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddCategory"><i class="fa-solid fa-plus me-1"></i>Add Category</button>
</div>

<div class="card-flat p-0 table-responsive-fin">
  <table class="table table-fin mb-0">
    <thead><tr><th>Name</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($categories as $cat)
      <tr>
        <td class="fw-semibold">{{ $cat->name }}</td>
        <td>{{ $cat->products_count }}</td>
        <td><x-status-badge :status="$cat->is_active ? 'Active' : 'Inactive'" /></td>
        <td class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-fin" data-bs-toggle="modal" data-bs-target="#modalEditCategory{{ $cat->id }}"><i class="fa-solid fa-pen"></i></button>
          <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Remove category '{{ $cat->name }}'?" data-confirm-class="btn-danger"><i class="fa-solid fa-trash"></i></button>
          </form>
        </td>
      </tr>

      <div class="modal fade" id="modalEditCategory{{ $cat->id }}" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST" action="{{ route('admin.categories.update', $cat) }}">
              @csrf
              <div class="modal-header"><h5 class="modal-title">Edit Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <div class="mb-2"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ $cat->name }}" required></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="catActive{{ $cat->id }}" {{ $cat->is_active ? 'checked' : '' }}><label class="form-check-label" for="catActive{{ $cat->id }}">Active</label></div>
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
      <tr><td colspan="4" class="text-center text-muted-fin py-3">No categories yet.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="modal fade" id="modalAddCategory" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.categories.store') }}">
        @csrf
        <div class="modal-header"><h5 class="modal-title"><i class="fa-solid fa-tags me-2"></i>Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-2"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name') }}" required></div>
          <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="catActiveNew" checked><label class="form-check-label" for="catActiveNew">Active</label></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary-fin" type="submit"><i class="fa-solid fa-check me-1"></i>Save Category</button>
        </div>
      </form>
    </div>
  </div>
</div>
</x-app-layout>
