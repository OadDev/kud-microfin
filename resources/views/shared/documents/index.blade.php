<x-app-layout :title="$title" :active="$active">
<div class="mb-3">
  <div class="section-title mb-0">Documents</div>
  <div class="page-sub">Generate Welcome Letters &amp; Sanction Letters, and manage signed copies</div>
</div>
<div class="row g-3">
  @forelse($customers as $c)
    @php $loan = $c->loans->sortByDesc('id')->first(); @endphp
    @if($loan)
    <div class="col-md-6 col-lg-4">
      <div class="card-flat p-3">
        <div class="fw-semibold">{{ $c->user->name }}</div>
        <div class="small-note mb-2">{{ $c->customer_code }} · {{ $loan->loan_account_no }}</div>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-sm btn-outline-fin" href="{{ route('documents.show', [$loan, 'welcome_letter']) }}" target="_blank"><i class="fa-solid fa-envelope-open-text me-1"></i>Welcome Letter</a>
          <a class="btn btn-sm btn-outline-fin" href="{{ route('documents.show', [$loan, 'sanction_letter']) }}" target="_blank"><i class="fa-solid fa-file-signature me-1"></i>Sanction Letter</a>
        </div>
      </div>
    </div>
    @endif
  @empty
    <div class="text-center text-muted-fin py-3">No customers found.</div>
  @endforelse
</div>
</x-app-layout>
