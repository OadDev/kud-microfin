<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000;">
  @foreach(['success' => 'fa-circle-check text-success', 'error' => 'fa-circle-xmark text-danger', 'warning' => 'fa-triangle-exclamation text-warning', 'info' => 'fa-circle-info text-primary'] as $key => $icon)
    @if(session($key))
      <div class="toast align-items-center border-0 shadow show" role="alert" data-bs-autohide="true" data-bs-delay="4000" style="min-width:280px;">
        <div class="d-flex">
          <div class="toast-body"><i class="fa-solid {{ $icon }} me-2"></i>{{ session($key) }}</div>
          <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    @endif
  @endforeach
  @if($errors->any())
    <div class="toast align-items-center border-0 shadow show" role="alert" data-bs-autohide="true" data-bs-delay="6000" style="min-width:280px;">
      <div class="d-flex">
        <div class="toast-body"><i class="fa-solid fa-circle-xmark text-danger me-2"></i>{{ $errors->first() }}</div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  @endif
</div>
