<x-app-layout title="Shop Owner Details" active="shop-owners">
<a class="btn btn-sm btn-outline-fin mb-3" href="{{ route('admin.shop-owners.index') }}"><i class="fa-solid fa-arrow-left me-1"></i>Back to Shop Owners</a>
<div class="card-flat p-3 p-md-4">
  <div class="d-flex justify-content-between mb-3">
    <div>
      <div class="fw-bold fs-5">{{ $shopOwner->user->name }}</div>
      <div class="small-note">{{ $shopOwner->shop_owner_code }}</div>
    </div>
    <x-status-badge :status="ucfirst($shopOwner->user->status)" />
  </div>
  <div class="dc-row"><span class="text-muted-fin">Shop Name</span><span>{{ $shopOwner->shop_name }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Mobile</span><span>{{ $shopOwner->user->mobile }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Email</span><span>{{ $shopOwner->user->email ?? '-' }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">PAN</span><span>{{ $shopOwner->pan ?? '-' }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Aadhaar</span><span>{{ $shopOwner->aadhaar ?? '-' }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Address</span><span class="text-end">{{ $shopOwner->address }}, {{ $shopOwner->city }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Registration Type</span><span>{{ $shopOwner->reg_type === 'admin_created' ? 'Created by Admin' : 'Self Registered' }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Registered On</span><span>{{ $shopOwner->created_at->format('d/m/Y') }}</span></div>
  @if($shopOwner->reject_reason)
    <div class="dc-row"><span class="text-muted-fin">Reject Reason</span><span class="text-end">{{ $shopOwner->reject_reason }}</span></div>
  @endif
</div>
</x-app-layout>
