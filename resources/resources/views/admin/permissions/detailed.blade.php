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

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.permissions.detailed') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Tìm kiếm</label>
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm theo tên, email..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Vai trò</label>
                <select name="role" class="form-select">
                    <option value="">Tất cả vai trò</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User thường</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Câu lạc bộ</label>
                <select name="club_id" class="form-select">
                    <option value="">Tất cả CLB</option>
                    @foreach($clubs as $club)
                        <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                            {{ $club->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100" title="Tìm kiếm">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('admin.permissions.detailed') }}" class="btn btn-secondary w-100" title="Làm mới">
                    <i class="fas fa-refresh"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách users với CLB -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">👥 Phân Quyền Chi Tiết</h5>
        <button class="btn btn-primary" onclick="refreshPermissions()">
            <i class="fas fa-sync-alt"></i> Làm mới
        </button>
    </div>
    <div class="card-body">
        @foreach($users as $user)
            @php
                // Load lại từ database để lấy dữ liệu mới nhất (tránh cache)
                // Lấy tất cả rồi group unique club_id trong PHP
                $userClubsRaw = \App\Models\ClubMember::where('user_id', $user->id)
                    ->whereIn('status', ['approved', 'active'])
                    ->with('club')
                    ->get();
                
                // Group unique by club_id trong PHP
                $userClubs = $userClubsRaw->unique('club_id')->values();
            @endphp
            
            @if($userClubs->count() > 0 || !$user->isAdmin())
                <div class="card mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                @if($user->avatar && file_exists(public_path($user->avatar)))
                                    <img src="{{ asset($user->avatar) }}" 
                                         alt="{{ $user->name }}" 
                                         class="rounded-circle" 
                                         style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"
                                         onerror="this.onerror=null; this.src=''; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="user-avatar-fixed" style="display: none;">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @else
                                    <div class="user-avatar-fixed">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <strong>{{ $user->name }}</strong>
                                <br><small class="text-muted">{{ $user->email }}</small>
                            </div>
                        </div>
                        @if(!$user->isAdmin())
                            @if($userClubs->count() > 0)
                                <button class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#user{{ $user->id }}Clubs">
                                    <i class="fas fa-chevron-down"></i> Xem chi tiết
                                </button>
                            @else
                                <span class="text-muted">Chưa tham gia CLB</span>
                            @endif
                        @else
                            <span class="badge bg-danger">Admin Hệ Thống</span>
                        @endif
                    </div>
                    
                    @if($userClubs->count() > 0)
                        <div id="user{{ $user->id }}Clubs" class="collapse show">
                            <div class="card-body">
                                @foreach($userClubs as $clubMember)
                                    @php
                                        $club = $clubMember->club;
                                    @endphp
                                    
                                    @if($club)
                                        @php
                                            // Lấy position từ clubMember (đã được đồng bộ ở controller)
                                            $position = $clubMember->position ?? 'member';
                                            
                                            // Lấy quyền của user trong CLB này
                                            $clubPermissions = \DB::table('user_permissions_club')
                                                ->where('user_id', $user->id)
                                                ->where('club_id', $club->id)
                                                ->join('permissions', 'user_permissions_club.permission_id', '=', 'permissions.id')
                                                ->select('permissions.name')
                                                ->pluck('name')
                                                ->toArray();
                                        @endphp
                                        <div class="border rounded p-3 mb-2 d-flex justify-content-between align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h6 class="mb-0 me-3">{{ $club->name }}</h6>
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
                                            <div>
                                                <small class="text-muted">Quyền: </small>
                                                @if(count($clubPermissions) > 0)
                                                    @foreach($clubPermissions as $permName)
                                                        <span class="badge bg-info mb-1">{{ $permName }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">Chưa có quyền</span>
                                                @endif
                                            </div>
                                        </div>
                                        <button class="btn btn-sm btn-primary ms-2" 
                                                onclick="editPermissionsForClub({{ $user->id }}, {{ $club->id }}, '{{ $user->name }}', '{{ $club->name }}')">
                                            <i class="fas fa-edit"></i> Sửa quyền
                                        </button>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        @endforeach
        
        <!-- Phân trang -->
        @if($users->hasPages())
            <div class="d-flex justify-content-center mt-4 custom-pagination">
                {{ $users->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
            </div>
            <div class="text-center text-muted mt-2">
                Hiển thị {{ $users->firstItem() }} đến {{ $users->lastItem() }} trong tổng số {{ $users->total() }} kết quả
            </div>
        @endif
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
                    <label for="clubSelect" class="form-label">Câu lạc bộ:</label>
                    <select class="form-select" id="clubSelect">
                        <option value="">Chọn CLB</option>
                    </select>
                    <small class="text-muted d-none" id="clubSelectNote">Không thể chọn CLB khác vì thành viên chỉ thuộc 1 CLB này</small>
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
                <div class="alert alert-info mt-3">
                    <strong>Ghi chú:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Có <strong>5 quyền trở lên</strong> → Tự động thành <strong>Trưởng CLB</strong></li>
                        <li>Có <strong>2-4 quyền</strong> → Tự động thành <strong>Cán sự</strong></li>
                        <li>Chỉ có <strong>xem_bao_cao</strong> → Tự động thành <strong>Thành viên</strong></li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="savePermissions()">Lưu quyền</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let currentUserId = null;
let currentClubId = null;

function editPermissionsForClub(userId, clubId, userName, clubName) {
    editPermissions(userId, userName, [clubId]);
}

function editPermissions(userId, userName, clubIds) {
    console.log('editPermissions called', { userId, userName, clubIds });
    
    currentUserId = userId;
    
    document.getElementById('modalUserName').textContent = userName;
    
    // Tạo dropdown chọn CLB
    const clubSelect = document.getElementById('clubSelect');
    const clubSelectNote = document.getElementById('clubSelectNote');
    
    // Lấy tất cả CLB từ $clubs
    const allClubs = @json($clubs->pluck('name', 'id'));
    console.log('All clubs:', allClubs);
    
    // Nếu user đã có CLB
    if (clubIds.length > 0) {
        // Nếu chỉ có 1 CLB, chỉ hiển thị CLB đó và disable dropdown
        if (clubIds.length === 1) {
            clubSelect.innerHTML = '';
            const option = document.createElement('option');
            option.value = clubIds[0];
            option.textContent = allClubs[clubIds[0]];
            option.selected = true;
            clubSelect.appendChild(option);
            clubSelect.disabled = true;
            clubSelectNote.classList.remove('d-none');
            
            currentClubId = clubIds[0];
            loadPermissions(userId, clubIds[0]);
        } else {
            // Nếu có nhiều CLB, cho phép chọn
            clubSelect.innerHTML = '<option value="">Chọn CLB</option>';
            clubSelect.disabled = false;
            clubSelectNote.classList.add('d-none');
            
            Object.keys(allClubs).forEach(clubId => {
                const option = document.createElement('option');
                option.value = clubId;
                option.textContent = allClubs[clubId];
                if (clubIds.includes(parseInt(clubId))) {
                    option.selected = true;
                }
                clubSelect.appendChild(option);
            });
            
            // Chọn CLB đầu tiên
            clubSelect.value = clubIds[0];
            currentClubId = clubIds[0];
            loadPermissions(userId, clubIds[0]);
            
            // Load permissions khi chọn CLB khác
            clubSelect.onchange = function() {
                currentClubId = this.value;
                if (currentClubId) {
                    loadPermissions(userId, currentClubId);
                }
            };
        }
    } else {
        // Nếu chưa có CLB, hiển thị tất cả để chọn
        clubSelect.innerHTML = '<option value="">Chọn CLB</option>';
        clubSelect.disabled = false;
        clubSelectNote.classList.add('d-none');
        
        Object.keys(allClubs).forEach(clubId => {
            const option = document.createElement('option');
            option.value = clubId;
            option.textContent = allClubs[clubId];
            clubSelect.appendChild(option);
        });
        
        currentClubId = null;
        
        // Load permissions khi chọn CLB
        clubSelect.onchange = function() {
            currentClubId = this.value;
            if (currentClubId) {
                loadPermissions(userId, currentClubId);
            }
        };
    }
    
    // Reset checkboxes
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    
    // Show modal
    const modalElement = document.getElementById('editPermissionsModal');
    console.log('Modal element:', modalElement);
    
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    } else {
        console.error('Modal element not found!');
    }
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
    
    // Hiển thị loading
    const saveBtn = event.target;
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
    
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
        console.log('Response data:', data);
        if (data.success) {
            alert(data.message || 'Đã cập nhật quyền thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể cập nhật quyền.'));
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi: ' + error.message);
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
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

@section('styles')
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

/* Ẩn mũi tên phân trang */
.custom-pagination svg,
.custom-pagination .pagination svg,
svg.w-5,
svg.h-5 {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
    visibility: hidden !important;
}

.custom-pagination .pagination .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

/* Thay thế mũi tên bằng text */
.custom-pagination .pagination .page-link[rel="prev"]:before {
    content: "‹ ";
}

.custom-pagination .pagination .page-link[rel="next"]:before {
    content: " ›";
}
</style>
@endsection
