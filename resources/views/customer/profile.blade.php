<x-customer-layout :title="$title" :active="$active" pageTitle="My Profile">
<div class="card-flat p-3 mb-3 text-center">
  <label for="photoInput" style="cursor:pointer;">
    @if($customer->photo_path)
      <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($customer->photo_path) }}" class="rounded-circle mx-auto mb-2" style="width:72px;height:72px;object-fit:cover;" alt="Profile photo">
    @else
      <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center mx-auto mb-2" style="width:72px;height:72px;font-size:2rem;"><i class="fa-solid fa-user"></i></div>
    @endif
    <div class="small-note"><i class="fa-solid fa-camera me-1"></i>Change Photo</div>
  </label>
  <form id="photoForm" method="POST" action="{{ route('customer.profile.photo') }}" enctype="multipart/form-data" class="d-none">
    @csrf
    <input type="file" id="photoInput" name="photo" accept="image/*" onchange="document.getElementById('photoForm').submit()">
  </form>
  <div class="fw-bold fs-5 mt-2">{{ $customer->user->name }}</div>
  <div class="small-note">{{ $customer->customer_code }}</div>
</div>
<div class="card-flat p-3 mb-3">
  <div class="dc-row"><span class="text-muted-fin">Mobile Number</span><span>{{ $customer->user->mobile }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Email</span><span>{{ $customer->user->email ?? '-' }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Address</span><span class="text-end">{{ $customer->address }}, {{ $customer->city }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">PAN</span><span>{{ $customer->pan }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Aadhaar</span><span>{{ $customer->aadhaar }}</span></div>
</div>

<a href="{{ route('customer.orders.index') }}" class="card-flat p-3 mb-3 d-flex justify-content-between align-items-center" style="color:inherit;">
  <span><i class="fa-solid fa-receipt me-2 text-primary"></i>My Orders</span>
  <i class="fa-solid fa-chevron-right text-muted"></i>
</a>

<div class="card-flat p-3 mb-3">
  <div class="section-title mb-2">Quick Login</div>
  @if($customer->user->hasPinEnabled())
    <div class="small-note mb-2"><i class="fa-solid fa-circle text-success" style="font-size:8px;"></i> PIN quick login is enabled on this device.</div>
    <form method="POST" action="{{ route('quick-login.disable') }}" onsubmit="return confirm('Turn off quick login on this device?');">
      @csrf
      <button class="btn btn-outline-danger btn-sm w-100" type="submit"><i class="fa-solid fa-lock me-1"></i>Disable Quick Login</button>
    </form>
  @else
    <div class="small-note mb-2">Not set up yet — you'll be offered this the next time you log in.</div>
  @endif
</div>
<form method="POST" action="{{ route('logout') }}">
  @csrf
  <button class="btn btn-outline-danger w-100 mb-4" type="submit"><i class="fa-solid fa-right-from-bracket me-1"></i>Logout</button>
</form>
</x-customer-layout>
