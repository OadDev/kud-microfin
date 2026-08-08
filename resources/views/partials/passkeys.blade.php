<script>
(function () {
  function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content
      || document.querySelector('input[name="_token"]')?.value
      || '';
  }

  function bufToBase64url(buf) {
    const bytes = new Uint8Array(buf);
    let str = '';
    for (let i = 0; i < bytes.length; i++) str += String.fromCharCode(bytes[i]);
    return btoa(str).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
  }

  function base64urlToBuf(base64url) {
    const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
    const pad = base64.length % 4 === 0 ? '' : '='.repeat(4 - (base64.length % 4));
    const str = atob(base64 + pad);
    const bytes = new Uint8Array(str.length);
    for (let i = 0; i < str.length; i++) bytes[i] = str.charCodeAt(i);
    return bytes.buffer;
  }

  // Prefer the browser's native (spec) conversion when available; fall
  // back to a manual equivalent for older WebViews/browsers.
  function parseRequestOptions(options) {
    if (window.PublicKeyCredential && PublicKeyCredential.parseRequestOptionsFromJSON) {
      return PublicKeyCredential.parseRequestOptionsFromJSON(options);
    }
    const o = Object.assign({}, options);
    o.challenge = base64urlToBuf(options.challenge);
    if (options.allowCredentials) {
      o.allowCredentials = options.allowCredentials.map(c => Object.assign({}, c, { id: base64urlToBuf(c.id) }));
    }
    return o;
  }

  function parseCreationOptions(options) {
    if (window.PublicKeyCredential && PublicKeyCredential.parseCreationOptionsFromJSON) {
      return PublicKeyCredential.parseCreationOptionsFromJSON(options);
    }
    const o = Object.assign({}, options);
    o.challenge = base64urlToBuf(options.challenge);
    o.user = Object.assign({}, options.user, { id: base64urlToBuf(options.user.id) });
    if (options.excludeCredentials) {
      o.excludeCredentials = options.excludeCredentials.map(c => Object.assign({}, c, { id: base64urlToBuf(c.id) }));
    }
    return o;
  }

  function assertionToJSON(cred) {
    if (cred.toJSON) return cred.toJSON();
    return {
      id: cred.id,
      rawId: bufToBase64url(cred.rawId),
      type: cred.type,
      response: {
        clientDataJSON: bufToBase64url(cred.response.clientDataJSON),
        authenticatorData: bufToBase64url(cred.response.authenticatorData),
        signature: bufToBase64url(cred.response.signature),
        userHandle: cred.response.userHandle ? bufToBase64url(cred.response.userHandle) : null,
      },
      clientExtensionResults: cred.getClientExtensionResults ? cred.getClientExtensionResults() : {},
    };
  }

  function attestationToJSON(cred) {
    if (cred.toJSON) return cred.toJSON();
    return {
      id: cred.id,
      rawId: bufToBase64url(cred.rawId),
      type: cred.type,
      response: {
        clientDataJSON: bufToBase64url(cred.response.clientDataJSON),
        attestationObject: bufToBase64url(cred.response.attestationObject),
        transports: cred.response.getTransports ? cred.response.getTransports() : [],
      },
      clientExtensionResults: cred.getClientExtensionResults ? cred.getClientExtensionResults() : {},
    };
  }

  // Retries once on 419 (CSRF token page mismatch) -- a stale tab, or a
  // session write race under rapid back-to-back requests, both resolve on
  // a single retry since the token embedded in the page is still valid a
  // moment later.
  async function fetchWithRetry(url, opts) {
    let res = await fetch(url, opts);
    if (res.status === 419) {
      await new Promise(r => setTimeout(r, 300));
      res = await fetch(url, opts);
    }
    return res;
  }

  async function passkeySupported() {
    if (!window.PublicKeyCredential) return false;
    try {
      return await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
    } catch (e) {
      return false;
    }
  }

  async function passkeyLogin(onError) {
    try {
      const optRes = await fetch('/passkeys/login/options', { headers: { Accept: 'application/json' } });
      if (!optRes.ok) throw new Error('Could not start passkey login.');
      const { options } = await optRes.json();

      const credential = await navigator.credentials.get({ publicKey: parseRequestOptions(options) });

      const loginRes = await fetchWithRetry('/passkeys/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({ credential: assertionToJSON(credential) }),
      });

      if (loginRes.ok) {
        const data = await loginRes.json();
        window.location.href = data.redirect || '/customer/home';
        return;
      }

      const err = await loginRes.json().catch(() => ({}));
      onError?.(err.message || Object.values(err.errors || {})[0]?.[0] || 'Passkey login failed.');
    } catch (e) {
      if (e.name !== 'NotAllowedError') {
        onError?.('Passkey login was cancelled or is not available on this device.');
      }
    }
  }

  async function passkeyRegister(name, onSuccess, onError) {
    try {
      const optRes = await fetch('/user/passkeys/options', { headers: { Accept: 'application/json' } });
      if (!optRes.ok) throw new Error('Could not start passkey registration.');
      const { options } = await optRes.json();

      const credential = await navigator.credentials.create({ publicKey: parseCreationOptions(options) });

      const storeRes = await fetchWithRetry('/user/passkeys', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({ name: name, credential: attestationToJSON(credential) }),
      });

      if (storeRes.ok) {
        onSuccess?.();
        return;
      }

      const err = await storeRes.json().catch(() => ({}));
      onError?.(err.message || Object.values(err.errors || {})[0]?.[0] || 'Could not save passkey.');
    } catch (e) {
      if (e.name !== 'NotAllowedError') {
        onError?.('Passkey setup was cancelled or is not available on this device.');
      }
    }
  }

  async function passkeyDelete(id, onSuccess, onError) {
    try {
      const res = await fetchWithRetry('/user/passkeys/' + id, {
        method: 'DELETE',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
      });
      if (res.ok) {
        onSuccess?.();
      } else {
        onError?.('Could not remove passkey.');
      }
    } catch (e) {
      onError?.('Could not remove passkey.');
    }
  }

  window.BluePeakPasskeys = { passkeySupported, passkeyLogin, passkeyRegister, passkeyDelete };
})();
</script>
