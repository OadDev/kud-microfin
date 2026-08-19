<x-guest-layout title="Privacy Policy — BluePeak Fintech">
<style>
  .mk-navbar{background:#fff; border-bottom:1px solid var(--border); padding:14px 0;}
  .mk-navbar .logo-sm{width:38px; height:38px; border-radius:9px; overflow:hidden;}
  .mk-navbar .logo-sm img{width:100%; height:100%; object-fit:cover;}
  .mk-footer{background:var(--primary-dark); color:rgba(255,255,255,.75); padding:36px 0;}
  .mk-footer a{color:#fff;}
  .pp-section{padding:48px 0 72px;}
  .pp-content h2{font-size:1.15rem; font-weight:800; margin-top:2.2rem; margin-bottom:.75rem;}
  .pp-content h2:first-child{margin-top:0;}
  .pp-content p, .pp-content li{color:var(--text-muted, #5a6472); line-height:1.7;}
  .pp-content ul{padding-left:1.2rem;}
  .pp-updated{color:var(--text-muted, #5a6472); font-size:.9rem;}
</style>

<nav class="mk-navbar">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="{{ route('marketing.home') }}" class="d-flex align-items-center gap-2 text-decoration-none">
      <div class="logo-sm"><img src="{{ asset('images/logo-icon.png') }}" alt="BluePeak Fintech"></div>
      <span class="fw-bold brand-text fs-5">BluePeak Fintech</span>
    </a>
    <div class="d-flex align-items-center gap-2 gap-md-3">
      <a href="{{ route('shop-owner.register') }}" class="btn btn-sm btn-outline-fin d-none d-md-inline-block">Register as Shop Owner</a>
      <a href="{{ route('shopowner.login') }}" class="btn btn-sm btn-outline-fin d-none d-md-inline-block">Shop Owner Login</a>
      <a href="{{ route('login') }}" class="btn btn-sm btn-primary-fin">Customer Login</a>
    </div>
  </div>
</nav>

<section class="pp-section">
  <div class="container" style="max-width:800px;">
    <div class="mb-4">
      <div class="section-title fs-3 mb-1">Privacy Policy</div>
      <div class="pp-updated">Last updated: {{ now()->format('F j, Y') }}</div>
    </div>

    <div class="pp-content">
      <p>BluePeak Fintech ("we", "us", "our") provides microfinance loan and EMI management services through our website and mobile application (together, the "Service"). This Privacy Policy explains what information we collect, how we use it, and the choices you have.</p>

      <h2>1. Information We Collect</h2>
      <p>To provide loan and payment services, we collect:</p>
      <ul>
        <li><strong>Identity &amp; contact details</strong> — name, mobile number, email address, and residential address.</li>
        <li><strong>KYC documents</strong> — PAN number, Aadhaar number, and photographs or scanned copies of identity/address proof required for loan verification.</li>
        <li><strong>Loan &amp; payment information</strong> — loan applications, EMI schedules, payment history, and payment proofs (e.g. UPI/bank transaction references or screenshots) you submit.</li>
        <li><strong>Account security data</strong> — your login credentials, PIN, and biometric authentication tokens (fingerprint/Face ID data itself never leaves your device; we only store a token confirming that biometric unlock succeeded).</li>
        <li><strong>Device &amp; notification data</strong> — device push-notification tokens, used to deliver EMI reminders and account alerts.</li>
        <li><strong>Usage information</strong> — basic app/website interaction logs used for security and troubleshooting.</li>
      </ul>
      <p>We do <strong>not</strong> collect your device's precise location (GPS) or background location data. The app does not request or use location permissions.</p>

      <h2>2. How We Use Your Information</h2>
      <ul>
        <li>To process loan applications, disburse funds, and manage EMI schedules and repayments.</li>
        <li>To verify your identity and comply with KYC and other regulatory requirements applicable to microfinance lending.</li>
        <li>To send EMI due-date reminders, payment confirmations, and other account notifications via push notification, SMS, or email.</li>
        <li>To verify payment proofs you submit and reconcile them against your loan account.</li>
        <li>To provide customer support and respond to your requests.</li>
        <li>To detect, prevent, and investigate fraud or unauthorised account access.</li>
      </ul>

      <h2>3. How We Store &amp; Protect Your Information</h2>
      <p>Passwords, PINs, and biometric confirmation tokens are stored using one-way hashing — we cannot see or recover your actual password, PIN, or biometric data. Access to KYC documents and loan data is restricted to authorised personnel (your registered Shop Partner and BluePeak Fintech administrators) who need it to process your loan.</p>

      <h2>4. Sharing With Third Parties</h2>
      <p>We share limited data with the following service providers, solely to operate the Service:</p>
      <ul>
        <li><strong>OneSignal</strong> — to deliver push notifications to your device (device token only, no financial data).</li>
        <li><strong>Razorpay</strong> — to process online payments you initiate (payment details are handled directly by Razorpay under its own privacy policy).</li>
        <li><strong>Email/SMS delivery providers</strong> — to send account notifications and reminders.</li>
      </ul>
      <p>We do not sell your personal information to anyone. We may disclose information where required by law or by a regulator overseeing microfinance/lending activity.</p>

      <h2>5. Data Retention</h2>
      <p>We retain loan, KYC, and payment records for as long as your account is active and thereafter for the period required by applicable financial recordkeeping regulations.</p>

      <h2>6. Your Choices &amp; Rights</h2>
      <ul>
        <li>You can review and update your address, PAN, and Aadhaar details at any time from <strong>Profile</strong> in the app.</li>
        <li>You can enable or disable PIN and biometric login from <strong>Profile → Security</strong>.</li>
        <li>To request correction or deletion of your data (subject to our regulatory recordkeeping obligations), contact us using the details below.</li>
      </ul>

      <h2>7. Children's Privacy</h2>
      <p>The Service is intended for adults capable of entering into a loan agreement and is not directed at children. We do not knowingly collect information from minors.</p>

      <h2>8. Changes to This Policy</h2>
      <p>We may update this Privacy Policy from time to time. Material changes will be reflected by updating the "Last updated" date above.</p>

      <h2>9. Contact Us</h2>
      <p>If you have questions about this Privacy Policy or how your data is handled, contact us at:</p>
      <p>
        <i class="fa-solid fa-phone me-2"></i><a href="tel:+917002128302">+91 70021 28302</a><br>
        <i class="fa-brands fa-whatsapp me-2"></i><a href="https://wa.me/917002128302" target="_blank" rel="noopener">WhatsApp Support</a>
      </p>
    </div>
  </div>
</section>

<footer class="mk-footer">
  <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 text-center text-md-start">
    <div>
      <div class="fw-bold text-white">BluePeak Fintech</div>
      <div class="small">&copy; {{ now()->year }} BluePeak Fintech. All rights reserved.</div>
    </div>
    <div class="d-flex gap-3 small">
      <a href="{{ route('marketing.home') }}">Home</a>
      <a href="{{ route('login') }}">Customer Login</a>
      <a href="tel:+917002128302">+91 70021 28302</a>
    </div>
  </div>
</footer>
</x-guest-layout>
