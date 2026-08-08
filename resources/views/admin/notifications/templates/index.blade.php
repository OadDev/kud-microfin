<x-app-layout :title="$title" :active="$active">
<div class="mb-3">
  <div class="section-title mb-0">Notification Manager</div>
  <div class="page-sub">Edit the email/push copy sent for each automated event</div>
</div>
@include('admin.notifications._subnav')

<div class="card-flat p-0 table-responsive-fin">
  <table class="table table-fin mb-0">
    <thead><tr><th>Event</th><th>Email</th><th>Push</th><th>Active</th><th>Actions</th></tr></thead>
    <tbody>
    @foreach($templates as $t)
      <tr>
        <td>
          <div class="fw-semibold">{{ $t->name }}</div>
          <div class="small-note">{{ $t->description }}</div>
        </td>
        <td><i class="fa-solid fa-circle {{ $t->email_enabled ? 'text-success' : 'text-danger' }}" style="font-size:8px;"></i> {{ $t->email_enabled ? 'On' : 'Off' }}</td>
        <td><i class="fa-solid fa-circle {{ $t->push_enabled ? 'text-success' : 'text-danger' }}" style="font-size:8px;"></i> {{ $t->push_enabled ? 'On' : 'Off' }}</td>
        <td><x-status-badge :status="$t->is_active ? 'Active' : 'Inactive'" /></td>
        <td><a class="btn btn-sm btn-outline-fin" href="{{ route('admin.notification-manager.templates.edit', $t) }}">Edit</a></td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
<div class="data-cards">
  @foreach($templates as $t)
    <div class="data-card">
      <div class="dc-head"><div class="fw-bold">{{ $t->name }}</div><x-status-badge :status="$t->is_active ? 'Active' : 'Inactive'" /></div>
      <div class="small-note mb-2">{{ $t->description }}</div>
      <div class="dc-row"><span class="dc-label">Email</span><span>{{ $t->email_enabled ? 'On' : 'Off' }}</span></div>
      <div class="dc-row"><span class="dc-label">Push</span><span>{{ $t->push_enabled ? 'On' : 'Off' }}</span></div>
      <a class="btn btn-sm btn-outline-fin w-100 mt-2" href="{{ route('admin.notification-manager.templates.edit', $t) }}">Edit</a>
    </div>
  @endforeach
</div>
</x-app-layout>
