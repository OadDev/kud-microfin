<x-app-layout :title="$title" :active="$active">
<div class="mb-3">
  <div class="section-title mb-0">Notification Manager</div>
  <div class="page-sub">Manually push a message to app users, and see recent notification activity</div>
</div>
@include('admin.notifications._subnav')

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card-flat p-3">
      <div class="section-title mb-2"><i class="fa-solid fa-paper-plane me-2"></i>Send Push Notification</div>
      <form method="POST" action="{{ route('admin.notification-manager.send.store') }}">
        @csrf
        <div class="mb-2">
          <label class="form-label">Send To</label>
          <select class="form-select" name="target" id="targetSelect" onchange="document.getElementById('customerPicker').classList.toggle('d-none', this.value !== 'customer')">
            <option value="all">All App Users</option>
            <option value="customer">One Customer</option>
          </select>
        </div>
        <div class="mb-2 d-none" id="customerPicker">
          <label class="form-label">Customer</label>
          <select class="form-select" name="customer_id">
            @foreach($customers as $c)
              <option value="{{ $c->id }}">{{ $c->user->name }} ({{ $c->customer_code }})</option>
            @endforeach
          </select>
        </div>
        <div class="mb-2"><label class="form-label">Title</label><input class="form-control" name="push_title" maxlength="255" required></div>
        <div class="mb-3"><label class="form-label">Message</label><textarea class="form-control" name="push_body" rows="3" maxlength="255" required></textarea></div>
        <button class="btn btn-primary-fin w-100" type="submit"><i class="fa-solid fa-paper-plane me-1"></i>Send Now</button>
      </form>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card-flat p-0 table-responsive-fin">
      <div class="section-title p-3 pb-0">Recent Notification Activity</div>
      <table class="table table-fin mb-0">
        <thead><tr><th>Event</th><th>Customer</th><th>Channel</th><th>Status</th><th>When</th></tr></thead>
        <tbody>
        @forelse($logs as $log)
          <tr>
            <td>{{ $log->template_key === 'manual' ? 'Manual: '.$log->title : $log->title }}</td>
            <td>{{ $log->customer?->user?->name ?? 'All Users' }}</td>
            <td><i class="fa-solid {{ $log->channel === 'push' ? 'fa-bell' : 'fa-envelope' }} me-1"></i>{{ ucfirst($log->channel) }}</td>
            <td><x-status-badge :status="ucfirst($log->status)" /></td>
            <td>{{ $log->created_at->diffForHumans() }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted-fin py-3">No notifications sent yet.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
</x-app-layout>
