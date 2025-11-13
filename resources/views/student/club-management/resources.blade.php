@extends('layouts.student')

@section('title', 'Quản lý tài nguyên CLB')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-folder-open text-teal me-2"></i> Quản lý tài nguyên CLB
                    </h4>
                    <p class="text-muted mb-0">{{ $club->name ?? 'CLB' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('student.club-management.resources.create', ['club' => $clubId]) }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Tạo tài nguyên
                    </a>
                    <a href="{{ route('student.club-management.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <div>
                                    <div class="stat-number">{{ $totalResources }}</div>
                                    <div class="stat-label">Tài nguyên</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3">
                                    <i class="fas fa-file"></i>
                                </div>
                                <div>
                                    <div class="stat-number">{{ $totalFiles }}</div>
                                    <div class="stat-label">File</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resources List -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tiêu đề</th>
                            <th>Mô tả</th>
                            <th>Người tạo</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resources as $resource)
                            <tr>
                                <td>
                                    <strong>{{ $resource->title }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($resource->description ?? ''), 50) }}
                                    </small>
                                </td>
                                <td>
                                    {{ $resource->user->name ?? 'Hệ thống' }}
                                </td>
                                <td>
                                    <small>{{ $resource->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('student.club-management.resources.show', ['club' => $clubId, 'resource' => $resource->id]) }}" 
                                           class="btn btn-sm btn-primary action-btn" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @php
                                            $position = $user->getPositionInClub($clubId);
                                            $hasEditPermission = ($resource->user_id == $user->id) 
                                                || in_array($position, ['leader', 'vice_president', 'officer'])
                                                || $user->hasPermission('quan_ly_clb', $clubId)
                                                || $user->hasPermission('dang_thong_bao', $clubId);
                                        @endphp
                                        @if($hasEditPermission)
                                            <a href="{{ route('student.club-management.resources.edit', ['club' => $clubId, 'resource' => $resource->id]) }}" 
                                               class="btn btn-sm btn-warning action-btn" 
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('student.club-management.resources.destroy', ['club' => $clubId, 'resource' => $resource->id]) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài nguyên này?');"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger action-btn" 
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top" 
                                                        title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Chưa có tài nguyên nào.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($resources->hasPages())
                <div class="mt-3">
                    {{ $resources->links() }}
                </div>
            @endif

            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Lưu ý:</strong> Để tạo và quản lý tài nguyên chi tiết, vui lòng truy cập trang quản trị.
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #f0fdfa;
        color: #14b8a6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
        font-weight: bold;
        color: #14b8a6;
    }
    
    .stat-label {
        color: #666;
        font-size: 0.9rem;
    }
    
    .text-teal {
        color: #14b8a6 !important;
    }
    
    .action-btn {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .action-btn.btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    .action-btn.btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
    
    .action-btn.btn-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #000;
    }
    
    .action-btn.btn-warning:hover {
        background-color: #ffca2c;
        border-color: #ffc720;
        color: #000;
    }
    
    .action-btn.btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    .action-btn.btn-danger:hover {
        background-color: #bb2d3b;
        border-color: #b02a37;
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
@endsection


