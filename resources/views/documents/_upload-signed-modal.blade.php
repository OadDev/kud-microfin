<div class="modal fade" id="modalUploadSigned" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('documents.signed.store', [$loan, $type]) }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Upload Signed Document</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Document Type</label>
            <input class="form-control" value="{{ $type === 'welcome_letter' ? 'Welcome Letter' : 'Loan Sanction Letter' }}" disabled>
          </div>
          <div class="mb-2"><label class="form-label">Upload Signed Document</label><input type="file" class="form-control" name="file" required></div>
          <div class="mb-2"><label class="form-label">Signed Date</label><input type="date" class="form-control" name="signed_at" value="{{ now()->format('Y-m-d') }}" required></div>
          <div class="mb-2"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary-fin" type="submit"><i class="fa-solid fa-check me-1"></i>Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>
