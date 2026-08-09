<x-customer-layout :title="$title" :active="$active" pageTitle="My Profile">
<div class="card-flat p-3 mb-3 text-center">
  <label for="photoInput" style="cursor:pointer;">
    @if($customer->photo_path)
      <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($customer->photo_path) }}" class="rounded-circle mx-auto mb-2" style="width:72px;height:72px;object-fit:cover;" alt="Profile photo">
    @else
      <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center mx-auto mb-2" style="width:72px;height:72px;font-size:2rem;"><i class="fa-solid fa-user"></i></div>
    @endif
    <div class="small-note"><i class="fa-solid fa-camera me-1"></i>Change Photo</div>
  </label>
  <form id="photoForm" method="POST" action="{{ route('customer.profile.photo') }}" enctype="multipart/form-data" class="d-none">
    @csrf
    <input type="file" id="photoInput" name="photo" accept="image/*" onchange="document.getElementById('photoForm').submit()">
  </form>
  <div class="fw-bold fs-5 mt-2">{{ $customer->user->name }}</div>
  <div class="small-note">{{ $customer->customer_code }}</div>
</div>
<div class="card-flat p-3 mb-3">
  <div class="dc-row"><span class="text-muted-fin">Mobile Number</span><span>{{ $customer->user->mobile }}</span></div>
  <div class="dc-row"><span class="text-muted-fin">Email</span><span>{{ $customer->user->email ?? '-' }}</span></div>
</div>

