<x-guest-layout title="Login">
<div id="loginScreen">
  <div class="login-box">
    <div class="text-center mb-3">
      <div class="login-logo"><img src="{{ asset('images/logo-icon.png') }}" alt="BluePeak Fintech"></div>
      <h4 class="fw-bold brand-text mb-0">BluePeak Fintech</h4>
      <div class="page-sub">Microfinance Management Platform</div>
    </div>

    @if($quickLoginUser)
      <form method="POST" action="{{ route('quick-login.verify') }}">
        @csrf
        <input type="hidden" name="user_id" value="{{ $quickLoginUser->id }}">
        <div class="text-center mb-3">
          <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center mx-auto mb-2" style="width:56px;height:56px;font-size:1.4rem;"><i class="fa-solid fa-user"></i></div>
          <div class="fw-semibold">Welcome back, {{ $quickLoginUser->name }}</div>
        </div>
        <label class="form-label">Enter your PIN</label>
        <input type="password" inputmode="numeric" name="pin" maxlength="6" class="form-control otp-box mb-1 @error('pin') is-invalid @enderror" placeholder="••••" autofocus>
        @error('pin')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <button class="btn btn-primary-fin w-100 mt-3 mb-2" type="submit"><i class="fa-solid fa-unlock me-1"></i> Unlock</button>
      </form>
      <form method="POST" action="{{ route('quick-login.forget') }}">
        @csrf
        <button class="btn btn-link w-100 small-note" type="submit">Not you? Use a different account</button>
      </form>
    @else
      <form method="POST" action="{{ route('customer.otp.send') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">Registered Mobile Number</label>
          <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" maxlength="10" value="{{ old('mobile', session('otp_mobile')) }}" placeholder="10-digit mobile number" autofocus>
          @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn-primary-fin w-100 mb-3" type="submit"><i class="fa-solid fa-paper-plane me-1"></i> Send OTP</button>
      </form>

      @if(session('otp_mobile'))
        <form method="POST" action="{{ route('customer.otp.verify') }}">
          @csrf
          <input type="hidden" name="mobile" value="{{ session('otp_mobile') }}">
          <label class="form-label">Enter 6-Digit OTP</label>
          <input type="text" name="otp" class="form-control otp-box mb-1 @error('otp') is-invalid @enderror" maxlength="6" placeholder="••••••">
          @error('otp')<div class="invalid-feedback">{{ $message }}</div>@enderror
          <div class="otp-hint small-note mb-3">
            @if(session('otp_demo_code'))
              Demo OTP (SMS not connected yet): <strong>{{ session('otp_demo_code') }}</strong>
            @else
              An OTP has been generated for this number.
            @endif
          </div>
          <button class="btn btn-primary-fin w-100 mb-2" type="submit"><i class="fa-solid fa-check me-1"></i> Verify OTP</button>
        </form>
      @endif
    @endif
  </div>
</div>
</x-guest-layout>
