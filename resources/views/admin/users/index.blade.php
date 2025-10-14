@extends('admin.layouts.app')

@section('title', 'Quản lý người dùng - CLB Admin')

@section('content')
<div class="content-header">
    <h1>Quản lý người dùng</h1>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm theo tên, email, số điện thoại..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="is_admin" class="form-select">
                    <option value="">Tất cả quyền</option>
                    <option value="1" {{ request('is_admin') == '1' ? 'selected' : '' }}>Admin</option>
                    <option value="0" {{ request('is_admin') == '0' ? 'selected' : '' }}>User thường</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-3 text-end">
                <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách người dùng -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh đại diện</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Mã sinh viên</th>
                        <th>Số điện thoại</th>
                        <th>Địa chỉ</th>
                        <th>Role</th>
                        <th>Vai trò CLB</th>
                        <th>Quyền Admin</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                @if($user->avatar && file_exists(public_path($user->avatar)))
                                    <img src="{{ asset($user->avatar) }}" 
                                         alt="{{ $user->name }}" 
                                         class="rounded-circle" 
                                         width="40" 
                                         height="40">
                                @else
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->student_id)
                                    <span class="badge bg-success">{{ $user->student_id }}</span>
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>{{ Str::limit($user->address ?? 'N/A', 30) }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'club_manager' ? 'warning' : 'success') }}">
                                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $clubs = \App\Models\Club::all();
                                    $clubRoles = [];
                                    foreach($clubs as $club) {
                                        $position = $user->getPositionInClub($club->id);
                                        if($position) {
                                            $clubRoles[] = $club->name . ': ' . $position;
                                        }
                                    }
                                @endphp
                                @if(count($clubRoles) > 0)
                                    @foreach($clubRoles as $role)
                                        <span class="badge bg-info me-1">{{ $role }}</span><br>
                                    @endforeach
                                @else
                                    <span class="badge bg-light text-dark">Không có</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $user->is_admin ? 'danger' : 'secondary' }}">
                                    {{ $user->is_admin ? 'Admin' : 'User' }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editRoleModal{{ $user->id }}">
                                    <i class="fas fa-edit"></i> Chỉnh sửa
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                Không tìm thấy người dùng nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        @if($users->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Edit Role Modals -->
@foreach($users as $user)
<div class="modal fade" id="editRoleModal{{ $user->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa quyền - {{ $user->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.users.status', $user->id) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="role{{ $user->id }}" class="form-label">Vai trò hệ thống:</label>
                        <select name="role" id="role{{ $user->id }}" class="form-select" onchange="updateAdminStatus(this, 'adminStatus{{ $user->id }}')">
                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User thường</option>
                            <option value="club_manager" {{ $user->role === 'club_manager' ? 'selected' : '' }}>Quản lý CLB</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adminStatus{{ $user->id }}" class="form-label">Quyền Admin:</label>
                        <select name="is_admin" id="adminStatus{{ $user->id }}" class="form-select">
                            <option value="0" {{ !$user->is_admin ? 'selected' : '' }}>Không</option>
                            <option value="1" {{ $user->is_admin ? 'selected' : '' }}>Có</option>
                        </select>
                    </div>

                    <!-- Club Roles Section -->
                    <div class="mb-3">
                        <label class="form-label">Vai trò trong CLB:</label>
                        <div class="row">
                            @php
                                $clubs = \App\Models\Club::all();
                            @endphp
                            @foreach($clubs as $club)
                                @php
                                    $position = $user->getPositionInClub($club->id);
                                @endphp
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2 fw-bold">{{ $club->name }}:</span>
                                        @if($position)
                                            <span class="badge bg-success">{{ ucfirst($position) }}</span>
                                        @else
                                            <span class="badge bg-light text-dark">Không phải thành viên</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Để thay đổi vai trò trong CLB, vào trang "Phân Quyền Chi Tiết"</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('admin.permissions.detailed') }}" class="btn btn-outline-info me-auto">
                        <i class="fas fa-cogs"></i> Phân quyền chi tiết
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Đảm bảo tất cả functions được định nghĩa khi DOM ready
    window.updateAdminStatus = function(roleSelect, adminSelectId) {
        const adminSelect = document.getElementById(adminSelectId);
        
        if (adminSelect && roleSelect.value === 'admin') {
            adminSelect.value = '1';
            adminSelect.disabled = true;
        } else if (adminSelect) {
            adminSelect.disabled = false;
        }
    };
});
</script>
@endsection