<div class="card-flat p-3 mb-3">
  <div class="section-title mb-2">Address &amp; KYC Details</div>
  <form method="POST" action="{{ route('customer.profile.details') }}">
    @csrf
    <div class="mb-2">
      <label class="form-label">Address</label>
      <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="{{ old('address', $customer->address) }}">
      @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="row g-2 mb-2">
      <div class="col-6">
        <label class="form-label">City</label>
        <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city', $customer->city) }}">
        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col-6">
        <label class="form-label">State</label>
        <input type="text" class="form-control @error('state') is-invalid @enderror" name="state" value="{{ old('state', $customer->state) }}">
        @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="mb-2">
      <label class="form-label">Pincode</label>
      <input type="text" inputmode="numeric" maxlength="6" class="form-control @error('pin') is-invalid @enderror" name="pin" value="{{ old('pin', $customer->pin) }}">
      @error('pin')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="mb-2">
      <label class="form-label">PAN</label>
      <input type="text" maxlength="10" style="text-transform:uppercase;" class="form-control @error('pan') is-invalid @enderror" name="pan" value="{{ old('pan', $customer->pan) }}">
      @error('pan')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="mb-2">
      <label class="form-label">Aadhaar</label>
      <input type="text" inputmode="numeric" maxlength="12" class="form-control @error('aadhaar') is-invalid @enderror" name="aadhaar" value="{{ old('aadhaar', $customer->aadhaar) }}">
      @error('aadhaar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <button class="btn btn-primary-fin btn-sm w-100" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Save Details</button>
  </form>
</div>

<a href="{{ route('customer.orders.index') }}" class="card-flat p-3 mb-3 d-flex justify-content-between align-items-center" style="color:inherit;">
  <span><i class="fa-solid fa-receipt me-2 text-primary"></i>My Orders</span>
  <i class="fa-solid fa-chevron-right text-muted"></i>
</a>

<div class="card-flat p-3 mb-3">
  <div class="section-title mb-2">Change Password</div>
  <form method="POST" action="{{ route('customer.profile.password') }}">
    @csrf
    <div class="mb-2">
      <label class="form-label">Current Password</label>
      <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password">
      @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="mb-2">
      <label class="form-label">New Password</label>
      <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" minlength="6">
      @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="mb-2">
      <label class="form-label">Confirm New Password</label>
      <input type="password" class="form-control" name="password_confirmation" minlength="6">
    </div>
    <button class="btn btn-primary-fin btn-sm w-100" type="submit"><i class="fa-solid fa-key me-1"></i>Update Password</button>
  </form>
</div>

<div class="card-flat p-3 mb-3" id="passkeysCard">
  <div class="section-title mb-2">Biometric Login (Face ID / Fingerprint)</div>

  {{-- Inside the app: native biometric (Android BiometricPrompt / iOS
       LocalAuthentication), just an on/off toggle for this device --}}
  <div id="nativeBiometricBox" class="d-none">
    <div id="nativeBiometricEnabledRow" class="d-none">
      <div class="small-note mb-2"><i class="fa-solid fa-circle text-success" style="font-size:8px;"></i> Biometric login is enabled on this device.</div>
      <button type="button" id="nativeBiometricDisableBtn" class="btn btn-outline-danger btn-sm w-100"><i class="fa-solid fa-lock me-1"></i>Disable Biometric Login</button>
    </div>
    <div id="nativeBiometricDisabledRow" class="d-none">
      <div class="small-note mb-2">Not set up yet.</div>
      <button type="button" id="nativeBiometricEnableBtn" class="btn btn-outline-fin btn-sm w-100"><i class="fa-solid fa-fingerprint me-1"></i>Enable Biometric Login</button>
    </div>
    <div id="nativeBiometricError" class="small text-danger mt-1"></div>
  </div>
  <div id="nativeBiometricUnsupportedNote" class="small-note d-none mt-2">Biometric login isn't supported on this device.</div>

  {{-- In a browser: the existing WebAuthn passkey system, unaffected --}}
  <div id="passkeysBox" class="d-none">
    <div id="passkeysList">
      @forelse($passkeys as $pk)
        <div class="d-flex justify-content-between align-items-center py-1 border-bottom" data-passkey-row="{{ $pk->id }}">
          <div>
            <div class="fw-semibold small">{{ $pk->name }}</div>
            <div class="small-note">Added {{ $pk->created_at->format('d/m/Y') }}{{ $pk->last_used_at ? ' - Last used '.$pk->last_used_at->diffForHumans() : '' }}</div>
          </div>
          <button type="button" class="btn btn-sm btn-outline-danger" onclick="bpDeletePasskey({{ $pk->id }})"><i class="fa-solid fa-trash"></i></button>
        </div>
      @empty
        <div class="small-note mb-2">Not set up yet.</div>
      @endforelse
    </div>
    <div id="passkeyRegisterBox" class="d-none mt-2">
      <button type="button" id="passkeyRegisterBtn" class="btn btn-outline-fin btn-sm w-100"><i class="fa-solid fa-fingerprint me-1"></i>Add This Device</button>
      <div id="passkeyRegisterError" class="small text-danger mt-1"></div>
    </div>
    <div id="passkeyUnsupportedNote" class="small-note d-none mt-2">Biometric login isn't supported on this device/browser.</div>
  </div>
</div>

<div class="card-flat p-3 mb-3">
  <div class="section-title mb-2">Quick Login</div>
  @if($customer->user->hasPinEnabled())
    <div class="small-note mb-2"><i class="fa-solid fa-circle text-success" style="font-size:8px;"></i> PIN quick login is enabled on this device.</div>
    <form method="POST" action="{{ route('quick-login.disable') }}" onsubmit="return confirm('Turn off quick login on this device?');">
      @csrf
      <button class="btn btn-outline-danger btn-sm w-100" type="submit"><i class="fa-solid fa-lock me-1"></i>Disable Quick Login</button>
    </form>
  @else
    <div class="small-note mb-2">Not set up yet.</div>
    <a href="{{ route('quick-login.setup') }}" class="btn btn-outline-fin btn-sm w-100"><i class="fa-solid fa-unlock me-1"></i>Set Up Quick PIN Login</a>
  @endif
</div>
<form method="POST" action="{{ route('logout') }}">
  @csrf
  <button class="btn btn-outline-danger w-100 mb-4" type="submit"><i class="fa-solid fa-right-from-bracket me-1"></i>Logout</button>
</form>

@push('scripts')
<script>
bpReady(async function () {
  const inApp = window.Capacitor && window.Capacitor.isNativePlatform();

  if (inApp) {
    if (!window.BluePeakNativeBiometric || !(await window.BluePeakNativeBiometric.isSupported())) {
      document.getElementById('nativeBiometricUnsupportedNote').classList.remove('d-none');
      return;
    }

    const box = document.getElementById('nativeBiometricBox');
    const enabledRow = document.getElementById('nativeBiometricEnabledRow');
    const disabledRow = document.getElementById('nativeBiometricDisabledRow');
    const errEl = document.getElementById('nativeBiometricError');
    box.classList.remove('d-none');

    const refresh = async function () {
      const enabled = await window.BluePeakNativeBiometric.isEnabled();
      enabledRow.classList.toggle('d-none', !enabled);
      disabledRow.classList.toggle('d-none', enabled);
    };
    await refresh();

    document.getElementById('nativeBiometricEnableBtn').addEventListener('click', async function () {
      const btn = this;
      errEl.textContent = '';
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Waiting for Face ID / Fingerprint...';
      await window.BluePeakNativeBiometric.enable(refresh, function (message) {
        errEl.textContent = message;
      });
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-fingerprint me-1"></i>Enable Biometric Login';
    });

    document.getElementById('nativeBiometricDisableBtn').addEventListener('click', function () {
      if (!confirm('Turn off biometric login on this device?')) return;
      window.BluePeakNativeBiometric.disable(async function () {
        await refresh();
      }, function (message) {
        errEl.textContent = message;
      });
    });

    return;
  }

  if (!window.BluePeakPasskeys) return;
  document.getElementById('passkeysBox').classList.remove('d-none');

  if (await window.BluePeakPasskeys.passkeySupported()) {
    document.getElementById('passkeyRegisterBox').classList.remove('d-none');
  } else {
    document.getElementById('passkeyUnsupportedNote').classList.remove('d-none');
  }

  document.getElementById('passkeyRegisterBtn').addEventListener('click', function () {
    const btn = this;
    const errEl = document.getElementById('passkeyRegisterError');
    errEl.textContent = '';
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Waiting for Face ID / Fingerprint...';

    const deviceName = (navigator.userAgentData?.platform || navigator.platform || 'Device') + ' - added ' + new Date().toLocaleDateString();

    window.BluePeakPasskeys.passkeyRegister(deviceName, function () {
      window.location.reload();
    }, function (message) {
      errEl.textContent = message;
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-fingerprint me-1"></i>Add This Device';
    });
  });
});

function bpDeletePasskey(id) {
  if (!confirm('Remove this biometric login device?')) return;
  window.BluePeakPasskeys.passkeyDelete(id, function () {
    document.querySelector('[data-passkey-row="' + id + '"]')?.remove();
  }, function (message) {
    alert(message);
  });
}
</script>
@endpush
</x-customer-layout>
