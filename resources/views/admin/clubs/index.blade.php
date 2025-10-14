@extends('admin.layouts.app')

@section('title', 'Quản lý câu lạc bộ - CLB Admin')

@section('content')
<div class="content-header">
    <h1>Quản lý câu lạc bộ</h1>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.clubs') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm theo tên, mô tả..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-3 text-end">
                <a href="{{ route('admin.clubs') }}" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách câu lạc bộ -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Logo</th>
                        <th>Tên CLB</th>
                        <th>Lĩnh vực</th>
                        <th>Chủ sở hữu</th>
                        <th>Mô tả</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clubs as $club)
                        <tr>
                            <td>{{ $club->id }}</td>
                            <td>
                                <img src="{{ $club->logo ?? '/images/logos/logo_club.png' }}" 
                                     alt="{{ $club->name }}" 
                                     class="rounded" 
                                     width="40" 
                                     height="40"
                                     onerror="this.src='/images/logos/logo_club.png'">
                            </td>
                            <td>
                                <strong>{{ $club->name }}</strong>
                                <br><small class="text-muted">{{ $club->slug }}</small>
                            </td>
                            <td>{{ $club->field->name ?? 'Không xác định' }}</td>
                            <td>{{ $club->owner->name ?? 'Không xác định' }}</td>
                            <td>{{ Str::limit($club->description, 50) }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'rejected' => 'danger',
                                        'active' => 'success',
                                        'inactive' => 'secondary'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'rejected' => 'Từ chối',
                                        'active' => 'Đang hoạt động',
                                        'inactive' => 'Không hoạt động'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$club->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$club->status] ?? ucfirst($club->status) }}
                                </span>
                            </td>
                            <td>{{ $club->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <form method="POST" action="{{ route('admin.clubs.status', $club->id) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        @if($club->status === 'pending')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Duyệt
                                            </button>
                                        @endif
                                        
                                        @if($club->status === 'approved')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-play"></i> Kích hoạt
                                            </button>
                                        @endif
                                        
                                        @if(in_array($club->status, ['active', 'approved']))
                                            <input type="hidden" name="status" value="inactive">
                                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Bạn có chắc chắn muốn tạm dừng câu lạc bộ này?')">
                                                <i class="fas fa-pause"></i> Tạm dừng
                                            </button>
                                        @endif
                                        
                                        @if($club->status === 'pending')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn từ chối câu lạc bộ này?')">
                                                <i class="fas fa-times"></i> Từ chối
                                            </button>
                                        @endif
                                        
                                        @if($club->status === 'inactive')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-play"></i> Kích hoạt lại
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Không tìm thấy câu lạc bộ nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        @if($clubs->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $clubs->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
