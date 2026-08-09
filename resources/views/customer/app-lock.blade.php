<x-guest-layout title="Unlock">
<div id="loginScreen">
  <div class="login-box">
    <div class="text-center mb-3">
      <div class="login-logo"><img src="{{ asset('images/logo-icon.png') }}" alt="BluePeak Fintech"></div>
      <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center mx-auto mb-2 mt-3" style="width:56px;height:56px;font-size:1.4rem;"><i class="fa-solid fa-lock"></i></div>
      <div class="fw-semibold">Welcome back, {{ $user->name }}</div>
      <div class="page-sub">Unlock to continue</div>
    </div>

    @if($showBiometric)
      <div id="lockBiometricBox" class="mb-3">
        <button type="button" id="lockBiometricBtn" class="btn btn-outline-fin w-100"><i class="fa-solid fa-fingerprint me-1"></i> Unlock with Face ID / Fingerprint</button>
        <div id="lockBiometricError" class="small text-danger mt-1 text-center"></div>
      </div>
      @if($showPin)
        <div class="text-center small-note my-2">or</div>
      @endif
    @endif

    @if($showPin)
      <form method="POST" action="{{ route('customer.lock.verify') }}">
        @csrf
        <input type="hidden" name="next" value="{{ $next }}">
        <input type="password" inputmode="numeric" name="pin" maxlength="6" class="form-control otp-box mb-1 @error('pin') is-invalid @enderror" placeholder="••••" autofocus>
        @error('pin')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <button class="btn btn-primary-fin w-100 mt-3 mb-2" type="submit"><i class="fa-solid fa-unlock me-1"></i> Unlock with PIN</button>
      </form>
    @endif

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button class="btn btn-link w-100 small-note" type="submit">Log out instead</button>
    </form>
  </div>
</div>
@if($showBiometric)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('lockBiometricBtn');
  const errEl = document.getElementById('lockBiometricError');
  const next = @json($next);

  function attempt() {
    if (!window.BluePeakNativeBiometric) return;
    errEl.textContent = '';
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Waiting for Face ID / Fingerprint...';
    window.BluePeakNativeBiometric.login(function (message) {
      errEl.textContent = message;
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-fingerprint me-1"></i> Unlock with Face ID / Fingerprint';
    }, next);
  }

  btn.addEventListener('click', attempt);
  // Prompt immediately on arrival -- one tap saved is worth it here, and
  // cancelling just leaves the button (and PIN form, if enabled) to fall
  // back on.
  attempt();
});
</script>
@endpush
@endif
</x-guest-layout>
