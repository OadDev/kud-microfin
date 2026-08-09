<script>
(function () {
  // Only relevant inside the Capacitor-wrapped app. The native launch
  // splash is configured with launchAutoHide:false (see
  // mobile/capacitor.config.json) specifically so it stays up for however
  // long this page actually takes to load over the network, instead of a
  // guessed fixed duration that can either cut off too early (leaving a
  // blank white gap before this page paints) or hang around too long.
  // This script runs once the page has been parsed this far down, which is
  // the earliest reasonable "the app is ready to show" signal.
  if (!window.Capacitor || !window.Capacitor.isNativePlatform() || !window.Capacitor.Plugins || !window.Capacitor.Plugins.SplashScreen) {
    return;
  }
  window.Capacitor.Plugins.SplashScreen.hide();
})();
</script>
