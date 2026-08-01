@php
    $user = auth()->user();
    $isAdmin = $user->role === 'admin';
    $prefix = $isAdmin ? 'admin' : 'shopowner';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>{{ $title ?? 'Dashboard' }} - BluePeak Fintech</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
<meta name="theme-color" content="#0c2053">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@include('partials.styles')
</head>
<body>
<div class="app-shell">
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar(false)"></div>
  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <div class="logo-sm"><img src="{{ asset('images/logo-icon.png') }}" alt="BluePeak Fintech"></div>
      <div>
        <div class="fw-bold">BluePeak Fintech</div>
        <div style="font-size:.7rem; opacity:.75;">{{ $isAdmin ? 'Admin Panel' : 'Shop Owner Panel' }}</div>
      </div>
    </div>
    <nav>
      @if($isAdmin)
        <a class="nav-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a class="nav-link {{ ($active ?? '') === 'shop-owners' ? 'active' : '' }}" href="{{ route('admin.shop-owners.index') }}"><i class="fa-solid fa-store"></i> Shop Owners</a>
        <a class="nav-link {{ ($active ?? '') === 'customers' ? 'active' : '' }}" href="{{ route('admin.customers.index') }}"><i class="fa-solid fa-users"></i> Customers</a>
        <a class="nav-link {{ ($active ?? '') === 'create-customer' ? 'active' : '' }}" href="{{ route('admin.customers.create') }}"><i class="fa-solid fa-user-plus"></i> Create Customer</a>
        <a class="nav-link {{ ($active ?? '') === 'active-loans' ? 'active' : '' }}" href="{{ route('admin.loans.index') }}"><i class="fa-solid fa-file-invoice-dollar"></i> Active Loans</a>
        <a class="nav-link {{ ($active ?? '') === 'overdue-customers' ? 'active' : '' }}" href="{{ route('admin.customers.index', ['status' => 'overdue']) }}"><i class="fa-solid fa-triangle-exclamation"></i> Overdue Customers</a>
        <a class="nav-link {{ ($active ?? '') === 'loan-approvals' ? 'active' : '' }}" href="{{ route('admin.loan-approvals.index') }}"><i class="fa-solid fa-file-signature"></i> Loan Approvals</a>
        <a class="nav-link {{ ($active ?? '') === 'emi-calculator' ? 'active' : '' }}" href="{{ route('admin.emi-calculator') }}"><i class="fa-solid fa-calculator"></i> EMI Calculator</a>
        <a class="nav-link {{ ($active ?? '') === 'payment-verification' ? 'active' : '' }}" href="{{ route('admin.payment-verification.index') }}"><i class="fa-solid fa-magnifying-glass-dollar"></i> Payment Verification</a>
        <a class="nav-link {{ ($active ?? '') === 'payment-settings' ? 'active' : '' }}" href="{{ route('admin.payment-settings.edit') }}"><i class="fa-solid fa-gear"></i> Payment Settings</a>
        <a class="nav-link {{ ($active ?? '') === 'documents' ? 'active' : '' }}" href="{{ route('admin.documents.index') }}"><i class="fa-solid fa-file-lines"></i> Documents</a>
        <a class="nav-link {{ ($active ?? '') === 'banners' ? 'active' : '' }}" href="{{ route('admin.banners.index') }}"><i class="fa-solid fa-images"></i> Home Banners</a>
        <a class="nav-link {{ ($active ?? '') === 'categories' ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><i class="fa-solid fa-tags"></i> Categories</a>
        <a class="nav-link {{ ($active ?? '') === 'products' ? 'active' : '' }}" href="{{ route('admin.products.index') }}"><i class="fa-solid fa-box"></i> Products</a>
        <a class="nav-link {{ ($active ?? '') === 'orders' ? 'active' : '' }}" href="{{ route('admin.orders.index') }}"><i class="fa-solid fa-cart-shopping"></i> Orders</a>
      @else
        <a class="nav-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}" href="{{ route('shopowner.dashboard') }}"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a class="nav-link {{ ($active ?? '') === 'customers' ? 'active' : '' }}" href="{{ route('shopowner.customers.index') }}"><i class="fa-solid fa-users"></i> Customers</a>
        <a class="nav-link {{ ($active ?? '') === 'create-customer' ? 'active' : '' }}" href="{{ route('shopowner.customers.create') }}"><i class="fa-solid fa-user-plus"></i> Create Customer</a>
        <a class="nav-link {{ ($active ?? '') === 'active-loans' ? 'active' : '' }}" href="{{ route('shopowner.loans.index') }}"><i class="fa-solid fa-file-invoice-dollar"></i> Active Loans</a>
        <a class="nav-link {{ ($active ?? '') === 'emi-list' ? 'active' : '' }}" href="{{ route('shopowner.emis.index') }}"><i class="fa-solid fa-calendar-check"></i> EMI List</a>
        <a class="nav-link {{ ($active ?? '') === 'documents' ? 'active' : '' }}" href="{{ route('shopowner.documents.index') }}"><i class="fa-solid fa-file-lines"></i> Documents</a>
      @endif
      <hr style="border-color:rgba(255,255,255,.15); margin:8px 16px;">
      <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
    </nav>
  </aside>

  <div class="main-wrap">
    <div class="topbar">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
        <div>
          <div class="fw-bold">{{ $title ?? 'Dashboard' }}</div>
          <div class="page-sub">{{ now()->format('d/m/Y') }}</div>
        </div>
      </div>
      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:34px;height:34px;"><i class="fa-solid fa-user"></i></div>
          <span class="d-none d-sm-inline fw-semibold">{{ $user->name }}</span>
        </div>
      </div>
    </div>
    <div class="content-area">
      @include('partials.toasts')
      {{ $slot }}
    </div>
  </div>
</div>

@include('partials.confirm-modal')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
function toggleSidebar(force){
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const show = force !== undefined ? force : !sidebar.classList.contains('show');
  sidebar.classList.toggle('show', show);
  overlay.classList.toggle('show', show);
}
function copyToClipboard(text, label){
  if(navigator.clipboard && navigator.clipboard.writeText){
    navigator.clipboard.writeText(text);
  }
}
</script>
@stack('scripts')
</body>
</html>
