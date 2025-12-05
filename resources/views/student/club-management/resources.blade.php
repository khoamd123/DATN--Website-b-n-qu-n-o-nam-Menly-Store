@extends('layouts.student')

@section('title', 'Quản lý tài nguyên - ' . $club->name)

@push('styles')
<style>
    .action-btn {
        transition: all 0.2s ease;
    }
    
    .action-btn:hover {
        opacity: 0.9;
    }
    
    .btn-outline-primary:hover {
        background-color: #0d9488;
        border-color: #0d9488;
        color: white;
    }
    
    .btn-outline-danger:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }
    
    .btn-outline-warning:hover {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #000;
    }
    
    .btn-primary, .btn-outline-secondary, .btn-warning {
        border-radius: 6px;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <a href="{{ route('student.club-management.index') }}" class="text-decoration-none mb-2 d-inline-block">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại quản lý CLB
                    </a>
                    <h4 class="mb-0"><i class="fas fa-folder-open me-2"></i>Quản lý tài nguyên - {{ $club->name }}</h4>
                </div>
                <div class="d-flex gap-2">
                    @php
                        $position = $user->getPositionInClub($clubId);
                        $canManageResources = in_array($position, ['leader', 'vice_president']);
                    @endphp
                    @if($canManageResources)
                    <a href="{{ route('student.club-management.resources.create', ['club' => $clubId]) }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                        <i class="fas fa-plus me-1"></i> Tạo tài nguyên
                    </a>
                    @endif
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Bộ lọc -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Bộ lọc</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('student.club-management.resources', ['club' => $clubId]) }}" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label small">Tìm kiếm</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="Tìm theo tiêu đề, mô tả...">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label small">Trạng thái</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>Tất cả</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Lưu trữ</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm me-2">
                                <i class="fas fa-search me-1"></i> Tìm kiếm
                            </button>
                            <a href="{{ route('student.club-management.resources', ['club' => $clubId]) }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-redo me-1"></i> Làm mới
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danh sách tài nguyên -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">STT</th>
                            <th style="width: 30%;">Tiêu đề</th>
                            <th style="width: 20%;">Người tạo</th>
                            <th style="width: 10%;">Trạng thái</th>
                            <th style="width: 10%;">Lượt xem</th>
                            <th style="width: 15%;">Ngày tạo</th>
                            <th style="width: 10%;" class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resources as $resource)
                            <tr>
                                <td>{{ $loop->iteration + ($resources->currentPage() - 1) * $resources->perPage() }}</td>
                                <td>
                                    <div class="fw-bold">{{ $resource->title }}</div>
                                    @if($resource->description)
                                        <small class="text-muted">{{ Str::limit(strip_tags($resource->description), 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($resource->user)
                                        <div>{{ $resource->user->name ?? 'N/A' }}</div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusLabels = [
                                            'active' => ['label' => 'Hoạt động', 'class' => 'success'],
                                            'inactive' => ['label' => 'Không hoạt động', 'class' => 'secondary'],
                                            'archived' => ['label' => 'Lưu trữ', 'class' => 'warning'],
                                        ];
                                        $status = $statusLabels[$resource->status] ?? ['label' => $resource->status, 'class' => 'secondary'];
                                    @endphp
                                    <span class="badge bg-{{ $status['class'] }}">{{ $status['label'] }}</span>
                                </td>
                                <td>
                                    <i class="fas fa-eye me-1"></i>{{ $resource->views_count ?? 0 }}
                                </td>
                                <td>
                                    <small class="text-muted">{{ $resource->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td class="text-center">
                                    @php
                                        $position = $user->getPositionInClub($clubId);
                                        $canManageResources = in_array($position, ['leader', 'vice_president']);
                                    @endphp
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('student.club-management.resources.show', ['club' => $clubId, 'resource' => $resource->id]) }}" 
                                           class="btn btn-outline-primary action-btn" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($canManageResources)
                                        <a href="{{ route('student.club-management.resources.edit', ['club' => $clubId, 'resource' => $resource->id]) }}" 
                                           class="btn btn-outline-warning action-btn" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                                        <p class="mb-0">Chưa có tài nguyên nào.</p>
                                        @php
                                            $position = $user->getPositionInClub($clubId);
                                            $canManageResources = in_array($position, ['leader', 'vice_president']);
                                        @endphp
                                        @if($canManageResources)
                                        <a href="{{ route('student.club-management.resources.create', ['club' => $clubId]) }}" class="btn btn-primary btn-sm mt-2">
                                            <i class="fas fa-plus me-1"></i> Tạo tài nguyên đầu tiên
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($resources->hasPages())
                <div class="mt-4">
                    {{ $resources->links('vendor.pagination.bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection



