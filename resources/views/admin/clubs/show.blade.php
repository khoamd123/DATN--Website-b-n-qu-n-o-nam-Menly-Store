@extends('admin.layouts.app')

@section('title', 'Chi tiết câu lạc bộ: ' . $club->name)

@section('content')
<div class="content-header">
    <h1>Chi tiết câu lạc bộ: <span class="text-primary">{{ $club->name }}</span></h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.clubs') }}">Quản lý CLB</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
        </ol>
    </nav>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin chi tiết</h5>
                    <span class="badge bg-{{ $statusColors[$club->status] ?? 'secondary' }}">
                        {{ $statusLabels[$club->status] ?? ucfirst($club->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            @if($club->logo && file_exists(public_path($club->logo)))
                                <img src="{{ asset($club->logo) }}" alt="Logo {{ $club->name }}" class="img-fluid rounded border p-1 mb-3" style="max-height: 150px;">
                            @else
                                <div class="club-logo-fallback rounded d-flex align-items-center justify-content-center mx-auto mb-3" 
                                     style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 2.5rem; font-weight: bold;">
                                    {{ strtoupper(substr($club->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h2 class="card-title">{{ $club->name }}</h2>
                            <p class="text-muted">{{ $club->slug }}</p>
                            <hr>
                            <p><strong><i class="fas fa-tag me-2"></i>Lĩnh vực:</strong> {{ $club->field->name ?? 'Chưa xác định' }}</p>
                            <p><strong><i class="fas fa-user-crown me-2"></i>Chủ sở hữu:</strong> {{ $club->owner->name ?? 'Chưa xác định' }}</p>
                            <p><strong><i class="fas fa-calendar-alt me-2"></i>Ngày tạo:</strong> {{ $club->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5><i class="fas fa-file-alt me-2"></i>Mô tả</h5>
                        <div class="p-3 bg-light rounded">
                            {!! nl2br(e($club->description)) !!}
                        </div>
                    </div>
                </div>
            </div>

            @php
                $pendingMembers = $club->clubMembers->filter(function($member) {
                    return $member->status === 'pending';
                });
                $approvedMembers = $club->clubMembers->filter(function($member) {
                    return $member->status === 'approved' || $member->status === 'active';
                })->unique('user_id'); // Loại bỏ trùng lặp theo user_id
            @endphp

            @if($pendingMembers->isNotEmpty())
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-clock me-2"></i>Yêu cầu tham gia ({{ $pendingMembers->count() }})</h5>
                    <div>
                        <button class="btn btn-sm btn-success" onclick="handleBulkAction('approve')"><i class="fas fa-check-double me-1"></i>Duyệt mục đã chọn</button>
                        <button class="btn btn-sm btn-danger" onclick="handleBulkAction('reject')"><i class="fas fa-times-circle me-1"></i>Từ chối mục đã chọn</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%;"><input type="checkbox" id="selectAllCheckbox" title="Chọn tất cả"></th>
                                    <th style="width: 50%;">Thành viên</th>
                                    <th style="width: 25%;">Ngày gửi yêu cầu</th>
                                    <th style="width: 20%;" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingMembers as $member)
                                    <tr>
                                        <td><input type="checkbox" class="member-checkbox" value="{{ $member->id }}"></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($member->user->avatar && file_exists(public_path('uploads/avatars/' . basename($member->user->avatar))))
                                                    <img src="{{ asset('uploads/avatars/' . basename($member->user->avatar)) }}" 
                                                         alt="{{ $member->user->name }}" 
                                                         class="rounded-circle me-2" 
                                                         width="40" height="40"
                                                         style="object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle me-2 bg-primary text-white d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px; font-size: 16px; font-weight: bold;">
                                                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div style="min-width: 0;">
                                                    <strong class="text-truncate d-block">{{ $member->user->name }}</strong>
                                                    <small class="text-muted text-truncate d-block">{{ $member->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $member->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <form action="{{ route('admin.clubs.members.approve', ['club' => $club->id, 'member' => $member->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Duyệt"><i class="fas fa-check"></i></button>
                                                </form>
                                                
                                                <button type="button" class="btn btn-sm btn-danger" title="Từ chối"
                                                        data-bs-toggle="modal" data-bs-target="#rejectMemberModal"
                                                        data-member-id="{{ $member->id }}"
                                                        data-member-name="{{ $member->user->name }}"
                                                        data-action-url="{{ route('admin.clubs.members.reject', ['club' => $club->id, 'member' => $member->id]) }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Thành viên đã duyệt ({{ $approvedMembers->count() }})</h5>
                    <div>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                            <i class="fas fa-user-plus me-1"></i> Thêm thành viên
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 40%;">Thành viên</th>
                                    <th style="width: 20%;">Vai trò</th>
                                    <th style="width: 20%;">Ngày tham gia</th>
                                    <th style="width: 20%;">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($approvedMembers as $member)
                                    @if($member->user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($member->user->avatar && file_exists(public_path('uploads/avatars/' . basename($member->user->avatar))))
                                                    <img src="{{ asset('uploads/avatars/' . basename($member->user->avatar)) }}" 
                                                         alt="{{ $member->user->name }}" 
                                                         class="rounded-circle me-2" 
                                                         width="40" height="40"
                                                         style="object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle me-2 bg-primary text-white d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px; font-size: 16px; font-weight: bold;">
                                                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div style="min-width: 0;">
                                                    <strong class="text-truncate d-block">{{ $member->user->name }}</strong>
                                                    <small class="text-muted text-truncate d-block">{{ $member->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $role = $member->position ?? $member->role_in_club;
                                                $badgeColor = 'primary';
                                                if($role === 'leader' || $role === 'chunhiem') $badgeColor = 'danger';
                                                elseif($role === 'officer') $badgeColor = 'info';
                                                elseif($role === 'member' || $role === 'thanhvien') $badgeColor = 'success';
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }}">{{ ucfirst($role) }}</span>
                                        </td>
                                        <td>{{ $member->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-warning" title="Thay đổi vai trò" disabled><i class="fas fa-user-shield"></i></button>
                                                <button type="button" class="btn btn-sm btn-danger" title="Xóa thành viên"
                                                        data-bs-toggle="modal" data-bs-target="#removeMemberModal"
                                                        data-member-id="{{ $member->id }}"
                                                        data-member-name="{{ $member->user ? $member->user->name : 'Unknown' }}"
                                                        {{ $member->user_id == $club->owner_id ? 'disabled' : '' }}>
                                                    <i class="fas fa-user-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Chưa có thành viên nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Thống kê</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Thành viên đã duyệt
                            <span class="badge bg-primary rounded-pill">{{ $approvedMembers->count() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Bài viết
                            <span class="badge bg-info rounded-pill">{{ $club->posts?->count() ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Sự kiện
                            <span class="badge bg-success rounded-pill">{{ $club->events?->count() ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Chờ duyệt
                            <span class="badge bg-warning rounded-pill">{{ $pendingMembers->count() }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Hành động</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('admin.clubs.edit', $club->id) }}" class="btn btn-warning"><i class="fas fa-edit me-1"></i> Chỉnh sửa</a>
                    <a href="{{ route('admin.clubs') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Quay lại danh sách</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm thành viên -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addMemberModalLabel">Thêm thành viên mới vào CLB</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('admin.clubs.members.add', $club->id) }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="user_id" class="form-label">Chọn người dùng <span class="text-danger">*</span></label>
            <select class="form-select" id="user_id" name="user_id" required>
                <option value="" disabled selected>-- Chọn người dùng --</option>
                @foreach($addableUsers as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            @if($addableUsers->isEmpty())
                <div class="form-text text-muted">Tất cả người dùng đã là thành viên của câu lạc bộ này.</div>
            @endif
          </div>
          <div class="mb-3">
            <label for="role_in_club" class="form-label">Vai trò trong CLB <span class="text-danger">*</span></label>
            <select class="form-select" id="role_in_club" name="role_in_club" required>
                <option value="thanhvien" selected>Thành viên</option>
                <option value="chunhiem">Chủ nhiệm</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary" {{ $addableUsers->isEmpty() ? 'disabled' : '' }}>Thêm thành viên</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Xóa thành viên -->
<div class="modal fade" id="removeMemberModal" tabindex="-1" aria-labelledby="removeMemberModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="removeMemberModalLabel">Lý do xóa thành viên</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="removeMemberForm" method="POST" action="">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Bạn sắp xóa thành viên: <strong id="memberNameToRemove"></strong> khỏi câu lạc bộ.</p>
          <div class="mb-3">
            <label for="memberDeletionReason" class="form-label">Vui lòng nhập lý do xóa <span class="text-danger">*</span></label>
            <textarea class="form-control" id="memberDeletionReason" name="deletion_reason" rows="4" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-danger">Xác nhận xóa</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Từ chối thành viên -->
<div class="modal fade" id="rejectMemberModal" tabindex="-1" aria-labelledby="rejectMemberModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rejectMemberModalLabel">Lý do từ chối thành viên</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="rejectMemberForm" method="POST" action="">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Bạn sắp từ chối yêu cầu tham gia của: <strong id="memberNameToReject"></strong>.</p>
          <div class="mb-3">
            <label for="memberRejectionReason" class="form-label">Vui lòng nhập lý do từ chối <span class="text-danger">*</span></label>
            <textarea class="form-control" id="memberRejectionReason" name="rejection_reason" rows="4" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<style>
.table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
}
.table {
    table-layout: fixed;
    width: 100%;
}
tbody tr {
    height: auto !important;
}
tbody tr td img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 50%;
}
/* Fix flickering */
.table tbody {
    display: table-row-group;
}
.btn-group {
    display: flex;
    gap: 5px;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const memberCheckboxes = document.querySelectorAll('.member-checkbox');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            memberCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    var removeMemberModal = document.getElementById('removeMemberModal');
    if(removeMemberModal) {
        removeMemberModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var memberId = button.getAttribute('data-member-id');
            var memberName = button.getAttribute('data-member-name');

            var memberNameElement = removeMemberModal.querySelector('#memberNameToRemove');
            var form = removeMemberModal.querySelector('#removeMemberForm');

            memberNameElement.textContent = memberName;
            form.action = '{{ url("admin/clubs/".$club->id."/members") }}/' + memberId + '/remove';
        });
    }

    var rejectMemberModal = document.getElementById('rejectMemberModal');
    if(rejectMemberModal) {
        rejectMemberModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var memberName = button.getAttribute('data-member-name');
            var actionUrl = button.getAttribute('data-action-url');

            var memberNameElement = rejectMemberModal.querySelector('#memberNameToReject');
            var form = rejectMemberModal.querySelector('#rejectMemberForm');

            memberNameElement.textContent = memberName;
            form.action = actionUrl;
        });
    }
});

function handleBulkAction(action) {
    const selectedMembers = Array.from(document.querySelectorAll('.member-checkbox:checked')).map(cb => cb.value);

    if (selectedMembers.length === 0) {
        alert('Vui lòng chọn ít nhất một thành viên để thực hiện hành động.');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('admin.clubs.members.bulk-update', $club->id) }}';
    form.style.display = 'none';

    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);

    selectedMembers.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'member_ids[]';
        input.value = id;
        form.appendChild(input);
    });

    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = action;
    form.appendChild(actionInput);

    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
