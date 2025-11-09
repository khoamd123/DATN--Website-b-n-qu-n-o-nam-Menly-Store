@extends('admin.layouts.app')

@section('title', 'Phân Quyền - CLB Admin')

@section('content')
<div class="content-header">
    <h1>Phân Quyền</h1>
</div>

<!-- Thống kê quyền -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #dc3545;">
                <i class="fas fa-user-shield"></i>
            </div>
<<<<<<< HEAD
            <p class="stats-number">{{ $users->where('is_admin', true)->count() }}</p>
=======
            <p class="stats-number">{{ \App\Models\User::where('is_admin', true)->count() }}</p>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
            <p class="stats-label">Admin</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #28a745;">
                <i class="fas fa-users"></i>
            </div>
<<<<<<< HEAD
            <p class="stats-number">{{ $users->where('is_admin', false)->count() }}</p>
=======
            <p class="stats-number">{{ \App\Models\User::where('is_admin', false)->count() }}</p>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
            <p class="stats-label">User thường</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #007bff;">
                <i class="fas fa-users"></i>
            </div>
<<<<<<< HEAD
            <p class="stats-number">{{ $clubs->count() }}</p>
=======
            <p class="stats-number">{{ \App\Models\Club::count() }}</p>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
            <p class="stats-label">Câu lạc bộ</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #ffc107;">
                <i class="fas fa-key"></i>
            </div>
<<<<<<< HEAD
            <p class="stats-number">{{ $permissions->count() }}</p>
=======
            <p class="stats-number">{{ \App\Models\Permission::count() }}</p>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
            <p class="stats-label">Loại quyền</p>
        </div>
    </div>
</div>

<!-- Danh sách người dùng và phân quyền -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
<<<<<<< HEAD
                        <th>ID</th>
=======
                        <th>STT</th>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                        <th>Người dùng</th>
                        <th>Email</th>
                        <th>Quyền Admin</th>
                        <th>Câu lạc bộ sở hữu</th>
                        <th>Câu lạc bộ tham gia</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
<<<<<<< HEAD
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $user->avatar ?? '/images/avatar/avatar.png' }}" 
                                         alt="{{ $user->name }}" 
                                         class="rounded-circle me-2" 
                                         width="30" 
                                         height="30"
                                         onerror="this.src='/images/avatar/avatar.png'">
=======
                    @forelse($users as $index => $user)
                        <tr>
                            <td>{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($user->avatar && file_exists(public_path($user->avatar)))
                                        <img src="{{ asset($user->avatar) }}" 
                                             alt="{{ $user->name }}" 
                                             class="rounded-circle me-2" 
                                             width="30" 
                                             height="30">
                                    @else
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-2 text-white" 
                                             style="width: 30px; height: 30px; font-size: 12px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        @if($user->is_admin)
                                            <i class="fas fa-crown text-warning ms-1" title="Admin"></i>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->is_admin ? 'danger' : 'success' }}">
                                    {{ $user->is_admin ? 'Admin' : 'User' }}
                                </span>
                            </td>
                            <td>
                                @if($user->ownedClubs->count() > 0)
                                    @foreach($user->ownedClubs as $club)
                                        <span class="badge bg-primary me-1">{{ $club->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">Không có</span>
                                @endif
                            </td>
                            <td>
                                @if($user->clubs->count() > 0)
                                    {{ $user->clubs->count() }} CLB
                                @else
                                    <span class="text-muted">Không có</span>
                                @endif
                            </td>
<<<<<<< HEAD
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
=======
                            <td>{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</td>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editPermissionsModal{{ $user->id }}">
                                        <i class="fas fa-edit"></i> Sửa quyền
                                    </button>
                                </div>
                                
                                <!-- Modal sửa quyền -->
                                <div class="modal fade" id="editPermissionsModal{{ $user->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Sửa quyền: {{ $user->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('admin.permissions.user', $user->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Quyền Admin</label>
                                                        <select name="is_admin" class="form-select">
                                                            <option value="0" {{ !$user->is_admin ? 'selected' : '' }}>User thường</option>
                                                            <option value="1" {{ $user->is_admin ? 'selected' : '' }}>Admin</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Quyền cụ thể</label>
                                                        <div class="row">
                                                            @foreach($permissions as $permission)
                                                                <div class="col-md-6">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" 
                                                                               name="permissions[]" value="{{ $permission->id }}" 
                                                                               id="permission{{ $permission->id }}_{{ $user->id }}">
                                                                        <label class="form-check-label" for="permission{{ $permission->id }}_{{ $user->id }}">
                                                                            {{ $permission->name }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Không có người dùng nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        @if($users->hasPages())
            <div class="d-flex justify-content-center mt-4">
<<<<<<< HEAD
                {{ $users->appends(request()->query())->links() }}
=======
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
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
            </div>
        @endif
    </div>
</div>

<!-- Danh sách quyền hệ thống -->
<div class="card mt-4">
    <div class="card-header">
        <h5>Danh sách quyền hệ thống</h5>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($permissions as $permission)
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">{{ $permission->name }}</h6>
                            <p class="card-text text-muted">{{ $permission->description ?? 'Không có mô tả' }}</p>
                            <small class="text-muted">ID: {{ $permission->id }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
<<<<<<< HEAD
=======

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
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
@endsection
