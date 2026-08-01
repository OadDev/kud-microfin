<x-guest-layout title="Set Up Quick Login">
<div id="loginScreen">
  <div class="login-box">
    <div class="text-center mb-3">
      <div class="login-logo"><img src="{{ asset('images/logo-icon.png') }}" alt="BluePeak Fintech"></div>
      <h4 class="fw-bold brand-text mb-0">Set Up Quick Login</h4>
      <div class="page-sub">Skip typing your password/OTP next time on this device</div>
    </div>

    <form method="POST" action="{{ route('quick-login.setup.store') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Choose a 4-6 digit PIN</label>
        <input type="password" inputmode="numeric" name="pin" maxlength="6" class="form-control otp-box mb-1 @error('pin') is-invalid @enderror" placeholder="••••" autofocus>
        @error('pin')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm PIN</label>
        <input type="password" inputmode="numeric" name="pin_confirmation" maxlength="6" class="form-control otp-box">
      </div>
      <button class="btn btn-primary-fin w-100 mb-2" type="submit"><i class="fa-solid fa-lock me-1"></i> Set PIN &amp; Continue</button>
    </form>
    <form method="POST" action="{{ route('quick-login.skip') }}">
      @csrf
      <button class="btn btn-link w-100 small-note" type="submit">Skip for now</button>
    </form>
  </div>
</div>
</x-guest-layout>
