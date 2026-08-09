<x-guest-layout title="Enter Your PIN">
<div id="loginScreen">
  <div class="login-box">
    <div class="text-center mb-3">
      <div class="login-logo"><img src="{{ asset('images/logo-icon.png') }}" alt="BluePeak Fintech"></div>
      <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center mx-auto mb-2 mt-3" style="width:56px;height:56px;font-size:1.4rem;"><i class="fa-solid fa-lock"></i></div>
      <div class="fw-semibold">Welcome back, {{ $user->name }}</div>
      <div class="page-sub">Enter your PIN to continue</div>
    </div>

    <form method="POST" action="{{ route('customer.lock.verify') }}">
      @csrf
      <input type="hidden" name="next" value="{{ $next }}">
      <input type="password" inputmode="numeric" name="pin" maxlength="6" class="form-control otp-box mb-1 @error('pin') is-invalid @enderror" placeholder="••••" autofocus>
      @error('pin')<div class="invalid-feedback">{{ $message }}</div>@enderror
      <button class="btn btn-primary-fin w-100 mt-3 mb-2" type="submit"><i class="fa-solid fa-unlock me-1"></i> Unlock</button>
    </form>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button class="btn btn-link w-100 small-note" type="submit">Forgot PIN? Log out</button>
    </form>
  </div>
</div>
</x-guest-layout>
