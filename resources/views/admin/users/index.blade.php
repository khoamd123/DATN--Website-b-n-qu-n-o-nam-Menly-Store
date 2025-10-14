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
                    @forelse($users as $index => $user)
                        <tr>
                            <td>{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</td>
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
                            <td>{{ substr($user->address ?? 'N/A', 0, 30) }}{{ strlen($user->address ?? 'N/A') > 30 ? '...' : '' }}</td>
                            <td>
                                @php
                                    $roleLabel = $user->is_admin ? 'Admin' : 'User';
                                    $roleColor = $user->is_admin ? 'danger' : 'success';
                                @endphp
                                <span class="badge bg-{{ $roleColor }}">{{ $roleLabel }}</span>
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
                                    {{ $user->is_admin ? 'Có quyền' : 'Không có quyền' }}
                                </span>
                            </td>
                            <td>{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editRoleModal{{ $user->id }}">
                                    <i class="fas fa-edit"></i> Chỉnh sửa
                                </button>
                                <form method="POST" action="{{ route('admin.users.delete', $user->id) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
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
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        @if($users->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">« Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->previousPageUrl() }}">« Previous</a>
                            </li>
                        @endif
                        
                        <li class="page-item active">
                            <span class="page-link">{{ $users->currentPage() }}</span>
                        </li>
                        
                        @if($users->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->nextPageUrl() }}">Next »</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">Next »</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
            <div class="text-center text-muted mt-2">
                Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
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

<style>
/* Pagination styling */
.pagination {
    justify-content: center;
    margin: 0;
}

.pagination .page-link {
    padding: 0.5rem 0.75rem;
    margin: 0 0.25rem;
    border: 1px solid #dee2e6;
    color: #007bff;
    text-decoration: none;
    border-radius: 0.375rem;
    background-color: white;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
}

.pagination .page-link:hover {
    color: #0056b3;
    background-color: #e9ecef;
    border-color: #dee2e6;
}
</style>
@endsection
