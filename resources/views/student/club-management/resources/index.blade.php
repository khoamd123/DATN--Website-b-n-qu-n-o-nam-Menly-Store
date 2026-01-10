@extends('layouts.student')

@section('title', 'Quản lý tài nguyên - ' . $club->name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <a href="{{ route('student.club-management.index') }}" class="text-decoration-none mb-2 d-inline-block">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại quản lý CLB
                    </a>
                    <h4 class="mb-0"><i class="fas fa-folder me-2"></i>Quản lý tài nguyên - {{ $club->name }}</h4>
                </div>
                <div>
                    <a href="{{ route('student.club-management.resources.create', ['club' => $clubId]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Tạo tài nguyên
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
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
                            <input type="text" class="form-control form-control-sm" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Tìm theo tiêu đề, mô tả...">
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label small">Trạng thái</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>Tất cả</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Lưu trữ</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                <i class="fas fa-search me-1"></i> Lọc
                            </button>
                            <a href="{{ route('student.club-management.resources', ['club' => $clubId]) }}" class="btn btn-outline-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if($resources->count() === 0)
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-2x text-muted mb-3"></i>
                    <p class="text-muted mb-2">CLB này chưa có tài nguyên nào.</p>
                    <a href="{{ route('student.club-management.resources.create', ['club' => $clubId]) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Tạo tài nguyên
                    </a>
                </div>
            @else
                <div class="row">
                    @foreach($resources as $resource)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                @php
                                    $previewImage = null;
                                    if($resource->images && $resource->images->count() > 0) {
                                        $primaryImage = $resource->images->where('is_primary', true)->first();
                                        $firstImage = $primaryImage ?: $resource->images->first();
                                        $previewImage = $firstImage->thumbnail_url ?? $firstImage->image_url;
                                    } elseif($resource->thumbnail_path) {
                                        $previewImage = asset('storage/' . $resource->thumbnail_path);
                                    }
                                @endphp
                                @if($previewImage)
                                    <img src="{{ $previewImage }}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="{{ $resource->title }}">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-file-alt fa-3x text-muted"></i>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <h5 class="card-title">{{ Str::limit($resource->title, 50) }}</h5>
                                    <p class="card-text text-muted small">{{ Str::limit(strip_tags($resource->description ?? ''), 80) }}</p>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-{{ $resource->status == 'active' ? 'success' : ($resource->status == 'inactive' ? 'warning' : 'secondary') }}">
                                            {{ $resource->status == 'active' ? 'Hoạt động' : ($resource->status == 'inactive' ? 'Không hoạt động' : 'Lưu trữ') }}
                                        </span>
                                        <small class="text-muted">
                                            <i class="fas fa-eye me-1"></i>{{ number_format($resource->view_count ?? 0) }}
                                        </small>
                                    </div>
                                    @if($resource->images && $resource->images->count() > 0)
                                        <small class="text-muted d-block mb-1">
                                            <i class="fas fa-images me-1"></i>{{ $resource->images->count() }} ảnh
                                        </small>
                                    @endif
                                    @if($resource->files && $resource->files->count() > 0)
                                        <small class="text-muted d-block mb-2">
                                            <i class="fas fa-file me-1"></i>{{ $resource->files->count() }} file
                                        </small>
                                    @endif
                                </div>
                                <div class="card-footer bg-white">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('student.club-management.resources.show', ['club' => $clubId, 'resource' => $resource->id]) }}" 
                                           class="btn btn-sm btn-primary flex-fill">
                                            <i class="fas fa-eye me-1"></i> Xem
                                        </a>
                                        @php
                                            $position = $user->getPositionInClub($clubId);
                                            $canEdit = in_array($position, ['leader', 'vice_president', 'officer']) || $resource->user_id === $user->id;
                                        @endphp
                                        @if($canEdit)
                                            <a href="{{ route('student.club-management.resources.edit', ['club' => $clubId, 'resource' => $resource->id]) }}" 
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3">
                    {{ $resources->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection







