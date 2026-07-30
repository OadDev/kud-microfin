<x-customer-layout :title="$title" :active="$active">
<div class="card-flat p-3 mb-3 text-center">
  <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center mx-auto mb-2" style="width:72px;height:72px;font-size:2rem;"><i class="fa-solid fa-user"></i></div>
  <div class="fw-bold fs-5">{{ $customer->user->name }}</div>
  <div class="small-note">{{ $customer->customer_code }}</div>
</div>
<div class="card-flat p-3 mb-3">
  <div class="dc-row"><span class="text-muted-fin">Mobile Number</span><span>{{ $customer->user->mobile }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Email</span><span>{{ $customer->user->email ?? '-' }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Address</span><span class="text-end">{{ $customer->address }}, {{ $customer->city }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">PAN</span><span>{{ $customer->pan }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Aadhaar</span><span>{{ $customer->aadhaar }}</span></div>
</div>
<form method="POST" action="{{ route('logout') }}">
  @csrf
  <button class="btn btn-outline-danger w-100 mb-4" type="submit"><i class="fa-solid fa-right-from-bracket me-1"></i>Logout</button>
</form>
</x-customer-layout>
