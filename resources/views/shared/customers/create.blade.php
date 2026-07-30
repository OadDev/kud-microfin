<x-app-layout :title="$title" :active="$active">

<div class="mb-3">
  <div class="section-title mb-0">Create Customer &amp; Loan</div>
  <div class="page-sub">Fill in personal, identity and loan details to onboard a new customer</div>
</div>

<div class="card-flat p-3 p-md-4">
  <form method="POST" action="{{ route('customers.store') }}" enctype="multipart/form-data" id="createCustomerForm">
    @csrf

    <div class="form-section-title"><i class="fa-solid fa-id-card me-2"></i>Personal Details</div>
    <div class="row g-3">
      <div class="col-md-4"><label class="form-label">Full Name *</label><input class="form-control @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name') }}" required></div>
      <div class="col-md-4"><label class="form-label">Father's / Guardian's Name</label><input class="form-control" name="father_name" value="{{ old('father_name') }}"></div>
      <div class="col-md-4"><label class="form-label">Gender</label>
        <select class="form-select" name="gender"><option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option></select>
      </div>
      <div class="col-md-4"><label class="form-label">Mobile Number *</label><input class="form-control @error('mobile') is-invalid @enderror" name="mobile" maxlength="10" value="{{ old('mobile') }}" required></div>
      <div class="col-md-4"><label class="form-label">Alternate Mobile Number</label><input class="form-control" name="alt_mobile" maxlength="10" value="{{ old('alt_mobile') }}"></div>
      <div class="col-md-4"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ old('email') }}"></div>
      <div class="col-md-4"><label class="form-label">Date of Birth</label><input type="date" class="form-control" name="dob" value="{{ old('dob') }}"></div>
      <div class="col-md-8"><label class="form-label">Full Address *</label><input class="form-control @error('address') is-invalid @enderror" name="address" value="{{ old('address') }}" required></div>
      <div class="col-md-4"><label class="form-label">City *</label><input class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city') }}" required></div>
      <div class="col-md-4"><label class="form-label">State</label><input class="form-control" name="state" value="{{ old('state') }}"></div>
      <div class="col-md-4"><label class="form-label">PIN Code</label><input class="form-control" name="pin" maxlength="6" value="{{ old('pin') }}"></div>
    </div>

    <div class="form-section-title"><i class="fa-solid fa-fingerprint me-2"></i>Identity Details</div>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">PAN Number *</label><input class="form-control text-uppercase @error('pan') is-invalid @enderror" name="pan" maxlength="10" value="{{ old('pan') }}" required></div>
      <div class="col-md-6"><label class="form-label">Aadhaar Number *</label><input class="form-control @error('aadhaar') is-invalid @enderror" name="aadhaar" maxlength="12" value="{{ old('aadhaar') }}" required></div>
      <div class="col-md-4"><label class="form-label">Profile Photo</label><input type="file" class="form-control" name="photo" accept="image/*"></div>
      <div class="col-md-4"><label class="form-label">Aadhaar Upload</label><input type="file" class="form-control" name="aadhaar_doc" accept="image/*,.pdf"></div>
      <div class="col-md-4"><label class="form-label">PAN Upload</label><input type="file" class="form-control" name="pan_doc" accept="image/*,.pdf"></div>
    </div>

    <div class="form-section-title"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Loan Details</div>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Loan Purpose *</label><input class="form-control @error('purpose') is-invalid @enderror" name="purpose" value="{{ old('purpose') }}" required placeholder="e.g. Business Expansion"></div>
      <div class="col-md-3"><label class="form-label">Principal Loan Amount *</label><input type="number" min="0" class="form-control" id="loan_principal" name="principal" value="{{ old('principal') }}" required></div>
      <div class="col-md-3"><label class="form-label">Total Interest *</label><input type="number" min="0" class="form-control" id="loan_interest" name="interest" value="{{ old('interest') }}" required></div>
      <div class="col-md-3"><label class="form-label">Processing Fee</label><input type="number" min="0" class="form-control" id="loan_fee" name="fee" value="{{ old('fee', 0) }}"></div>
      <div class="col-md-3"><label class="form-label">Number of EMIs *</label><input type="number" min="1" class="form-control" id="loan_numEmis" name="num_emis" value="{{ old('num_emis') }}" required></div>
      <div class="col-md-3"><label class="form-label">EMI Frequency</label>
        <select class="form-select" id="loan_frequency" name="frequency"><option value="Monthly">Monthly</option><option value="Weekly">Weekly</option></select>
      </div>
      <div class="col-md-3"><label class="form-label">Loan Start Date *</label><input type="date" class="form-control" name="start_date" value="{{ old('start_date') }}" required></div>
      <div class="col-md-3"><label class="form-label">First EMI Due Date *</label><input type="date" class="form-control" name="first_due_date" value="{{ old('first_due_date') }}" required></div>
      <div class="col-md-3"><label class="form-label">Late Fee (per EMI)</label><input type="number" min="0" class="form-control" name="late_fee" value="{{ old('late_fee', 200) }}"></div>
      <div class="col-md-9">
        <label class="form-label">Assigned Shop Owner</label>
        @if($isAdmin)
          <select class="form-select" name="shop_owner_id" required>
            @foreach($shopOwners as $o)
              <option value="{{ $o->id }}">{{ $o->user->name }} — {{ $o->shop_name }}</option>
            @endforeach
          </select>
        @else
          <input class="form-control" value="{{ auth()->user()->name }} (You)" disabled>
        @endif
      </div>
    </div>

    <div class="calc-box mt-4">
      <div class="row text-center g-2">
        <div class="col-6 col-md-3"><div class="small-note">Total Payable</div><div class="fw-bold fs-5" id="calc_totalPayable">₹0</div></div>
        <div class="col-6 col-md-3"><div class="small-note">EMI Amount</div><div class="fw-bold fs-5" id="calc_emiAmount">₹0</div></div>
        <div class="col-6 col-md-3"><div class="small-note">Number of EMIs</div><div class="fw-bold fs-5" id="calc_numEmis">0</div></div>
        <div class="col-6 col-md-3"><div class="small-note">Frequency</div><div class="fw-bold fs-5" id="calc_frequency">Monthly</div></div>
      </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
      <button type="button" class="btn btn-outline-fin" onclick="generateEmiPreview()"><i class="fa-solid fa-calendar-plus me-1"></i>Generate EMI Schedule</button>
      <button type="submit" class="btn btn-primary-fin"><i class="fa-solid fa-floppy-disk me-1"></i>Save Customer &amp; Loan</button>
    </div>

    <div id="emiPreviewHost" class="mt-4"></div>
  </form>
