<x-guest-layout title="Reset Password">
<div id="loginScreen">
  <div class="login-box">
    <div class="text-center mb-3">
      <div class="login-logo"><img src="{{ asset('images/logo-icon.png') }}" alt="BluePeak Fintech"></div>
      <h4 class="fw-bold brand-text mb-0">Reset Password</h4>
      <div class="page-sub">Enter the code we emailed you and a new password</div>
    </div>

    @if(session('status'))
      <div class="alert alert-success py-2 small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.reset') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Registered Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $email) }}" placeholder="you@example.com">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="mb-3">
        <label class="form-label">6-Digit Code</label>
        <input type="text" inputmode="numeric" name="code" maxlength="6" class="form-control otp-box @error('code') is-invalid @enderror" placeholder="••••••">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="At least 6 characters">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm New Password</label>
        <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter new password">
      </div>
      <button class="btn btn-primary-fin w-100 mb-2" type="submit"><i class="fa-solid fa-key me-1"></i> Reset Password</button>
    </form>

    <div class="text-center mt-3">
      <a href="{{ route('password.request') }}" class="small-note">Didn't get a code? Request a new one</a>
    </div>
  </div>
</div>
</x-guest-layout>
