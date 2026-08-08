<x-guest-layout title="Login">
<div id="loginScreen">
  <div class="login-box">
    <div class="text-center mb-3">
      <div class="login-logo"><img src="{{ asset('images/logo-icon.png') }}" alt="BluePeak Fintech"></div>
      <h4 class="fw-bold brand-text mb-0">BluePeak Fintech</h4>
      <div class="page-sub">Microfinance Management Platform</div>
    </div>

    <div id="passkeyLoginBox" class="d-none mb-3">
      <button type="button" id="passkeyLoginBtn" class="btn btn-outline-fin w-100"><i class="fa-solid fa-fingerprint me-1"></i> Login with Face ID / Fingerprint</button>
      <div id="passkeyLoginError" class="small text-danger mt-1 text-center"></div>
      <div class="text-center small-note my-2">or</div>
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
      <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">Registered Mobile Number or Email</label>
          <input type="text" name="identifier" class="form-control @error('identifier') is-invalid @enderror" value="{{ old('identifier') }}" placeholder="10-digit mobile number" autofocus>
          @error('identifier')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Enter password">
        </div>
        <div class="text-end mb-2"><a href="{{ route('password.request') }}" class="small-note">Forgot password?</a></div>
        <button class="btn btn-primary-fin w-100 mb-2" type="submit"><i class="fa-solid fa-right-to-bracket me-1"></i> Login</button>
        <div class="small-note text-center mt-2">Don't know your password? Ask your Shop Owner or our Helpline -- it was shared with you when your account was created.</div>
      </form>
      <div class="text-center mt-3">
        <a href="{{ route('customer.register') }}" class="small-note">New here? Create an account</a>
      </div>
    @endif
  </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {
  if (!window.BluePeakPasskeys) return;
  const box = document.getElementById('passkeyLoginBox');
  const btn = document.getElementById('passkeyLoginBtn');
  const errEl = document.getElementById('passkeyLoginError');

  if (await window.BluePeakPasskeys.passkeySupported()) {
    box.classList.remove('d-none');
  }

  btn.addEventListener('click', function () {
    errEl.textContent = '';
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Waiting for Face ID / Fingerprint...';
    window.BluePeakPasskeys.passkeyLogin(function (message) {
      errEl.textContent = message;
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-fingerprint me-1"></i> Login with Face ID / Fingerprint';
    });
  });
});
</script>
@endpush
</x-guest-layout>
