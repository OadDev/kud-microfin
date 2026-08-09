<script>
(function () {
  // Only relevant inside the Capacitor-wrapped app. Browser biometric
  // login is the separate WebAuthn passkey system (partials/passkeys.blade.php)
  // -- this is specifically for here, where that doesn't work reliably
  // across devices (see mobile/README.md, "Biometric login").
  if (!window.Capacitor || !window.Capacitor.isNativePlatform() || !window.Capacitor.Plugins || !window.Capacitor.Plugins.NativeBiometric) {
    window.BluePeakNativeBiometric = { isSupported: async () => false, isEnabled: async () => false, login() {}, enable() {}, disable() {} };
    return;
  }

  var NativeBiometric = window.Capacitor.Plugins.NativeBiometric;
  var STORAGE_KEY = 'bp_biometric_login';
  // AccessControl.BIOMETRY_ANY from @capgo/capacitor-native-biometric --
  // hardcoded since this page has no bundler to import the enum from.
  var ACCESS_CONTROL_BIOMETRY_ANY = 2;
  // Without this, the plugin binds the encryption key to that exact BiometricPrompt
  // operation ("this exact instant"), which some devices' Keystore/Keymaster
  // implementations handle unreliably -- the fingerprint scan itself succeeds, then the
  // crypto operation right after it fails (surfaces as a null-message
  // IllegalBlockSizeException wrapping "key user not authenticated"). Setting a short
  // validity window instead ("authenticated within the last N seconds") routes the plugin
  // through its non-CryptoObject-bound path, which is documented as more robust across
  // devices, at the cost of a negligibly wider (and still very short) window than strict
  // per-operation binding -- a login/setup flow doesn't need atomic-operation strictness.
  var AUTH_VALIDITY_SECONDS = 30;

  function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
  }

  async function isSupported() {
    try {
      var result = await NativeBiometric.isAvailable({ useFallback: false });
      return !!result.isAvailable;
    } catch (e) {
      return false;
    }
  }

  async function isEnabled() {
    try {
      var result = await NativeBiometric.isDataSaved({ key: STORAGE_KEY });
      return !!result.isSaved;
    } catch (e) {
      return false;
    }
  }

  // redirectOverride: the app-lock screen already knows where it wants to
  // go back to (its own "next" value) and uses this instead of the
  // server's default post-login destination.
  async function login(onError, redirectOverride) {
    try {
      var stored = await NativeBiometric.getSecureData({
        key: STORAGE_KEY,
        reason: 'Log in to BluePeak Fintech',
        title: 'Biometric Login',
      });
      var payload = JSON.parse(stored.value);

      var res = await fetch('{{ route('biometric.login') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify(payload),
      });

      if (res.ok) {
        var data = await res.json();
        window.location.href = redirectOverride || data.redirect || '/customer/home';
        return;
      }

      onError?.('Biometric login failed. Please use your password.');
    } catch (e) {
      var detail = (e && (e.message || e.errorMessage)) || '';
      onError?.('Biometric login was cancelled or is not available right now.' + (detail ? ' (' + detail + ')' : ''));
    }
  }

  async function enable(onSuccess, onError) {
    try {
      if (!(await isSupported())) {
        onError?.('Biometric login is not supported on this device.');
        return;
      }

      var res = await fetch('{{ route('biometric.enable') }}', {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
      });
      if (!res.ok) {
        onError?.('Could not enable biometric login.');
        return;
      }
      var data = await res.json();

      // Clear out any key left over from an earlier attempt first, so setData() below
      // always starts from a clean slate. Safe to ignore if there was nothing to delete.
      await NativeBiometric.deleteData({ key: STORAGE_KEY }).catch(function () {});

      await NativeBiometric.setData({
        key: STORAGE_KEY,
        value: JSON.stringify({ user_id: data.user_id, token: data.token }),
        accessControl: ACCESS_CONTROL_BIOMETRY_ANY,
        authValidityDuration: AUTH_VALIDITY_SECONDS,
        title: 'Enable Biometric Login',
      });

      onSuccess?.();
    } catch (e) {
      // Surface the plugin's actual error text -- "cancelled or unavailable" alone isn't
      // enough to tell a user cancel apart from a keystore/device issue when there's no
      // way to pull logcat from the reporting device.
      var detail = (e && (e.message || e.errorMessage)) || '';
      onError?.('Biometric setup was cancelled or is not available right now.' + (detail ? ' (' + detail + ')' : ''));
    }
  }

  async function disable(onSuccess, onError) {
    try {
      await NativeBiometric.deleteData({ key: STORAGE_KEY });
      await fetch('{{ route('biometric.disable') }}', {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
      });
      onSuccess?.();
    } catch (e) {
      onError?.('Could not disable biometric login.');
    }
  }

  window.BluePeakNativeBiometric = { isSupported, isEnabled, login, enable, disable };
})();
</script>
