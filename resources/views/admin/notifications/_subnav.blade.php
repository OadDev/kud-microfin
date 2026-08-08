@php $current = $active ?? request()->route()->getName(); @endphp
<div class="d-flex gap-2 mb-3 flex-wrap">
  <a class="btn btn-sm {{ request()->routeIs('admin.notification-manager.settings*') ? 'btn-primary-fin' : 'btn-outline-fin' }}" href="{{ route('admin.notification-manager.settings') }}"><i class="fa-solid fa-gear me-1"></i>Settings</a>
  <a class="btn btn-sm {{ request()->routeIs('admin.notification-manager.templates*') ? 'btn-primary-fin' : 'btn-outline-fin' }}" href="{{ route('admin.notification-manager.templates') }}"><i class="fa-solid fa-file-lines me-1"></i>Templates</a>
  <a class="btn btn-sm {{ request()->routeIs('admin.notification-manager.send*') ? 'btn-primary-fin' : 'btn-outline-fin' }}" href="{{ route('admin.notification-manager.send') }}"><i class="fa-solid fa-paper-plane me-1"></i>Send &amp; Logs</a>
</div>
