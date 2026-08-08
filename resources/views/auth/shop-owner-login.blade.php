<x-guest-layout title="Shop Owner Login">
<div id="loginScreen">
  <div class="login-box">
    <div class="text-center mb-3">
      <div class="login-logo"><img src="{{ asset('images/logo-icon.png') }}" alt="BluePeak Fintech"></div>
      <h4 class="fw-bold brand-text mb-0">BluePeak Fintech</h4>
      <div class="page-sub">Shop Owner Login</div>
    </div>

    <form method="POST" action="{{ route('shopowner.login.submit') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Mobile Number or Email</label>
        <input type="text" name="identifier" class="form-control @error('identifier') is-invalid @enderror" value="{{ old('identifier') }}" placeholder="e.g. 9876543210 or you@email.com" autofocus>
        @error('identifier')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter password">
      </div>
      <button class="btn btn-primary-fin w-100 mb-2" type="submit"><i class="fa-solid fa-right-to-bracket me-1"></i> Login</button>
    </form>
    <div class="text-center small-note mt-3">
      Want to list your shop? <a href="{{ route('shop-owner.register') }}">Register as Shop Owner</a>
    </div>
  </div>
</div>
</x-guest-layout>
