<script>
(function () {
  // Only inside the Capacitor-wrapped app -- a normal browser tab already
  // has its own fast, native navigation feel; adding an artificial delay
  // there would just make the website feel slower for no benefit. The
  // fade-in itself (see partials/styles.blade.php) is CSS-only and applies
  // everywhere, since that part costs nothing.
  if (!window.Capacitor || !window.Capacitor.isNativePlatform()) {
    return;
  }

  document.addEventListener('click', function (e) {
    var a = e.target.closest('a[href]');
    if (!a || a.target === '_blank' || a.hasAttribute('download')) return;
    if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;

    var href = a.getAttribute('href');
    if (!href || /^(#|javascript:|tel:|mailto:)/.test(href)) return;

    var url;
    try {
      url = new URL(href, window.location.href);
    } catch (err) {
      return;
    }
    if (url.origin !== window.location.origin || url.href === window.location.href) return;

    e.preventDefault();
    document.documentElement.classList.add('bp-page-leaving');
    // Matches the CSS fade-out duration -- short enough to read as a
    // transition, not as lag.
    setTimeout(function () {
      window.location.href = url.href;
    }, 100);
  }, true);
})();
</script>
