<x-guest-layout title="Shop Owner Registration">
<div class="d-flex align-items-center justify-content-center" style="min-height:100vh; background:linear-gradient(160deg,var(--accent) 0%,#123a7a 45%,var(--primary) 100%); padding:20px;">
  <div class="login-box" style="max-width:520px;">
    <div class="text-center mb-3">
      <div class="login-logo"><img src="{{ asset('images/logo-icon.png') }}" alt="BluePeak Fintech"></div>
      <h5 class="fw-bold brand-text mb-0">Shop Owner Registration</h5>
      <div class="page-sub">Join BluePeak Fintech as a Partner Shop</div>
    </div>

    @if(session('registered'))
      <div class="text-center py-3">
        <i class="fa-solid fa-circle-check text-success" style="font-size:3rem;"></i>
        <h5 class="fw-bold mt-3">Registration Submitted</h5>
        <p class="text-muted-fin">Your registration has been submitted and is pending Admin approval.</p>
      </div>
    @else
      <form method="POST" action="{{ route('shop-owner.register.submit') }}">
        @csrf
        <div class="row g-2">
          <div class="col-12"><label class="form-label">Full Name</label><input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}"></div>
          <div class="col-12"><label class="form-label">Shop Name</label><input class="form-control @error('shop_name') is-invalid @enderror" name="shop_name" value="{{ old('shop_name') }}"></div>
          <div class="col-6"><label class="form-label">Mobile Number</label><input class="form-control @error('mobile') is-invalid @enderror" name="mobile" maxlength="10" value="{{ old('mobile') }}"></div>
          <div class="col-6"><label class="form-label">Email</label><input class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}"></div>
          <div class="col-6"><label class="form-label">PAN Number</label><input class="form-control" name="pan" maxlength="10" value="{{ old('pan') }}"></div>
          <div class="col-6"><label class="form-label">Aadhaar Number</label><input class="form-control" name="aadhaar" maxlength="12" value="{{ old('aadhaar') }}"></div>
          <div class="col-12"><label class="form-label">Address</label><input class="form-control" name="address" value="{{ old('address') }}"></div>
          <div class="col-6"><label class="form-label">City</label><input class="form-control" name="city" value="{{ old('city') }}"></div>
          <div class="col-6"><label class="form-label">Password</label><input type="password" class="form-control @error('password') is-invalid @enderror" name="password"></div>
        </div>
        <button class="btn btn-primary-fin w-100 mt-3" type="submit"><i class="fa-solid fa-paper-plane me-1"></i> Submit Registration</button>
      </form>
    @endif
    <div class="text-center small-note mt-3"><a href="{{ route('login') }}">Back to Login</a></div>
  </div>
</div>
</x-guest-layout>
