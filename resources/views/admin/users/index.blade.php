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
                        <th>Số điện thoại</th>
                        <th>Địa chỉ</th>
                        <th>Quyền</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <img src="{{ $user->avatar ?? '/images/avatar/avatar.png' }}" 
                                     alt="{{ $user->name }}" 
                                     class="rounded-circle" 
                                     width="40" 
                                     height="40"
                                     onerror="this.src='/images/avatar/avatar.png'">
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>{{ Str::limit($user->address ?? 'N/A', 30) }}</td>
                            <td>
                                <span class="badge bg-{{ $user->is_admin ? 'danger' : 'success' }}">
                                    {{ $user->is_admin ? 'Admin' : 'User' }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_admin" value="{{ $user->is_admin ? 0 : 1 }}">
                                    <button type="submit" 
                                            class="btn btn-sm btn-{{ $user->is_admin ? 'warning' : 'primary' }}"
                                            onclick="return confirm('Bạn có chắc chắn muốn thay đổi quyền của người dùng này?')">
                                        <i class="fas fa-{{ $user->is_admin ? 'user-minus' : 'user-plus' }}"></i>
                                        {{ $user->is_admin ? 'Bỏ quyền Admin' : 'Cấp quyền Admin' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
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
@endsection
