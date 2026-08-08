@php $user = auth()->user(); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? 'BluePeak Fintech' }}</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<meta name="theme-color" content="#0c2053">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@include('partials.styles')
</head>
<body>
<div id="customerApp">
  <div class="cust-header">
    <div class="cust-header-top">
      <div class="min-w-0">
        <div class="cust-greeting">Welcome back,</div>
        <div class="cust-username">{{ $user->name }}</div>
      </div>
      <div class="cust-header-actions">
        <a href="{{ route('customer.cart.index') }}" class="icon-btn" title="Cart">
          <i class="fa-solid fa-cart-shopping"></i>
          @if(($cartCount ?? 0) > 0)
            <span class="icon-badge">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
          @endif
        </a>
        <a href="{{ route('customer.helpline') }}" class="icon-btn" title="Helpline"><i class="fa-solid fa-headset"></i></a>
        <a href="#" class="icon-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
      </div>
    </div>
    @if(! empty($pageTitle) || (isset($pageActions) && trim($pageActions)))
      <div class="cust-header-page">
        <div class="d-flex align-items-center gap-2 min-w-0">
          @if(! empty($backUrl))
            <a href="{{ $backUrl }}" class="icon-btn icon-btn-muted" title="Back"><i class="fa-solid fa-chevron-left"></i></a>
          @endif
          @if(! empty($pageTitle))
            <div class="cust-page-title">{{ $pageTitle }}</div>
          @endif
        </div>
        @isset($pageActions)
          <div class="cust-header-actions">{{ $pageActions }}</div>
        @endisset
      </div>
    @endif
  </div>
  <div class="cust-content">
    @include('partials.toasts')
    {{ $slot }}
  </div>
  <div class="bottom-nav">
    <a class="bn-item {{ ($active ?? '') === 'home' ? 'active' : '' }}" href="{{ route('customer.home') }}"><i class="fa-solid fa-house"></i>Home</a>
    <a class="bn-item {{ ($active ?? '') === 'loan' ? 'active' : '' }}" href="{{ route('customer.loan') }}"><i class="fa-solid fa-file-invoice-dollar"></i>Loan</a>
    <a class="bn-item {{ ($active ?? '') === 'pay' ? 'active' : '' }}" href="{{ route('customer.pay') }}"><i class="fa-solid fa-indian-rupee-sign"></i>Pay EMI</a>
    <a class="bn-item {{ ($active ?? '') === 'products' ? 'active' : '' }}" href="{{ route('customer.products.index') }}"><i class="fa-solid fa-bag-shopping"></i>Shop</a>
    <a class="bn-item {{ ($active ?? '') === 'documents' ? 'active' : '' }}" href="{{ route('customer.documents') }}"><i class="fa-solid fa-file-lines"></i>Documents</a>
    <a class="bn-item {{ ($active ?? '') === 'profile' ? 'active' : '' }}" href="{{ route('customer.profile') }}"><i class="fa-solid fa-user"></i>Profile</a>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function copyToClipboard(text, label){
  if(navigator.clipboard && navigator.clipboard.writeText){
    navigator.clipboard.writeText(text);
  }
}
</script>
@include('partials.passkeys')
@include('partials.onesignal-bridge')
@include('partials.native-back-button')
@stack('scripts')
</body>
</html>