</div>

@if(session('success') && session('created_loan_id'))
<div class="modal fade" id="modalCustomerSuccess" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success-subtle"><h5 class="modal-title"><i class="fa-solid fa-circle-check text-success me-2"></i>Customer &amp; Loan Created</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">{{ session('success') }}</div>
      <div class="modal-footer flex-wrap">
        <a class="btn btn-outline-fin btn-sm" href="{{ route('customers.show', session('created_customer_id')) }}">View Customer</a>
        <a class="btn btn-outline-fin btn-sm" href="{{ route('documents.show', [session('created_loan_id'), 'welcome_letter']) }}">Generate Welcome Letter</a>
        <a class="btn btn-primary-fin btn-sm" href="{{ route('documents.show', [session('created_loan_id'), 'sanction_letter']) }}">Generate Sanction Letter</a>
      </div>
    </div>
  </div>
</div>
@endif

@push('scripts')
<script>
function updateLoanCalc(){
  const principal = Number(document.getElementById('loan_principal').value) || 0;
  const interest = Number(document.getElementById('loan_interest').value) || 0;
  const fee = Number(document.getElementById('loan_fee').value) || 0;
  const numEmis = Number(document.getElementById('loan_numEmis').value) || 0;
  const totalPayable = principal + interest + fee;
  const emiAmount = numEmis > 0 ? Math.round(totalPayable / numEmis) : 0;
  document.getElementById('calc_totalPayable').innerText = '₹' + totalPayable.toLocaleString('en-IN');
  document.getElementById('calc_emiAmount').innerText = '₹' + emiAmount.toLocaleString('en-IN');
  document.getElementById('calc_numEmis').innerText = numEmis;
  document.getElementById('calc_frequency').innerText = document.getElementById('loan_frequency').value;
}
['loan_principal','loan_interest','loan_fee','loan_numEmis','loan_frequency'].forEach(id=>{
  document.getElementById(id).addEventListener('input', updateLoanCalc);
  document.getElementById(id).addEventListener('change', updateLoanCalc);
});
updateLoanCalc();

function generateEmiPreview(){
  const principal = Number(document.getElementById('loan_principal').value) || 0;
  const interest = Number(document.getElementById('loan_interest').value) || 0;
  const fee = Number(document.getElementById('loan_fee').value) || 0;
  const numEmis = Number(document.getElementById('loan_numEmis').value) || 0;
  const frequency = document.getElementById('loan_frequency').value;
  const firstDue = document.querySelector('[name=first_due_date]').value;
  if(!principal || !interest || !numEmis || !firstDue){
    alert('Please fill principal, interest, number of EMIs and first due date first.');
    return;
  }
  const totalPayable = principal + interest + fee;
  const emiAmount = Math.round(totalPayable / numEmis);
  let rows = '', cards = '';
  let due = new Date(firstDue + 'T00:00:00');
  for(let i=1;i<=numEmis;i++){
    const d = new Date(due);
    if(frequency === 'Weekly') d.setDate(d.getDate() + 7*(i-1));
    else d.setMonth(d.getMonth() + (i-1));
    const ds = String(d.getDate()).padStart(2,'0')+'/'+String(d.getMonth()+1).padStart(2,'0')+'/'+d.getFullYear();
    rows += `<tr><td>${i}</td><td>${ds}</td><td>₹${emiAmount.toLocaleString('en-IN')}</td><td><span class="badge-status badge-warning">Upcoming</span></td></tr>`;
    cards += `<div class="data-card"><div class="dc-head"><div class="fw-bold">EMI #${i}</div><span class="badge-status badge-warning">Upcoming</span></div><div class="dc-row"><span class="dc-label">Due Date</span><span>${ds}</span></div><div class="dc-row"><span class="dc-label">Amount</span><span>₹${emiAmount.toLocaleString('en-IN')}</span></div></div>`;
  }
  document.getElementById('emiPreviewHost').innerHTML = `
    <div class="form-section-title"><i class="fa-solid fa-calendar-check me-2"></i>EMI Schedule Preview</div>
    <div class="card-flat p-0 table-responsive-fin"><table class="table table-fin mb-0"><thead><tr><th>EMI #</th><th>Due Date</th><th>Amount</th><th>Status</th></tr></thead><tbody>${rows}</tbody></table></div>
    <div class="data-cards">${cards}</div>`;
}

@if(session('success') && session('created_loan_id'))
document.addEventListener('DOMContentLoaded', function(){
  bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCustomerSuccess')).show();
});
@endif
</script>
@endpush
</x-app-layout>
