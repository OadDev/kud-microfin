<x-app-layout :title="$title" :active="$active">
<a class="btn btn-sm btn-outline-fin mb-3" href="{{ route('admin.notification-manager.templates') }}"><i class="fa-solid fa-arrow-left me-1"></i>Back to Templates</a>

<div class="mb-3">
  <div class="section-title mb-0">{{ $template->name }}</div>
  <div class="page-sub">{{ $template->description }}</div>
</div>

<form method="POST" action="{{ route('admin.notification-manager.templates.update', $template) }}">
  @csrf
  @method('PUT')
  <div class="card-flat p-3 mb-3">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ $template->is_active ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">Template active (master switch — turn off to stop sending this notification entirely)</label>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card-flat p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="section-title mb-0"><i class="fa-solid fa-envelope me-2"></i>Email</div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="email_enabled" value="1" {{ $template->email_enabled ? 'checked' : '' }}>
          </div>
        </div>
        <div class="mb-2"><label class="form-label">Subject</label><input class="form-control" name="email_subject" value="{{ $template->email_subject }}"></div>
        <div class="mb-2"><label class="form-label">Body</label><textarea class="form-control" name="email_body" rows="8">{{ $template->email_body }}</textarea></div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card-flat p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="section-title mb-0"><i class="fa-solid fa-bell me-2"></i>Push Notification</div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="push_enabled" value="1" {{ $template->push_enabled ? 'checked' : '' }}>
          </div>
        </div>
        <div class="mb-2"><label class="form-label">Title</label><input class="form-control" name="push_title" value="{{ $template->push_title }}"></div>
        <div class="mb-2"><label class="form-label">Body</label><textarea class="form-control" name="push_body" rows="3" maxlength="255">{{ $template->push_body }}</textarea></div>
      </div>
    </div>
  </div>

  <div class="mt-3">
    <button class="btn btn-primary-fin" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Save Template</button>
  </div>
</form>
</x-app-layout>
