@auth
@if(auth()->user()->isCustomer() && (auth()->user()->hasPinEnabled() || auth()->user()->hasBiometricEnabled()))
<script>
(function () {
  // Only relevant inside the Capacitor-wrapped app, and only once this
  // customer has actually turned on Quick PIN and/or biometric login. See
  // EnsureAppUnlocked for the server-side half of this.
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
      // backgrounding right now, nothing here waits for a response. This
      // is still sent (covers a fully-killed-and-relaunched app, which
      // skips the resume handler below entirely and depends on this
      // having reached the server already), but resuming below no longer
      // waits on or assumes it landed in time -- backgrounded apps can get
      // starved of CPU/network before a fire-and-forget request completes,
      // especially on a quick background-then-reopen.
      var body = new FormData();
      body.append('_token', '{{ csrf_token() }}');
      navigator.sendBeacon('{{ route('customer.lock.now') }}', body);
      return;
    }

    if (hasBackgrounded) {
      // This WebView stayed alive with the same page still sitting in
      // memory -- Capacitor doesn't reload on resume, so nothing would
      // otherwise hit the server (and EnsureAppUnlocked) again to notice
      // the unlock flag was just cleared above. Navigating straight to the
      // lock screen -- rather than a bare reload that trusts the beacon
      // above already landed -- re-locks reliably regardless of whether
      // that fire-and-forget request actually completed in time.
      var next = location.pathname + location.search;
      window.location.href = '{{ route('customer.lock.show') }}?next=' + encodeURIComponent(next);
    }
  });
})();
</script>
@endif
@endauth
