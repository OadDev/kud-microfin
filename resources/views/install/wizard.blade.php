@php $allOk = collect($checks)->every(fn($c) => $c['ok']); @endphp
<x-guest-layout title="Setup — BluePeak Fintech">
<div class="d-flex align-items-center justify-content-center" style="min-height:100vh; background:linear-gradient(160deg,var(--accent) 0%,#123a7a 45%,var(--primary) 100%); padding:20px;">
  <div class="login-box" style="max-width:640px;">
    <div class="text-center mb-3">
      <div class="login-logo"><img src="{{ asset('images/logo-icon.png') }}" alt="BluePeak Fintech"></div>
      <h4 class="fw-bold brand-text mb-0">BluePeak Fintech Setup</h4>
      <div class="page-sub">One-time installation — configure your database and create the Admin account</div>
    </div>

    <div class="card-flat p-3 mb-3">
      <div class="section-title mb-2">Server Requirements</div>
      <div class="row g-1">
        @foreach($checks as $c)
          <div class="col-6" style="font-size:.8rem;">
            <i class="fa-solid {{ $c['ok'] ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger' }} me-1"></i>{{ $c['label'] }}
          </div>
        @endforeach
      </div>
    </div>

    @if(!$allOk)
      <div class="alert alert-danger" style="font-size:.85rem;">Some server requirements aren't met — fix these (ask your host to enable the missing PHP extensions, or check file permissions) before continuing.</div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger" style="font-size:.85rem;">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('install.store') }}">
      @csrf
      <div class="form-section-title">Database Connection</div>
      <div class="row g-2">
        <div class="col-8"><label class="form-label">DB Host</label><input class="form-control" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" required></div>
        <div class="col-4"><label class="form-label">Port</label><input class="form-control" name="db_port" value="{{ old('db_port', '3306') }}" required></div>
        <div class="col-12"><label class="form-label">Database Name</label><input class="form-control" name="db_database" value="{{ old('db_database') }}" required></div>
        <div class="col-6"><label class="form-label">DB Username</label><input class="form-control" name="db_username" value="{{ old('db_username') }}" required></div>
        <div class="col-6"><label class="form-label">DB Password</label><input type="password" class="form-control" name="db_password"></div>
      </div>

      <div class="form-section-title">Admin Account</div>
      <div class="row g-2">
        <div class="col-12"><label class="form-label">Full Name</label><input class="form-control" name="admin_name" value="{{ old('admin_name') }}" required></div>
        <div class="col-6"><label class="form-label">Mobile Number</label><input class="form-control" name="admin_mobile" maxlength="10" value="{{ old('admin_mobile') }}" required></div>
        <div class="col-6"><label class="form-label">Email</label><input type="email" class="form-control" name="admin_email" value="{{ old('admin_email') }}"></div>
        <div class="col-6"><label class="form-label">Password</label><input type="password" class="form-control" name="admin_password" required></div>
        <div class="col-6"><label class="form-label">Confirm Password</label><input type="password" class="form-control" name="admin_password_confirmation" required></div>
      </div>

      <div class="form-check mt-3">
        <input class="form-check-input" type="checkbox" name="load_demo_data" value="1" id="load_demo_data" checked>
        <label class="form-check-label small-note" for="load_demo_data">Load sample demo data (shop owners, customers, loans) — useful for a walkthrough, remove before handing to real users</label>
      </div>

      <button class="btn btn-primary-fin w-100 mt-3" type="submit" {{ $allOk ? '' : 'disabled' }}><i class="fa-solid fa-rocket me-1"></i> Install BluePeak Fintech</button>
    </form>
  </div>
</div>
</x-guest-layout>
