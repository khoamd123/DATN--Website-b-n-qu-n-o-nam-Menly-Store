@extends('admin.layouts.app')

@section('title', 'Quản Lý Phân Quyền Chi Tiết - CLB Admin')

@section('content')
<div class="content-header">
    <h1>🔐 Quản Lý Phân Quyền Chi Tiết</h1>
    <p class="text-muted">Quản lý quyền hạn chi tiết cho từng người dùng trong từng CLB</p>
</div>

<!-- Thống kê tổng quan -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $users->count() }}</h4>
                        <p class="mb-0">Tổng người dùng</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $clubs->count() }}</h4>
                        <p class="mb-0">Câu lạc bộ</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-building fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $permissions->count() }}</h4>
                        <p class="mb-0">Loại quyền</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-key fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $users->where('is_admin', true)->count() }}</h4>
                        <p class="mb-0">Admin</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-shield fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Danh sách quyền có sẵn -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📋 Danh sách quyền có sẵn</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($permissions as $permission)
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                @switch($permission->name)
                                    @case('manage_club')
                                        <i class="fas fa-cogs text-primary fa-lg"></i>
                                        @break
                                    @case('manage_members')
                                        <i class="fas fa-users-cog text-success fa-lg"></i>
                                        @break
                                    @case('create_event')
                                        <i class="fas fa-calendar-plus text-warning fa-lg"></i>
                                        @break
                                    @case('post_announcement')
                                        <i class="fas fa-bullhorn text-info fa-lg"></i>
                                        @break
                                    @case('evaluate_member')
                                        <i class="fas fa-star text-warning fa-lg"></i>
                                        @break
                                    @case('manage_department')
                                        <i class="fas fa-sitemap text-secondary fa-lg"></i>
                                        @break
                                    @case('manage_documents')
                                        <i class="fas fa-file-alt text-dark fa-lg"></i>
                                        @break
                                    @case('view_reports')
                                        <i class="fas fa-chart-bar text-success fa-lg"></i>
                                        @break
                                    @default
                                        <i class="fas fa-key text-primary fa-lg"></i>
                                @endswitch
                            </div>
                            <div>
                                <strong>{{ $permission->name }}</strong>
                                <br><small class="text-muted">{{ $permission->description }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bảng quản lý permissions -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">👥 Phân Quyền Chi Tiết</h5>
        <button class="btn btn-primary" onclick="refreshPermissions()">
            <i class="fas fa-sync-alt"></i> Làm mới
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Người dùng</th>
                        <th>CLB</th>
                        <th>Vị trí</th>
                        <th>Quyền hiện tại</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        @foreach($clubs as $club)
                            @php
                                $position = $user->getPositionInClub($club->id);
                                $userPermissions = $user->getClubPermissions($club->id);
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar-fixed me-2">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <strong>{{ $user->name }}</strong>
                                            <br><small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $club->name }}</strong>
                                    <br><small class="text-muted">{{ $club->description }}</small>
                                </td>
                                <td>
                                    @if($position)
                                        @switch($position)
                                            @case('leader')
                                                <span class="badge bg-danger">Trưởng CLB</span>
                                                @break
                                            @case('vice_president')
                                                <span class="badge bg-warning">Phó CLB</span>
                                                @break
                                            @case('officer')
                                                <span class="badge bg-info">Cán sự</span>
                                                @break
                                            @case('member')
                                                <span class="badge bg-success">Thành viên</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $position }}</span>
                                        @endswitch
                                    @else
                                        <span class="badge bg-light text-dark">Không phải thành viên</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->isAdmin())
                                        <span class="badge bg-danger">Admin - Tất cả quyền</span>
                                    @elseif($userPermissions)
                                        @foreach($userPermissions as $perm)
                                            <span class="badge bg-primary me-1">{{ $perm }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Không có quyền</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$user->isAdmin())
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="editPermissions({{ $user->id }}, {{ $club->id }}, '{{ $user->name }}', '{{ $club->name }}')">
                                            <i class="fas fa-edit"></i> Sửa quyền
                                        </button>
                                    @else
                                        <span class="text-muted">Admin - Không thể sửa</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal chỉnh sửa quyền -->
<div class="modal fade" id="editPermissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa quyền</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Người dùng:</strong> <span id="modalUserName"></span><br>
                    <strong>CLB:</strong> <span id="modalClubName"></span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Chọn quyền:</label>
                    <div class="row" id="permissionsList">
                        @foreach($permissions as $permission)
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input permission-checkbox" 
                                       type="checkbox" 
                                       value="{{ $permission->id }}" 
                                       id="perm_{{ $permission->id }}"
                                       name="permissions[]">
                                <label class="form-check-label" for="perm_{{ $permission->id }}">
                                    <strong>{{ $permission->name }}</strong><br>
                                    <small class="text-muted">{{ $permission->description }}</small>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="savePermissions()">Lưu quyền</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentUserId = null;
let currentClubId = null;

function editPermissions(userId, clubId, userName, clubName) {
    currentUserId = userId;
    currentClubId = clubId;
    
    document.getElementById('modalUserName').textContent = userName;
    document.getElementById('modalClubName').textContent = clubName;
    
    // Debug: Log available permissions
    console.log('Available permissions:', document.querySelectorAll('.permission-checkbox').length);
    
    // Reset checkboxes
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    
    // Load current permissions
    fetch(`{{ url('/admin/permissions/user-permissions') }}?user_id=${userId}&club_id=${clubId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.permissions) {
                // Check permissions based on current position
                data.permissions.forEach(perm => {
                    const permissionId = getPermissionIdByName(perm);
                    if (permissionId) {
                        const checkbox = document.querySelector(`input[value="${permissionId}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error loading permissions:', error);
        });
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editPermissionsModal'));
    modal.show();
}

function savePermissions() {
    const selectedPermissions = Array.from(document.querySelectorAll('.permission-checkbox:checked'))
        .map(cb => cb.value);
    
    fetch('{{ url("/admin/permissions/update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            user_id: currentUserId,
            club_id: currentClubId,
            permissions: selectedPermissions
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đã cập nhật quyền thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        alert('Lỗi: ' + error.message);
    });
}

function refreshPermissions() {
    location.reload();
}

function getPermissionIdByName(permissionName) {
    const permissionMap = {
        @foreach($permissions as $permission)
        '{{ $permission->name }}': {{ $permission->id }},
        @endforeach
    };
    return permissionMap[permissionName] || null;
}

function getPermissionNameById(permissionId) {
    const permissionMap = {
        @foreach($permissions as $permission)
        {{ $permission->id }}: '{{ $permission->name }}',
        @endforeach
    };
    return permissionMap[permissionId] || null;
}
</script>

<style>
.user-avatar-fixed {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
</style>
@endsection
