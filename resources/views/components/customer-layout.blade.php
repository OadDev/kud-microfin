@php $user = auth()->user(); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
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
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <div style="font-size:.8rem;opacity:.85;">Welcome back,</div>
        <div class="fw-bold fs-5">{{ $user->name }}</div>
      </div>
      <div class="d-flex align-items-center gap-3">
        <a href="{{ route('customer.helpline') }}" style="color:#fff;" title="Helpline"><i class="fa-solid fa-headset"></i></a>
        <a href="#" style="color:#fff;" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
      </div>
    </div>
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
@stack('scripts')
</body>
</html>
