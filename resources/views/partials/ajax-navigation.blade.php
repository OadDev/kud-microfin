<script>
(function () {
  // Only the in-app customer shell gets this treatment -- the public site keeps plain
  // full-page navigation. Scoped to customer-layout pages only (this partial isn't
  // included in guest-layout), and further gated to native so browser customers see no
  // behaviour change at all.
  if (!window.Capacitor || !window.Capacitor.isNativePlatform()) return;

  var app = document.getElementById('customerApp');
  var pageScripts = document.getElementById('bpPageScripts');
  if (!app || !pageScripts) return;

  var bar = document.createElement('div');
  bar.id = 'bpNavProgress';
  bar.style.cssText = 'position:fixed;top:0;left:0;height:3px;width:0;background:#fff;z-index:9999;opacity:0;transition:width .25s ease,opacity .15s ease;pointer-events:none;';
  document.body.appendChild(bar);

  var inFlight = null;

  function showBar() {
    bar.style.transition = 'none';
    bar.style.width = '0';
    bar.style.opacity = '1';
    void bar.offsetWidth;
    bar.style.transition = 'width 1.2s cubic-bezier(.1,.6,.4,1),opacity .15s ease';
    bar.style.width = '80%';
  }
  function finishBar() {
    bar.style.transition = 'width .2s ease';
    bar.style.width = '100%';
    setTimeout(function () { bar.style.opacity = '0'; }, 150);
  }

  function isAjaxable(link) {
    if (!link || !link.href) return false;
    if (link.target && link.target !== '_self') return false;
    if (link.hasAttribute('download') || link.hasAttribute('data-no-ajax') || link.hasAttribute('onclick')) return false;
    var url;
    try { url = new URL(link.href, location.href); } catch (e) { return false; }
    if (url.origin !== location.origin) return false;
    if (!/^https?:$/.test(url.protocol)) return false;
    if (url.pathname === location.pathname && url.search === location.search && url.hash) return false;
    return true;
  }

  function runPageScripts(sourceEl) {
    var scripts = sourceEl.querySelectorAll('script');
    scripts.forEach(function (old) {
      var s = document.createElement('script');
      for (var i = 0; i < old.attributes.length; i++) {
        s.setAttribute(old.attributes[i].name, old.attributes[i].value);
      }
      s.textContent = old.textContent;
      old.parentNode.replaceChild(s, old);
    });
  }

  function initCarousels() {
    if (!window.bootstrap || !window.bootstrap.Carousel) return;
    app.querySelectorAll('[data-bs-ride="carousel"]').forEach(function (el) {
      new window.bootstrap.Carousel(el);
    });
  }

  function swap(html, url) {
    var doc = new DOMParser().parseFromString(html, 'text/html');
    var newApp = doc.getElementById('customerApp');
    var newScripts = doc.getElementById('bpPageScripts');
    if (!newApp || !newScripts) return false;

    document.title = doc.title;
    app.innerHTML = newApp.innerHTML;
    pageScripts.innerHTML = newScripts.innerHTML;
    initCarousels();
    // Page scripts normally live in #bpPageScripts (outside #customerApp, via
    // @push('scripts')) and are re-run below. This extra pass is a safety net for any
    // stray <script> left directly in a page's content -- innerHTML-injected <script>
    // tags are inert by spec, so without this they'd silently never run after a swap.
    runPageScripts(app);
    runPageScripts(pageScripts);
    app.scrollTop = 0;
    window.scrollTo(0, 0);
    document.dispatchEvent(new CustomEvent('bp:pageready'));
    return true;
  }

  function navigate(url, push) {
    if (inFlight) inFlight.abort();
    var controller = new AbortController();
    inFlight = controller;
    showBar();

    fetch(url, {
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      signal: controller.signal,
    }).then(function (res) {
      if (inFlight !== controller) return;
      var ct = res.headers.get('content-type') || '';
      if (!res.ok || ct.indexOf('text/html') === -1) {
        window.location.href = res.url || url;
        return;
      }
      return res.text().then(function (html) {
        if (inFlight !== controller) return;
        var ok = swap(html, res.url || url);
        if (!ok) {
          window.location.href = res.url || url;
          return;
        }
        if (push) history.pushState({ bpAjax: true }, '', res.url || url);
        finishBar();
        inFlight = null;
      });
    }).catch(function () {
      if (inFlight !== controller) return;
      // Network error, aborted differently than expected, etc. -- fall back to a real
      // navigation, which also lets the app's existing offline-page handling kick in.
      window.location.href = url;
    });
  }

  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var link = e.target.closest('a');
    if (!link || !app.contains(link) || !isAjaxable(link)) return;
    e.preventDefault();
    if (link.href === location.href) return;
    navigate(link.href, true);
  });

  window.addEventListener('popstate', function () {
    navigate(location.href, false);
  });

  // No initCarousels() call here for the very first real page load -- Bootstrap's own
  // data-api already handles `data-bs-ride="carousel"` elements present at that point
  // (via a window 'load' listener). Calling it again here too would double-initialize
  // whatever carousel is already on the page, leaking a duplicate autoplay interval.
  // initCarousels() inside swap() above is the only place it's needed, since Bootstrap's
  // one-time 'load' listener never fires again for later AJAX-swapped content.
})();
</script>
