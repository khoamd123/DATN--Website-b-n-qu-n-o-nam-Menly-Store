@extends('admin.layouts.app')

@section('title', 'Chi tiết câu lạc bộ: ' . $club->name)

@section('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Select2 styling */
.select2-container {
    width: 100% !important;
}
.select2-container--default .select2-selection--multiple {
    min-height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #0d6efd;
    border: 1px solid #0d6efd;
    color: white;
    padding: 2px 8px;
    margin: 3px;
    max-width: 100%;
    word-break: break-word;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: white;
    margin-right: 5px;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #f8f9fa;
}
/* Dropdown styling - đảm bảo không bị tràn */
.select2-container--open .select2-dropdown {
    z-index: 9999 !important;
    min-width: 100% !important;
    width: auto !important;
}
.select2-container {
    z-index: 9998 !important;
}
/* Đảm bảo dropdown rộng đủ để hiển thị email - FORCE width */
.select2-dropdown {
    min-width: 500px !important;
    width: 500px !important;
    max-width: 100% !important;
}
.select2-container--default .select2-dropdown {
    min-width: 500px !important;
    width: 500px !important;
}
/* Options trong dropdown - FORCE text wrap và không tràn */
.select2-results__option {
    white-space: normal !important;
    word-wrap: break-word !important;
    word-break: break-word !important;
    overflow-wrap: break-word !important;
    overflow: visible !important;
    padding: 8px 12px !important;
    line-height: 1.5 !important;
    max-width: 100% !important;
    width: 100% !important;
    box-sizing: border-box !important;
    display: block !important;
    text-overflow: clip !important;
}
.select2-results__option * {
    white-space: normal !important;
    word-wrap: break-word !important;
    word-break: break-word !important;
    overflow-wrap: break-word !important;
    display: inline !important;
}
/* Override Select2 default styles */
.select2-container--default .select2-results__option {
    white-space: normal !important;
    word-wrap: break-word !important;
    word-break: break-word !important;
    overflow-wrap: break-word !important;
    overflow: visible !important;
    text-overflow: clip !important;
}
.select2-results__option--highlighted {
    background-color: #0d6efd !important;
    color: white !important;
}
/* Đảm bảo dropdown hiển thị đầy đủ options khi mở */
.select2-results__options {
    max-height: 300px !important;
    overflow-y: auto;
    overflow-x: hidden !important;
    width: 100% !important;
}
.select2-results {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: hidden !important;
}
/* Search box trong dropdown */
.select2-search--dropdown {
    padding: 8px;
    width: 100% !important;
    box-sizing: border-box !important;
}
.select2-search--dropdown .select2-search__field {
    width: 100% !important;
    max-width: 100% !important;
    padding: 6px 12px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    box-sizing: border-box !important;
}
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Chi tiết câu lạc bộ: <span class="text-primary">{{ $club->name }}</span></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.clubs') }}">Quản lý CLB</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.clubs.edit', $club->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Chỉnh sửa
            </a>
            @if($club->status === 'pending')
                <form method="POST" action="{{ route('admin.clubs.status', $club->id) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Duyệt
                    </button>
                </form>
                <button type="button" class="btn btn-danger" 
                        data-bs-toggle="modal" data-bs-target="#rejectClubModal" 
                        data-club-id="{{ $club->id }}" data-club-name="{{ $club->name }}">
                    <i class="fas fa-times me-1"></i> Từ chối
                </button>
            @endif
            @if(in_array($club->status, ['active', 'approved']))
                <form method="POST" action="{{ route('admin.clubs.status', $club->id) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="inactive">
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Bạn có chắc chắn muốn tạm dừng câu lạc bộ này?')">
                        <i class="fas fa-pause me-1"></i> Tạm dừng
                    </button>
                </form>
            @endif
            @if($club->status === 'inactive')
                <form method="POST" action="{{ route('admin.clubs.status', $club->id) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="active">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-play me-1"></i> Kích hoạt lại
                    </button>
                </form>
            @endif
            @if($club->status === 'rejected')
                <form method="POST" action="{{ route('admin.clubs.status', $club->id) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="pending">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-undo me-1"></i> Khôi phục
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.clubs') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>
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
                            @if($club->leader)
                                <p><strong><i class="fas fa-crown me-2 text-warning"></i>Trưởng CLB:</strong> 
                                    <span class="badge bg-warning text-dark">{{ $club->leader->name ?? 'Chưa xác định' }}</span>
                                    @if($club->owner_id && $club->leader_id && $club->owner_id === $club->leader_id)
                                        <small class="text-muted">(Chủ sở hữu)</small>
                                    @endif
                                </p>
                            @endif
                            <p><strong><i class="fas fa-calendar-alt me-2"></i>Ngày tạo:</strong> {{ $club->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5><i class="fas fa-file-alt me-2"></i>Mô tả</h5>
                        <div class="p-3 bg-light rounded">
                            {!! $club->description !!}
                        </div>
                    </div>
                    
                    @if($club->status === 'rejected')
                        <div class="mt-4">
                            <h5><i class="fas fa-times-circle me-2 text-danger"></i>Lý do từ chối</h5>
                            <div class="p-3 bg-danger bg-opacity-10 border border-danger rounded">
                                <p class="mb-0 text-dark">
                                    @if(!empty($club->rejection_reason))
                                        {{ $club->rejection_reason }}
                                    @else
                                        <em class="text-muted">Chưa có lý do từ chối</em>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($pendingJoinRequests->isNotEmpty())
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-clock me-2"></i>Yêu cầu tham gia CLB ({{ $pendingJoinRequests->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Thành viên</th>
                                    <th style="width: 20%;">Ngày gửi yêu cầu</th>
                                    <th style="width: 30%;" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingJoinRequests as $request)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($request->user->avatar && file_exists(public_path($request->user->avatar)))
                                                    <img src="{{ asset($request->user->avatar) }}" 
                                                         alt="{{ $request->user->name }}" 
                                                         class="rounded-circle me-2" 
                                                         width="40" height="40"
                                                         style="object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle me-2 bg-primary text-white d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px; font-size: 16px; font-weight: bold;">
                                                        {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div style="min-width: 0;">
                                                    <strong class="text-truncate d-block">{{ $request->user->name }}</strong>
                                                    <small class="text-muted text-truncate d-block">{{ $request->user->email }}</small>
                                                    @if($request->message)
                                                        <small class="text-muted d-block mt-1">
                                                            <i class="fas fa-comment me-1"></i>{{ Str::limit($request->message, 50) }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <form action="{{ route('admin.join-requests.approve', $request->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Duyệt" onclick="return confirm('Bạn có chắc chắn muốn duyệt yêu cầu này?')">
                                                        <i class="fas fa-check me-1"></i> Duyệt
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('admin.join-requests.reject', $request->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Từ chối" onclick="return confirm('Bạn có chắc chắn muốn từ chối yêu cầu này?')">
                                                        <i class="fas fa-times me-1"></i> Từ chối
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="card mb-4">
                <div class="card-body text-center text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <p class="mb-0">Không có yêu cầu tham gia nào đang chờ duyệt.</p>
                </div>
            </div>
            @endif

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
                            <span class="badge bg-primary rounded-pill">{{ $approvedMembers->total() }}</span>
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
                            Yêu cầu tham gia chờ duyệt
                            <span class="badge bg-warning rounded-pill">{{ $pendingJoinRequests->count() }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Thành viên đã duyệt - Full width -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Thành viên đã duyệt ({{ $approvedMembers->total() }})</h5>
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
                                                $role = $member->position ?? $member->role_in_club ?? 'member';
                                                $badgeColor = 'primary';
                                                $roleLabel = 'Thành viên';
                                                
                                                if($role === 'leader' || $role === 'chunhiem') {
                                                    $badgeColor = 'danger';
                                                    $roleLabel = 'Trưởng CLB';
                                                } elseif($role === 'vice_president') {
                                                    $badgeColor = 'warning';
                                                    $roleLabel = 'Phó CLB';
                                                } elseif($role === 'treasurer') {
                                                    $badgeColor = 'info';
                                                    $roleLabel = 'Thủ quỹ';
                                                } elseif($role === 'member' || $role === 'thanhvien') {
                                                    $badgeColor = 'success';
                                                    $roleLabel = 'Thành viên';
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }}">{{ $roleLabel }}</span>
                                        </td>
                                        <td>{{ $member->created_at->format('d/m/Y') }}</td>
                                        <td style="min-width: 120px; width: 120px;">
                                            <div class="d-flex flex-column gap-1">
                                                <button type="button" class="btn btn-sm btn-warning text-white w-100" title="Thay đổi vai trò"
                                                        data-bs-toggle="modal" data-bs-target="#changeRoleModal"
                                                        data-member-id="{{ $member->id }}"
                                                        data-member-name="{{ $member->user ? $member->user->name : 'Unknown' }}"
                                                        data-current-role="{{ $member->position ?? $member->role_in_club ?? 'member' }}">
                                                    <i class="fas fa-user-shield"></i> Thay đổi vai trò
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger w-100 text-white" title="Xóa thành viên"
                                                        data-bs-toggle="modal" data-bs-target="#removeMemberModal"
                                                        data-member-id="{{ $member->id }}"
                                                        data-member-name="{{ $member->user ? $member->user->name : 'Unknown' }}"
                                                        {{ $member->user_id == $club->owner_id ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash"></i> Xóa
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
                    
                    <!-- Phân trang -->
                    @if($approvedMembers->hasPages())
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div class="text-muted small">
                                    <i class="fas fa-info-circle"></i>
                                    Hiển thị <strong>{{ $approvedMembers->firstItem() }}</strong> - <strong>{{ $approvedMembers->lastItem() }}</strong> 
                                    trong tổng <strong>{{ $approvedMembers->total() }}</strong> kết quả
                                </div>
                                <div>
                                    {{ $approvedMembers->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    @endif
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
      <form method="POST" action="{{ route('admin.clubs.members.add', $club->id) }}" id="addMemberForm">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <label for="user_id" class="form-label mb-0">Chọn người dùng <span class="text-danger">*</span></label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAllUsers" onchange="toggleSelectAll()">
                <label class="form-check-label" for="selectAllUsers">
                  Chọn tất cả
                </label>
              </div>
            </div>
            <select class="form-select" id="user_id" name="user_id[]" multiple required style="width: 100%;">
                @foreach($addableUsers as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            <small class="text-muted">Gõ để tìm kiếm người dùng, có thể chọn nhiều người</small>
            @if($addableUsers->isEmpty())
                <div class="form-text text-muted">Tất cả người dùng đã là thành viên của câu lạc bộ này.</div>
            @endif
          </div>
          <div class="mb-3">
            <label for="position" class="form-label">Vai trò trong CLB <span class="text-danger">*</span></label>
            <select class="form-select" id="position" name="position" required>
                <option value="member" selected>Thành viên</option>
                <option value="treasurer">Thủ quỹ</option>
                <option value="vice_president">Phó CLB</option>
                <option value="leader">Trưởng CLB</option>
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

<!-- Modal Thay đổi vai trò -->
<div class="modal fade" id="changeRoleModal" tabindex="-1" aria-labelledby="changeRoleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="changeRoleModalLabel">Thay đổi vai trò thành viên</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="changeRoleForm" method="POST" action="">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <p>Thành viên: <strong id="memberNameToChangeRole"></strong></p>
          <div class="mb-3">
            <label for="newPosition" class="form-label">Vai trò mới <span class="text-danger">*</span></label>
            <select class="form-select" id="newPosition" name="position" required>
              <option value="member">Thành viên</option>
              <option value="treasurer">Thủ quỹ</option>
              <option value="vice_president">Phó CLB</option>
              <option value="leader">Trưởng CLB</option>
            </select>
            <small class="form-text text-muted">
              <strong>Lưu ý:</strong> Mỗi CLB chỉ có 1 trưởng, 2 phó CLB và 1 thủ quỹ.
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-warning">Xác nhận thay đổi</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
    // Khởi tạo Select2 cho dropdown user
    $('#user_id').select2({
        placeholder: 'Gõ để tìm kiếm người dùng...',
        allowClear: true,
        dropdownAutoWidth: false, // Tắt auto width để dùng CSS custom
        width: '100%', // Full width của container
        minimumResultsForSearch: 0, // Luôn hiển thị search box, ngay cả với ít options
        closeOnSelect: false, // Không đóng khi chọn (để chọn nhiều)
        language: {
            noResults: function() {
                return "Không tìm thấy kết quả";
            },
            searching: function() {
                return "Đang tìm kiếm...";
            },
            inputTooShort: function() {
                return "Gõ để tìm kiếm...";
            }
        }
    });
    
    // Xử lý khi mở dropdown
    $('#user_id').on('select2:open', function() {
        setTimeout(function() {
            // FORCE dropdown width đủ rộng - ít nhất 500px
            var dropdown = $('.select2-dropdown');
            var container = $('.select2-container');
            // Set dropdown width bằng container width hoặc tối thiểu 500px
            var minWidth = Math.max(container.outerWidth(), 500);
            dropdown.css({
                'width': minWidth + 'px',
                'min-width': minWidth + 'px',
                'max-width': '100%'
            });
            
            // Force width cho results
            dropdown.find('.select2-results').css({
                'width': '100%',
                'max-width': '100%',
                'overflow-x': 'hidden'
            });
            
            // Force width cho mỗi option
            dropdown.find('.select2-results__option').css({
                'width': '100%',
                'max-width': '100%',
                'white-space': 'normal',
                'word-wrap': 'break-word',
                'overflow-wrap': 'break-word'
            });
            
            // Focus vào search box
            var searchField = $('.select2-search__field');
            searchField.focus();
            
            // Xóa text trong search để hiển thị tất cả options
            searchField.val('');
            searchField.trigger('input');
        }, 150);
    });
    
    // Đảm bảo dropdown không đóng khi click bên trong
    $(document).on('click', '.select2-container--open .select2-dropdown', function(e) {
        e.stopPropagation();
    });
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

    var changeRoleModal = document.getElementById('changeRoleModal');
    if(changeRoleModal) {
        changeRoleModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var memberId = button.getAttribute('data-member-id');
            var memberName = button.getAttribute('data-member-name');
            var currentRole = button.getAttribute('data-current-role');

            var memberNameElement = changeRoleModal.querySelector('#memberNameToChangeRole');
            var form = changeRoleModal.querySelector('#changeRoleForm');
            var positionSelect = changeRoleModal.querySelector('#newPosition');

            memberNameElement.textContent = memberName;
            form.action = '{{ url("admin/clubs/".$club->id."/members") }}/' + memberId + '/role';
            
            // Set giá trị hiện tại
            if (positionSelect) {
                positionSelect.value = currentRole;
            }
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

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllUsers');
    const userSelect = $('#user_id');
    
    if (selectAllCheckbox.checked) {
        // Chọn tất cả options
        userSelect.find('option').prop('selected', true);
        userSelect.trigger('change');
    } else {
        // Bỏ chọn tất cả
        userSelect.val(null).trigger('change');
    }
}
</script>

<!-- Modal Từ chối Câu lạc bộ -->
<div class="modal fade" id="rejectClubModal" tabindex="-1" aria-labelledby="rejectClubModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rejectClubModalLabel">Lý do từ chối câu lạc bộ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="rejectClubForm" method="POST" action="">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <p>Bạn sắp từ chối câu lạc bộ: <strong id="clubNameToReject"></strong></p>
          <input type="hidden" name="status" value="rejected">
          <div class="mb-3">
            <label for="rejectionReason" class="form-label">Vui lòng nhập lý do từ chối <span class="text-danger">*</span></label>
            <textarea class="form-control" id="rejectionReason" name="rejection_reason" rows="4" required></textarea>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    var rejectClubModal = document.getElementById('rejectClubModal');
    if(rejectClubModal) {
        rejectClubModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var clubId = button.getAttribute('data-club-id');
            var clubName = button.getAttribute('data-club-name');

            var modalTitle = rejectClubModal.querySelector('.modal-title');
            var clubNameElement = rejectClubModal.querySelector('#clubNameToReject');
            var form = rejectClubModal.querySelector('#rejectClubForm');

            clubNameElement.textContent = clubName;
            form.action = '{{ url("admin/clubs") }}/' + clubId + '/status';
        });
    }
});
</script>
@endsection
