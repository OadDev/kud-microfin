<x-guest-layout title="Forgot Password">
<div id="loginScreen">
  <div class="login-box">
    <div class="text-center mb-3">
      <div class="login-logo"><img src="{{ asset('images/logo-icon.png') }}" alt="BluePeak Fintech"></div>
      <h4 class="fw-bold brand-text mb-0">Forgot Password</h4>
      <div class="page-sub">We'll email you a 6-digit code to reset it</div>
    </div>

    @if(session('status'))
      <div class="alert alert-success py-2 small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Registered Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="you@example.com" autofocus>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <button class="btn btn-primary-fin w-100 mb-2" type="submit"><i class="fa-solid fa-paper-plane me-1"></i> Send Code</button>
      <div class="small-note text-center mt-2">No email on file? Ask your Shop Owner or our Helpline to reset it for you.</div>
    </form>

    <div class="text-center mt-3">
      <a href="{{ route('login') }}" class="small-note"><i class="fa-solid fa-arrow-left me-1"></i> Back to Login</a>
    </div>
  </div>
</div>
</x-guest-layout>
