<x-guest-layout title="Customer Registration">
<div class="d-flex align-items-center justify-content-center" style="min-height:100vh; background:linear-gradient(160deg,var(--accent) 0%,#123a7a 45%,var(--primary) 100%); padding:20px;">
  <div class="login-box" style="max-width:420px;">
    <div class="text-center mb-3">
      <div class="login-logo"><img src="{{ asset('images/logo-icon.png') }}" alt="BluePeak Fintech"></div>
      <h5 class="fw-bold brand-text mb-0">Create Your Account</h5>
      <div class="page-sub">Register as a Customer</div>
    </div>

    <form method="POST" action="{{ route('customer.register.submit') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="mb-3">
        <label class="form-label">Mobile Number</label>
        <input class="form-control @error('mobile') is-invalid @enderror" name="mobile" maxlength="10" value="{{ old('mobile') }}" placeholder="10-digit mobile number">
        @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="At least 6 characters">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" class="form-control" name="password_confirmation" placeholder="Re-enter password">
      </div>
      <button class="btn btn-primary-fin w-100 mb-2" type="submit"><i class="fa-solid fa-user-plus me-1"></i> Create Account</button>
    </form>

    <div class="text-center mt-3">
      <a href="{{ route('login') }}" class="small-note">Already have an account? Login</a>
    </div>
  </div>
</div>
</x-guest-layout>
