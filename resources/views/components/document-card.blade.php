@props(['label', 'icon', 'loan' => null, 'type' => null, 'previewable' => false, 'href' => null, 'note' => null])
@php
    $doc = $loan && $type ? $loan->documents->firstWhere('type', $type) : null;
    $note = $note ?? ($previewable
        ? ($doc && $doc->signed_file_path ? 'Signed copy uploaded' : 'Generated · Awaiting signed copy')
        : 'Available for this loan account');
@endphp
<div class="col-md-6 col-lg-4">
  <div class="doc-card h-100 d-flex flex-column">
    <div class="d-flex align-items-center gap-3 mb-2">
      <div class="stat-icon" style="background:var(--primary-light); color:var(--primary);"><i class="fa-solid {{ $icon }}"></i></div>
      <div class="fw-semibold">{{ $label }}</div>
    </div>
    <div class="small-note mb-3">{{ $note }}</div>
    <div class="mt-auto d-flex gap-2 flex-wrap">
      @if($href)
        <a class="btn btn-sm btn-outline-fin" href="{{ $href }}"><i class="fa-solid fa-arrow-right me-1"></i>Open</a>
      @elseif($previewable && $loan)
        <a class="btn btn-sm btn-outline-fin" href="{{ route('documents.show', [$loan, $type]) }}" target="_blank"><i class="fa-solid fa-eye me-1"></i>View</a>
        <a class="btn btn-sm btn-outline-fin" href="{{ route('documents.show', [$loan, $type]) }}?print=1" target="_blank"><i class="fa-solid fa-print me-1"></i>Print</a>
      @else
        <button class="btn btn-sm btn-outline-fin" type="button" disabled title="Simulated for demo"><i class="fa-solid fa-eye me-1"></i>View</button>
        <button class="btn btn-sm btn-outline-fin" type="button" disabled title="Simulated for demo"><i class="fa-solid fa-print me-1"></i>Print</button>
      @endif
      @unless($href)
        <button class="btn btn-sm btn-outline-secondary" type="button" disabled title="Simulated for demo"><i class="fa-solid fa-download me-1"></i>Download</button>
      @endunless
    </div>
  </div>
</div>
