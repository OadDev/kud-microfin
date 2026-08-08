<x-app-layout :title="$title" :active="$active">
<div class="mb-3">
  <div class="section-title mb-0">Notification Manager</div>
  <div class="page-sub">Connect OneSignal (push) and SMTP (email) so customers get automatic order/loan/EMI notifications</div>
</div>
@include('admin.notifications._subnav')

<form method="POST" action="{{ route('admin.notification-manager.settings.update') }}">
  @csrf
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card-flat p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="section-title mb-0"><i class="fa-solid fa-bell me-2"></i>OneSignal (Push Notifications)</div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="onesignal_enabled" value="1" {{ $settings->onesignal_enabled ? 'checked' : '' }}>
          </div>
        </div>
        <div class="small-note mb-2">Create a free app at <strong>onesignal.com</strong>, then paste its App ID and REST API Key here. The Android/iOS apps use this same App ID to register for push.</div>
        <div class="mb-2"><label class="form-label">OneSignal App ID</label><input class="form-control" name="onesignal_app_id" value="{{ $settings->onesignal_app_id }}" placeholder="e.g. 8f4b1c2a-....."></div>
        <div class="mb-2">
          <label class="form-label">REST API Key</label>
          <input type="password" class="form-control" name="onesignal_api_key" autocomplete="new-password" placeholder="{{ $settings->onesignal_api_key ? '•••••••••••••••• (leave blank to keep current)' : 'Paste your REST API key' }}">
        </div>
        <div class="small-note">
          <i class="fa-solid fa-circle {{ $settings->pushReady() ? 'text-success' : 'text-danger' }}" style="font-size:8px;"></i>
          Push is currently <strong>{{ $settings->pushReady() ? 'ready' : 'not configured' }}</strong>.
        </div>
      </div>

      <div class="card-flat p-3">
        <div class="section-title mb-2"><i class="fa-solid fa-clock me-2"></i>EMI Reminder Timing</div>
        <div class="small-note mb-2">How many days before an EMI's due date should the automatic reminder go out?</div>
        <div class="mb-2">
          <label class="form-label">Days Before Due Date</label>
          <input type="number" min="1" max="30" class="form-control" name="emi_reminder_days_before" value="{{ $settings->emi_reminder_days_before }}" required style="max-width:140px;">
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card-flat p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="section-title mb-0"><i class="fa-solid fa-envelope me-2"></i>SMTP (Email Notifications)</div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="smtp_enabled" value="1" {{ $settings->smtp_enabled ? 'checked' : '' }}>
          </div>
        </div>
        <div class="row g-2">
          <div class="col-8"><label class="form-label">SMTP Host</label><input class="form-control" name="smtp_host" value="{{ $settings->smtp_host }}" placeholder="smtp.example.com"></div>
          <div class="col-4"><label class="form-label">Port</label><input type="number" class="form-control" name="smtp_port" value="{{ $settings->smtp_port }}" placeholder="587"></div>
        </div>
        <div class="mb-2 mt-2"><label class="form-label">Username</label><input class="form-control" name="smtp_username" value="{{ $settings->smtp_username }}"></div>
        <div class="mb-2">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" name="smtp_password" autocomplete="new-password" placeholder="{{ $settings->smtp_password ? '•••••••••••••••• (leave blank to keep current)' : 'SMTP password' }}">
        </div>
        <div class="mb-2">
          <label class="form-label">Encryption</label>
          <select class="form-select" name="smtp_encryption">
            <option value="tls" {{ $settings->smtp_encryption === 'tls' ? 'selected' : '' }}>TLS (STARTTLS, usually port 587)</option>
            <option value="ssl" {{ $settings->smtp_encryption === 'ssl' ? 'selected' : '' }}>SSL (usually port 465)</option>
            <option value="" {{ blank($settings->smtp_encryption) ? 'selected' : '' }}>None</option>
          </select>
        </div>
        <div class="row g-2">
          <div class="col-6"><label class="form-label">From Address</label><input type="email" class="form-control" name="smtp_from_address" value="{{ $settings->smtp_from_address }}" placeholder="notifications@yourdomain.com"></div>
          <div class="col-6"><label class="form-label">From Name</label><input class="form-control" name="smtp_from_name" value="{{ $settings->smtp_from_name }}" placeholder="BluePeak Fintech"></div>
        </div>
        <div class="small-note mt-2">
          <i class="fa-solid fa-circle {{ $settings->mailReady() ? 'text-success' : 'text-danger' }}" style="font-size:8px;"></i>
          Email is currently <strong>{{ $settings->mailReady() ? 'ready' : 'not configured' }}</strong>.
        </div>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary-fin" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Save Settings</button>
      </div>
    </div>
  </div>
</form>

<div class="row g-3 mt-1">
  <div class="col-lg-6">
    <div class="card-flat p-3">
      <div class="section-title mb-2">Send Test Email</div>
      <form method="POST" action="{{ route('admin.notification-manager.settings.test-email') }}" class="d-flex gap-2">
        @csrf
        <input type="email" class="form-control" name="test_email" placeholder="you@email.com" required>
        <button class="btn btn-outline-fin text-nowrap" type="submit"><i class="fa-solid fa-paper-plane me-1"></i>Send</button>
      </form>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card-flat p-3">
      <div class="section-title mb-2">Send Test Push</div>
      <div class="small-note mb-2">Sends to every device currently subscribed in OneSignal.</div>
      <form method="POST" action="{{ route('admin.notification-manager.settings.test-push') }}">
        @csrf
        <button class="btn btn-outline-fin" type="submit"><i class="fa-solid fa-paper-plane me-1"></i>Send Test Push</button>
      </form>
    </div>
  </div>
</div>
</x-app-layout>
