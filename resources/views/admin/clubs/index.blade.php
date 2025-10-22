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
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('admin.clubs') }}" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
                <a href="{{ route('admin.clubs.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Tạo CLB mới
                </a>
            </div>
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
                        <th>Thành viên</th>
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
                                @php
                                    $logoPath = $club->logo ? public_path($club->logo) : null;
                                    $hasLogo = $logoPath && file_exists($logoPath);
                                @endphp
                                
                                @if($hasLogo)
                                    <img src="{{ asset($club->logo) }}" 
                                         alt="{{ $club->name }}" 
                                         class="rounded" 
                                         width="40" 
                                         height="40">
                                @else
                                    <div class="club-logo-fallback rounded d-inline-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: bold;">
                                        {{ strtoupper(substr($club->name, 0, 2)) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $club->name }}</strong>
                                <br><small class="text-muted">{{ $club->slug }}</small>
                            </td>
                            <td>{{ $club->field->name ?? 'Không xác định' }}</td>
                            <td>
                                <strong>{{ $club->owner->name ?? 'Không xác định' }}</strong>
                                <br><small class="text-muted">Chủ sở hữu</small>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $club->clubMembers?->count() ?? 0 }}</span>
                                <br><small class="text-muted">{{ $club->clubMembers?->where('role_in_club', 'chunhiem')->count() ?? 0 }} trưởng</small>
                                @if($club->leader) {{-- This now correctly uses the new leader relationship --}}
                                    <br><small class="text-success">
                                        <i class="fas fa-crown"></i> {{ $club->leader->name }}
                                    </small>
                                @elseif($club->clubMembers?->where('role_in_club', 'chunhiem')->count() > 0)
                                    <br><small class="text-warning">
                                        <i class="fas fa-users"></i> 
                                        @foreach($club->clubMembers->where('role_in_club', 'chunhiem') as $leader)
                                            {{ $leader->user->name }}@if(!$loop->last), @endif
                                        @endforeach
                                    </small>
                                @else
                                    <br><small class="text-danger">
                                        <i class="fas fa-exclamation-triangle"></i> Chưa có trưởng
                                    </small>
                                @endif
                            </td>
                            <td>{{ substr($club->description, 0, 50) }}{{ strlen($club->description) > 50 ? '...' : '' }}</td>
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
            <td style="min-width: 160px; width: 160px;">
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('admin.clubs.show', $club->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Chi tiết
                    </a>
                    <a href="{{ route('admin.clubs.members', $club->id) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-users"></i> Thành viên
                    </a>
                    <a href="{{ route('admin.clubs.edit', $club->id) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                                    <form method="POST" action="{{ route('admin.clubs.status', $club->id) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        @if($club->status === 'pending')
                                            <!-- Form Duyệt -->
                                            <form method="POST" action="{{ route('admin.clubs.status', $club->id) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="btn btn-sm btn-success w-100">
                                                    <i class="fas fa-check"></i> Duyệt
                                                </button>
                                            </form>
                                            
                                            <!-- Form Từ chối -->
                                            <form method="POST" action="{{ route('admin.clubs.status', $club->id) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('Bạn có chắc chắn muốn từ chối câu lạc bộ này?')">
                                                    <i class="fas fa-times"></i> Từ chối
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if($club->status === 'approved')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                                <i class="fas fa-play"></i> Kích hoạt
                                            </button>
                                        @endif
                                        
                                        @if(in_array($club->status, ['active', 'approved']))
                                            <input type="hidden" name="status" value="inactive">
                                            <button type="submit" class="btn btn-sm btn-warning w-100" onclick="return confirm('Bạn có chắc chắn muốn tạm dừng câu lạc bộ này?')">
                                                <i class="fas fa-pause"></i> Tạm dừng
                                            </button>
                                        @endif
                                        
                                        @if($club->status === 'inactive')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-sm btn-success w-100">
                                                <i class="fas fa-play"></i> Kích hoạt lại
                                            </button>
                                        @endif
                                        
                                        @if($club->status === 'rejected')
                                            <input type="hidden" name="status" value="pending">
                                            <button type="submit" class="btn btn-sm btn-info w-100">
                                                <i class="fas fa-undo"></i> Khôi phục
                                            </button>
                                        @endif
                                    </form>

                                    <form method="POST" action="{{ route('admin.clubs.delete', $club->id) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger w-100"
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa câu lạc bộ này? Hành động này không thể hoàn tác.')"
                                                {{ $club->status !== 'inactive' ? 'disabled' : '' }}
                                                title="{{ $club->status !== 'inactive' ? 'Chỉ có thể xóa câu lạc bộ đã tạm dừng' : 'Xóa câu lạc bộ' }}">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
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