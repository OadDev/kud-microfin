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

      // Clear out any stale key first. If a fingerprint/face was added or removed on this
      // device since a key was last created here (including a previous failed enable
      // attempt), Android auto-invalidates that key -- the fingerprint scan itself still
      // succeeds, but the crypto operation behind setData() then fails right after,
      // which is exactly the "prompt appears, then cancels itself" symptom. Deleting
      // first guarantees setData() always creates a fresh key against the device's
      // current biometric enrollment. Safe to ignore if there was nothing to delete.
      await NativeBiometric.deleteData({ key: STORAGE_KEY }).catch(function () {});

      await NativeBiometric.setData({
        key: STORAGE_KEY,
        value: JSON.stringify({ user_id: data.user_id, token: data.token }),
        accessControl: ACCESS_CONTROL_BIOMETRY_ANY,
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
