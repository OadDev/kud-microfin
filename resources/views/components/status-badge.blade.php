@props(['status'])
@php
    $key = strtolower(str_replace(' ', '', $status));
    $class = match(true) {
        in_array($key, ['approved', 'paid', 'active']) => 'badge-success',
        in_array($key, ['pending', 'upcoming', 'duetoday']) => 'badge-warning',
        in_array($key, ['rejected', 'overdue', 'suspended', 'closed']) => 'badge-danger',
        $key === 'underverification' => 'badge-info',
        default => 'badge-secondary',
    };
@endphp
<span class="badge-status {{ $class }}"><i class="fa-solid fa-circle" style="font-size:6px;"></i> {{ $status }}</span>
