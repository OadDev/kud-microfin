<script>
(function () {
  // Only relevant inside the Capacitor-wrapped app -- a normal browser tab
  // has no window.Capacitor and this is a silent no-op. Without this,
  // Android's hardware/gesture back button has no JS listener to catch it,
  // so Capacitor's native side just closes the app outright instead of
  // navigating back through the page history.
  if (!window.Capacitor || !window.Capacitor.isNativePlatform() || !window.Capacitor.Plugins || !window.Capacitor.Plugins.App) {
    return;
  }

  window.Capacitor.Plugins.App.addListener('backButton', function (event) {
    if (event.canGoBack) {
      window.history.back();
    } else {
      // At the root of the app's own history -- exiting is the expected
      // native behaviour here (no-op on iOS, which doesn't allow this).
      window.Capacitor.Plugins.App.exitApp();
    }
  });
})();
</script>
