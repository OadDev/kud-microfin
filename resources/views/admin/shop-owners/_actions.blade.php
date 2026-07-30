<button class="btn btn-sm btn-outline-fin" data-bs-toggle="modal" data-bs-target="#modalViewShopOwner{{ $s->id }}" title="View"><i class="fa-solid fa-eye"></i></button>
@if($s->user->status === 'pending')
  <form method="POST" action="{{ route('admin.shop-owners.approve', $s) }}" class="d-inline">
    @csrf
    <button class="btn btn-sm btn-outline-success" type="submit" data-confirm="Approve {{ $s->user->name }} ({{ $s->shop_owner_code }}) as an active Shop Owner?" data-confirm-class="btn-success" title="Approve"><i class="fa-solid fa-check"></i></button>
  </form>
  <button class="btn btn-sm btn-outline-danger" type="button" onclick="openRejectModal('{{ route('admin.shop-owners.reject', $s) }}')" title="Reject"><i class="fa-solid fa-xmark"></i></button>
@elseif($s->user->status === 'approved')
  <form method="POST" action="{{ route('admin.shop-owners.suspend', $s) }}" class="d-inline">
    @csrf
    <button class="btn btn-sm btn-outline-warning" type="submit" data-confirm="Suspend {{ $s->user->name }} ({{ $s->shop_owner_code }})? They will not be able to create new customers." data-confirm-class="btn-warning" title="Suspend"><i class="fa-solid fa-ban"></i></button>
  </form>
@elseif($s->user->status === 'suspended')
  <form method="POST" action="{{ route('admin.shop-owners.approve', $s) }}" class="d-inline">
    @csrf
    <button class="btn btn-sm btn-outline-success" type="submit" data-confirm="Reactivate {{ $s->user->name }} ({{ $s->shop_owner_code }})?" title="Reactivate"><i class="fa-solid fa-rotate-left"></i></button>
  </form>
@endif
