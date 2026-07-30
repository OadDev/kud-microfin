<x-app-layout :title="$title" :active="$active">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <div class="section-title mb-0">Shop Owner Management</div>
    <div class="page-sub">Manage partner shop owner accounts</div>
  </div>
  <button class="btn btn-primary-fin btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddShopOwner"><i class="fa-solid fa-plus me-1"></i>Add Shop Owner</button>
</div>

<div class="card-flat p-3 mb-3">
  <form method="GET" class="row g-2">
    <div class="col-md-8"><input class="form-control" name="search" value="{{ $search }}" placeholder="Search by name, shop, mobile or ID..."></div>
    <div class="col-md-4">
      <select class="form-select" name="status" onchange="this.form.submit()">
        <option value="" {{ $status==='All'?'selected':'' }}>All Statuses</option>
        @foreach(['pending','approved','rejected','suspended'] as $s)
          <option value="{{ $s }}" {{ $status===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
    </div>
  </form>
</div>

<div class="card-flat p-0 table-responsive-fin">
  <table class="table table-fin mb-0">
    <thead><tr><th>ID</th><th>Full Name</th><th>Shop Name</th><th>Mobile</th><th>City</th><th>Reg. Type</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($shopOwners as $s)
      <tr>
        <td class="fw-semibold">{{ $s->shop_owner_code }}</td>
        <td>{{ $s->user->name }}</td>
        <td>{{ $s->shop_name }}</td>
        <td>{{ $s->user->mobile }}</td>
        <td>{{ $s->city }}</td>
        <td><span class="small-note">{{ $s->reg_type === 'admin_created' ? 'Created by Admin' : 'Self Registered' }}</span></td>
        <td><x-status-badge :status="ucfirst($s->user->status)" /></td>
        <td>@include('admin.shop-owners._actions', ['s' => $s])</td>
      </tr>
    @empty
      <tr><td colspan="8" class="text-center text-muted-fin py-3">No shop owners found.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="data-cards">
  @forelse($shopOwners as $s)
    <div class="data-card">
      <div class="dc-head">
        <div><div class="fw-bold">{{ $s->user->name }}</div><div class="small-note">{{ $s->shop_owner_code }}</div></div>
        <x-status-badge :status="ucfirst($s->user->status)" />
      </div>
      <div class="dc-row"><span class="dc-label">Shop Name</span><span>{{ $s->shop_name }}</span></div>
      <div class="dc-row"><span class="dc-label">Mobile</span><span>{{ $s->user->mobile }}</span></div>
      <div class="dc-row"><span class="dc-label">City</span><span>{{ $s->city }}</span></div>
      <div class="d-flex gap-2 mt-2">@include('admin.shop-owners._actions', ['s' => $s])</div>
    </div>
  @empty
    <div class="text-center text-muted-fin py-3">No shop owners found.</div>
  @endforelse
</div>

<!-- Add Shop Owner Modal -->
<div class="modal fade" id="modalAddShopOwner" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.shop-owners.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-solid fa-store me-2"></i>Add Shop Owner</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Full Name</label><input class="form-control" name="name" value="{{ old('name') }}"></div>
            <div class="col-md-6"><label class="form-label">Shop Name</label><input class="form-control" name="shop_name" value="{{ old('shop_name') }}"></div>
            <div class="col-md-6"><label class="form-label">Mobile Number</label><input class="form-control" name="mobile" value="{{ old('mobile') }}"></div>
            <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" value="{{ old('email') }}"></div>
            <div class="col-md-6"><label class="form-label">PAN Number</label><input class="form-control" name="pan" value="{{ old('pan') }}"></div>
            <div class="col-md-6"><label class="form-label">Aadhaar Number</label><input class="form-control" name="aadhaar" value="{{ old('aadhaar') }}"></div>
            <div class="col-md-8"><label class="form-label">Address</label><input class="form-control" name="address" value="{{ old('address') }}"></div>
            <div class="col-md-4"><label class="form-label">City</label><input class="form-control" name="city" value="{{ old('city') }}"></div>
            <div class="col-md-6"><label class="form-label">Password</label><input type="password" class="form-control" name="password"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary-fin" type="submit"><i class="fa-solid fa-check me-1"></i>Save Shop Owner</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Shared Reject Reason Modal -->
<div class="modal fade" id="modalRejectReason" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="rejectForm">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Reject Shop Owner Registration</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <label class="form-label">Reason for Rejection</label>
          <textarea class="form-control" name="reason" rows="3" required></textarea>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-danger" type="submit"><i class="fa-solid fa-xmark me-1"></i>Confirm Reject</button>
        </div>
      </form>
    </div>
  </div>
</div>

@foreach($shopOwners as $s)
<div class="modal fade" id="modalViewShopOwner{{ $s->id }}" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Shop Owner Details</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="d-flex justify-content-between mb-2"><span class="fw-bold">{{ $s->user->name }}</span><x-status-badge :status="ucfirst($s->user->status)" /></div>
        <div class="dc-row"><span class="text-muted-fin">Shop Owner ID</span><span>{{ $s->shop_owner_code }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Shop Name</span><span>{{ $s->shop_name }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Mobile</span><span>{{ $s->user->mobile }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Email</span><span>{{ $s->user->email ?? '-' }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">PAN</span><span>{{ $s->pan ?? '-' }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Aadhaar</span><span>{{ $s->aadhaar ?? '-' }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Address</span><span class="text-end">{{ $s->address }}, {{ $s->city }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Registration Type</span><span>{{ $s->reg_type === 'admin_created' ? 'Created by Admin' : 'Self Registered' }}</span></div>
        <div class="dc-row"><span class="text-muted-fin">Registered On</span><span>{{ $s->created_at->format('d/m/Y') }}</span></div>
        @if($s->reject_reason)
          <div class="dc-row"><span class="text-muted-fin">Reject Reason</span><span class="text-end">{{ $s->reject_reason }}</span></div>
        @endif
      </div>
      <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>
@endforeach

@push('scripts')
<script>
function openRejectModal(actionUrl){
  document.getElementById('rejectForm').setAttribute('action', actionUrl);
  bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRejectReason')).show();
}
</script>
@endpush
</x-app-layout>
