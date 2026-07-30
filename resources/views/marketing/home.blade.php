<x-guest-layout title="BluePeak Fintech — Microfinance Made Simple">
<style>
  .mk-navbar{background:#fff; border-bottom:1px solid var(--border); padding:14px 0;}
  .mk-navbar .logo-sm{width:38px; height:38px; border-radius:9px; overflow:hidden;}
  .mk-navbar .logo-sm img{width:100%; height:100%; object-fit:cover;}
  .mk-hero{background:linear-gradient(135deg,var(--primary) 0%,var(--accent) 100%); color:#fff; padding:70px 0 90px;}
  .mk-hero h1{font-weight:800; font-size:2.4rem;}
  .mk-hero .lead{opacity:.92; font-size:1.1rem;}
  .mk-hero-badge{background:rgba(255,255,255,.15); border-radius:30px; padding:6px 16px; display:inline-block; font-size:.8rem; margin-bottom:16px;}
  .mk-section{padding:64px 0;}
  .mk-feature-icon{width:56px; height:56px; border-radius:14px; background:var(--primary-light); color:var(--accent); display:flex; align-items:center; justify-content:center; font-size:1.4rem; margin-bottom:14px;}
  .mk-step-num{width:38px; height:38px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; margin-bottom:10px;}
  .mk-footer{background:var(--primary-dark); color:rgba(255,255,255,.75); padding:36px 0;}
  .mk-footer a{color:#fff;}
  .mk-cta-card{background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; border-radius:var(--radius);}
  @media (max-width:767px){ .mk-hero{padding:44px 0 60px;} .mk-hero h1{font-size:1.7rem;} }
</style>

<nav class="mk-navbar">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <div class="logo-sm"><img src="{{ asset('images/logo-icon.png') }}" alt="BluePeak Fintech"></div>
      <span class="fw-bold brand-text fs-5">BluePeak Fintech</span>
    </div>
    <div class="d-flex align-items-center gap-2 gap-md-3">
      <a href="#features" class="d-none d-md-inline text-decoration-none text-muted-fin small fw-semibold">Features</a>
      <a href="#how-it-works" class="d-none d-md-inline text-decoration-none text-muted-fin small fw-semibold">How It Works</a>
      <a href="#contact" class="d-none d-md-inline text-decoration-none text-muted-fin small fw-semibold">Contact</a>
      <a href="{{ route('shop-owner.register') }}" class="btn btn-sm btn-outline-fin">Register as Shop Owner</a>
      <a href="{{ route('login') }}" class="btn btn-sm btn-primary-fin">Login</a>
    </div>
  </div>
</nav>

<header class="mk-hero text-center text-md-start">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-md-7">
        <span class="mk-hero-badge"><i class="fa-solid fa-shield-halved me-1"></i>Trusted Microfinance Partner</span>
        <h1>Small loans, big possibilities — with EMIs you can actually plan around.</h1>
        <p class="lead mt-3 mb-4">BluePeak Fintech helps you access fast, transparent microloans through your nearest registered Shop Partner, track every EMI in one place, and even shop for everyday essentials with Cash on Delivery or online payment.</p>
        <div class="d-flex gap-2 justify-content-center justify-content-md-start flex-wrap">
          <a href="{{ route('login') }}" class="btn btn-light fw-semibold px-4"><i class="fa-solid fa-right-to-bracket me-1"></i>Customer / Shop Owner Login</a>
          <a href="{{ route('shop-owner.register') }}" class="btn btn-outline-light px-4"><i class="fa-solid fa-store me-1"></i>Become a Shop Partner</a>
        </div>
      </div>
      <div class="col-md-5 d-none d-md-block text-center">
        <i class="fa-solid fa-sack-dollar" style="font-size:9rem; opacity:.18;"></i>
      </div>
    </div>
  </div>
</header>

<section class="mk-section" id="features">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-title fs-4">Everything you need, in one app</div>
      <div class="page-sub">Built for customers, shop partners and admins alike</div>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <div class="card-flat p-4 h-100">
          <div class="mk-feature-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
          <div class="fw-bold mb-1">Quick Microloans</div>
          <div class="text-muted-fin small">Apply through your nearest registered Shop Partner and get a clear, upfront breakdown of your loan and EMI schedule.</div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card-flat p-4 h-100">
          <div class="mk-feature-icon"><i class="fa-solid fa-calendar-check"></i></div>
          <div class="fw-bold mb-1">Easy EMI Tracking</div>
          <div class="text-muted-fin small">See exactly what's due, when, and what you've already paid — with UPI, QR or bank transfer payment options.</div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card-flat p-4 h-100">
          <div class="mk-feature-icon"><i class="fa-solid fa-bag-shopping"></i></div>
          <div class="fw-bold mb-1">Shop for Essentials</div>
          <div class="text-muted-fin small">Browse everyday products and check out with Cash on Delivery or secure online payment via Razorpay.</div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card-flat p-4 h-100">
          <div class="mk-feature-icon"><i class="fa-solid fa-headset"></i></div>
          <div class="fw-bold mb-1">Always-On Support</div>
          <div class="text-muted-fin small">A dedicated helpline is one tap away from every screen — no digging through menus to get help.</div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="mk-section" id="how-it-works" style="background:var(--card); border-top:1px solid var(--border); border-bottom:1px solid var(--border);">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-title fs-4">How It Works</div>
      <div class="page-sub">From application to your last EMI, in three simple steps</div>
    </div>
    <div class="row g-4 text-center">
      <div class="col-md-4">
        <div class="mk-step-num mx-auto">1</div>
        <div class="fw-bold mb-1">Visit a Shop Partner</div>
        <div class="text-muted-fin small">Your nearest registered Shop Partner sets up your customer profile and loan application.</div>
      </div>
      <div class="col-md-4">
        <div class="mk-step-num mx-auto">2</div>
        <div class="fw-bold mb-1">Get Approved &amp; Funded</div>
        <div class="text-muted-fin small">Once approved, you receive a clear Sanction Letter with your full EMI schedule.</div>
      </div>
      <div class="col-md-4">
        <div class="mk-step-num mx-auto">3</div>
        <div class="fw-bold mb-1">Repay via Easy EMIs</div>
        <div class="text-muted-fin small">Pay each EMI from your phone and track status in real time until your loan is fully closed.</div>
      </div>
    </div>
  </div>
</section>

@if($featuredProducts->isNotEmpty())
<section class="mk-section">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-title fs-4">From the Shop</div>
      <div class="page-sub">Log in to browse the full catalog and buy with COD or Razorpay</div>
    </div>
    <div class="row g-3">
      @foreach($featuredProducts as $p)
        <div class="col-6 col-lg-3">
          <div class="card-flat p-0 overflow-hidden h-100">
            @if($p->image_path)
              <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($p->image_path) }}" alt="{{ $p->name }}" class="w-100" style="height:120px;object-fit:cover;">
            @else
              <div class="d-flex align-items-center justify-content-center bg-primary-subtle" style="height:120px;"><i class="fa-solid fa-box fa-2x text-primary"></i></div>
            @endif
            <div class="p-2">
              <div class="fw-semibold small">{{ $p->name }}</div>
              <div class="fw-bold text-primary">₹{{ number_format($p->price, 2) }}</div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<section class="mk-section" id="contact">
  <div class="container">
    <div class="mk-cta-card p-4 p-md-5 text-center">
      <i class="fa-solid fa-headset" style="font-size:2.2rem;"></i>
      <h4 class="fw-bold mt-3 mb-1">Have questions? We're here to help.</h4>
      <p class="mb-4" style="opacity:.9;">Call our support helpline for anything — loan queries, EMI payments, or shop orders.</p>
      <a href="tel:+917002128302" class="btn btn-light fw-semibold px-4"><i class="fa-solid fa-phone me-2"></i>+91 70021 28302</a>
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
      <a href="{{ route('login') }}">Login</a>
      <a href="{{ route('shop-owner.register') }}">Register as Shop Owner</a>
      <a href="tel:+917002128302">+91 70021 28302</a>
    </div>
  </div>
</footer>
</x-guest-layout>
