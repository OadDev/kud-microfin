<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Please Confirm</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="confirmModalText">Are you sure?</div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary-fin" id="confirmModalBtn" type="button">Confirm</button>
      </div>
    </div>
  </div>
</div>
<script>
(function(){
  var pendingForm = null;
  var modalEl = document.getElementById('confirmModal');
  var modal = null;
  document.addEventListener('click', function(e){
    var btn = e.target.closest('[data-confirm]');
    if(!btn) return;
    e.preventDefault();
    pendingForm = btn.closest('form');
    if(!pendingForm) return;
    document.getElementById('confirmModalText').textContent = btn.getAttribute('data-confirm');
    var confirmBtn = document.getElementById('confirmModalBtn');
    confirmBtn.className = 'btn ' + (btn.getAttribute('data-confirm-class') || 'btn-primary-fin');
    modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  });
  document.getElementById('confirmModalBtn').addEventListener('click', function(){
    if(pendingForm){ HTMLFormElement.prototype.submit.call(pendingForm); }
    if(modal) modal.hide();
  });
})();
</script>
