@extends('admin.layouts.app-simple')

@section('title', 'Phân Quyền - CLB Admin')

@section('content')
<div class="content-header">
    <h1>Phân Quyền</h1>
    <p class="text-muted">Quản lý quyền hạn người dùng trong hệ thống</p>
</div>

<!-- Thống kê quyền -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ \App\Models\User::where('is_admin', true)->count() }}</h4>
                        <p class="mb-0">Admin</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-shield fa-2x"></i>
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
                        <h4>{{ \App\Models\User::where('is_admin', false)->count() }}</h4>
                        <p class="mb-0">User thường</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ \App\Models\Club::count() }}</h4>
                        <p class="mb-0">Câu lạc bộ</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ \App\Models\Permission::count() }}</h4>
                        <p class="mb-0">Loại quyền</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-key fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Danh sách người dùng và phân quyền -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Danh sách người dùng và quyền hạn</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="overflow-x: visible !important; overflow: visible !important;">
            <table class="table table-hover table-striped" style="width: 100% !important; table-layout: auto !important;">
                <thead class="table-dark">
                    <tr>
                        <th>STT</th>
                        <th>Người dùng</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Quyền Admin</th>
                        <th>Câu lạc bộ tham gia</th>
                        <th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr>
                            <td><strong>{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-2 text-white" 
                                         style="width: 30px; height: 30px; font-size: 12px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
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
                                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'club_manager' ? 'warning' : ($user->role === 'executive_board' ? 'info' : 'success')) }}">
                                    {{ $user->role === 'executive_board' ? 'Executive' : ucfirst(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </td>
                            <td>
                                @if($user->is_admin)
                                    <span class="badge bg-danger">Admin</span>
                                @else
                                    <span class="badge bg-success">User</span>
                                @endif
                            </td>
                            <td>
                                @if($user->clubs && $user->clubs->count() > 0)
                                    @foreach($user->clubs->take(2) as $club)
                                        <span class="badge bg-info me-1">{{ $club->name }}</span>
                                    @endforeach
                                    @if($user->clubs->count() > 2)
                                        <span class="text-muted">+{{ $user->clubs->count() - 2 }} khác</span>
                                    @endif
                                @else
                                    <span class="text-muted">Không có</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-3 d-block"></i>
                                Không có người dùng nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Danh sách quyền hệ thống -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Danh sách quyền hệ thống</h5>
    </div>
    <div class="card-body">
        @if($permissions->count() > 0)
            <div class="row">
                @foreach($permissions as $permission)
                    <div class="col-md-4 mb-3">
                        <div class="card border-left-primary">
                            <div class="card-body">
                                <h6 class="card-title text-primary">{{ $permission->name }}</h6>
                                <p class="card-text text-muted">{{ $permission->description ?? 'Không có mô tả' }}</p>
                                <small class="text-muted">ID: {{ $permission->id }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-muted py-4">
                <i class="fas fa-key fa-2x mb-3 d-block"></i>
                <p>Không có quyền hệ thống nào được định nghĩa</p>
            </div>
        @endif
    </div>
</div>

<style>
/* CSS để tránh nháy màn hình */
.card {
    border: 1px solid #e9ecef;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.table {
    font-size: 14px;
}

.table th {
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
}

.table tr:hover {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.btn {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}

.form-control, .form-select {
    font-size: 0.875rem;
}

/* Loại bỏ transitions có thể gây nháy */
* {
    transition: none !important;
    animation: none !important;
}

/* Không cho scroll ngang */
div.table-responsive {
    overflow-x: visible !important;
    overflow: visible !important;
    -ms-overflow-x: visible !important;
}

table.table {
    width: 100% !important;
    table-layout: auto !important;
    max-width: 100% !important;
}

/* Avatar tĩnh */
.user-avatar-fixed {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
</style>
@endsection
