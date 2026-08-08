@php
  $oneSignalAppId = \App\Models\NotificationSetting::current()->onesignal_app_id;
@endphp
@if($oneSignalAppId)
<script>
(function () {
  // Only relevant inside the Capacitor-wrapped Android/iOS app -- a normal
  // browser tab has no window.Capacitor and this is a silent no-op. The
  // OneSignal plugin JS is injected natively by Capacitor's bridge, not
  // loaded from this page, so it's only present inside the app shell.
  if (!window.Capacitor || !window.Capacitor.isNativePlatform() || !window.OneSignal) {
    return;
  }

  OneSignal.initialize('{{ $oneSignalAppId }}');
  OneSignal.Notifications.requestPermission();

  @auth
    // Ties this device to the logged-in customer's user id so Admin's
    // per-customer pushes (and automated order/loan/EMI notifications)
    // reach the right device -- see PushNotificationService::sendToUser().
    OneSignal.login('{{ auth()->id() }}');
  @else
    OneSignal.logout();
  @endauth
})();
</script>
@endif
