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
                        <th>Vị trí trong CLB</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        @php
                            // Chỉ lấy clubMembers có status = 'approved'
                            $userClubs = $user->clubMembers()->where('status', 'approved')->with('club')->get();
                        @endphp
                        
                        @if($userClubs->count() > 0)
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
                                    @if($user->isAdmin())
                                        <span class="badge bg-danger">Admin Hệ Thống</span>
                                        <br><small class="text-muted">Quyền: Tất cả CLB</small>
                                    @else
                                        @foreach($userClubs as $clubMember)
                                            @php
                                                $club = $clubMember->club;
                                                $position = $clubMember->position ?? $clubMember->role_in_club;
                                            @endphp
                                            <div class="mb-2">
                                                <strong>{{ $club->name }}:</strong> 
                                                @switch($position)
                                                    @case('leader')
                                                    @case('chunhiem')
                                                        <span class="badge bg-danger">Trưởng CLB</span>
                                                        @break
                                                    @case('vice_president')
                                                    @case('phonhiem')
                                                        <span class="badge bg-warning">Phó CLB</span>
                                                        @break
                                                    @case('officer')
                                                        <span class="badge bg-info">Cán sự</span>
                                                        @break
                                                    @case('member')
                                                    @case('thanhvien')
                                                        <span class="badge bg-success">Thành viên</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $position }}</span>
                                                @endswitch
                                            </div>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    @if(!$user->isAdmin())
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="editPermissions({{ $user->id }}, '{{ $user->name }}', {{ $userClubs->pluck('club.id')->toJson() }})">
                                            <i class="fas fa-edit"></i> Sửa quyền
                                        </button>
                                    @else
                                        <span class="text-muted">Admin - Không thể sửa</span>
                                    @endif
                                </td>
                            </tr>
                        @else
                            {{-- Hiển thị user chưa tham gia CLB nào --}}
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
                                    <span class="text-muted">Chưa tham gia CLB nào</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">Không có</span>
                                </td>
                                <td>
                                    <span class="text-muted">Không có quyền</span>
                                </td>
                                <td>
                                    <span class="text-muted">Chọn CLB để thêm</span>
                                </td>
                            </tr>
                        @endif
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
                    <strong>Người dùng:</strong> <span id="modalUserName"></span>
                </div>
                <div class="mb-3">
                    <label for="clubSelect" class="form-label">Chọn CLB:</label>
                    <select class="form-select" id="clubSelect">
                        <option value="">Chọn CLB</option>
                    </select>
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

function editPermissions(userId, userName, clubIds) {
    currentUserId = userId;
    
    document.getElementById('modalUserName').textContent = userName;
    
    // Tạo dropdown chọn CLB
    const clubSelect = document.getElementById('clubSelect');
    clubSelect.innerHTML = '<option value="">Chọn CLB</option>';
    
    // Lấy danh sách CLB từ user
    clubIds.forEach(clubId => {
        // Tìm CLB trong danh sách $clubs
        const club = {{ $clubs->pluck('name', 'id')->toJson() }};
        if (club[clubId]) {
            const option = document.createElement('option');
            option.value = clubId;
            option.textContent = club[clubId];
            clubSelect.appendChild(option);
        }
    });
    
    // Reset club ID và load permissions khi chọn CLB
    currentClubId = null;
    clubSelect.onchange = function() {
        currentClubId = this.value;
        if (currentClubId) {
            loadPermissions(userId, currentClubId);
        }
    };
    
    // Reset checkboxes
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editPermissionsModal'));
    modal.show();
}

function loadPermissions(userId, clubId) {
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
}

function savePermissions() {
    // Kiểm tra đã chọn CLB chưa
    if (!currentClubId) {
        alert('Vui lòng chọn CLB trước khi lưu quyền!');
        return;
    }
    
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

<script>
function addToClub(userId, clubId, userName, clubName) {
    if (confirm(`Bạn có chắc chắn muốn thêm "${userName}" vào CLB "${clubName}"?`)) {
        // Gửi request thêm user vào CLB
        fetch('/admin/permissions-detailed/add-to-club', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                user_id: userId,
                club_id: clubId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Thêm thành viên thành công!');
                location.reload(); // Reload trang để cập nhật
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra!');
        });
    }
}
</script>
@endsection
