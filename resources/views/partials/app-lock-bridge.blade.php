@auth
@if(auth()->user()->isCustomer() && auth()->user()->hasPinEnabled())
<script>
(function () {
  // Only relevant inside the Capacitor-wrapped app, and only once this
  // customer has actually turned Quick PIN on. See EnsureAppUnlocked for
  // the server-side half of this.
  if (!window.Capacitor || !window.Capacitor.isNativePlatform() || !window.Capacitor.Plugins || !window.Capacitor.Plugins.App) {
    return;
  }

  var hasBackgrounded = false;

  window.Capacitor.Plugins.App.addListener('appStateChange', function (state) {
    if (!state.isActive) {
      hasBackgrounded = true;
      // sendBeacon can't set custom headers, so the CSRF token has to
      // travel in the body instead -- Laravel's CSRF check accepts it
      // from either place. Fire-and-forget by design: the app is
      // backgrounding right now, nothing here waits for a response.
      var body = new FormData();
      body.append('_token', '{{ csrf_token() }}');
      navigator.sendBeacon('{{ route('customer.lock.now') }}', body);
      return;
    }

    if (hasBackgrounded) {
      // This WebView stayed alive with the same page still sitting in
      // memory -- Capacitor doesn't reload on resume, so nothing would
      // otherwise hit the server (and EnsureAppUnlocked) again to notice
      // the unlock flag was just cleared above.
      window.location.reload();
    }
  });
})();
</script>
@endif
@endauth
