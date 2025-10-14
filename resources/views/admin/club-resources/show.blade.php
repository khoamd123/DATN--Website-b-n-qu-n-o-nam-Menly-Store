@extends('admin.layouts.app')

@section('title', 'Chi tiết Tài nguyên - CLB Admin')

@section('content')
<div class="content-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-1">📦 Chi tiết Tài nguyên</h1>
        <p class="text-muted mb-0">Xem thông tin chi tiết về tài nguyên</p>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.club-resources.index') }}">Tài nguyên CLB</a></li>
            <li class="breadcrumb-item active">Chi tiết</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h2 class="card-title mb-2">{{ $resource->title }}</h2>
                        <p class="text-muted mb-2">
                            <span class="badge bg-info rounded-pill me-2">
                                {{ $resource->resource_type_label }}
                            </span>
                            <span class="badge bg-{{ $resource->status == 'active' ? 'success' : ($resource->status == 'inactive' ? 'warning' : 'secondary') }} rounded-pill">
                                {{ $resource->status_label }}
                            </span>
                        </p>
                        <small class="text-muted">
                            <i class="fas fa-user"></i> {{ $resource->user->name ?? 'N/A' }} |
                            <i class="fas fa-calendar"></i> {{ $resource->created_at->format('d/m/Y H:i') }} |
                            <i class="fas fa-building"></i> {{ $resource->club->name ?? 'N/A' }}
                        </small>
                    </div>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.club-resources.edit', $resource->id) }}" class="btn btn-warning btn-sm" title="Chỉnh sửa">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        @if($resource->file_path)
                            <a href="{{ route('admin.club-resources.download', $resource->id) }}" class="btn btn-success btn-sm" title="Tải xuống">
                                <i class="fas fa-download"></i> Tải xuống
                            </a>
                        @endif
                        <form method="POST" action="{{ route('admin.club-resources.destroy', $resource->id) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa tài nguyên này?')">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Mô tả -->
                @if($resource->description)
                <div class="mb-4">
                    <h6 class="text-muted mb-2"><i class="fas fa-align-left"></i> Mô tả</h6>
                    <p class="text-dark">{{ $resource->description }}</p>
                </div>
                @endif

                <!-- File preview -->
                @if($resource->file_path)
                <div class="mb-4">
                    <h6 class="text-muted mb-3"><i class="fas fa-file"></i> File đính kèm</h6>
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center">
                                    @if($resource->thumbnail_url)
                                        <img src="{{ $resource->thumbnail_url }}" alt="{{ $resource->title }}" class="img-fluid rounded">
                                    @else
                                        <i class="fas fa-file fa-4x text-muted"></i>
                                    @endif
                                </div>
                                <div class="col-md-7">
                                    <h6 class="mb-1">{{ $resource->file_name }}</h6>
                                    <p class="text-muted mb-1">
                                        <small>
                                            <i class="fas fa-hdd"></i> {{ $resource->formatted_file_size }} |
                                            <i class="fas fa-file-code"></i> {{ $resource->file_type }}
                                        </small>
                                    </p>
                                </div>
                                <div class="col-md-3 text-end">
                                    <a href="{{ route('admin.club-resources.download', $resource->id) }}" class="btn btn-primary">
                                        <i class="fas fa-download"></i> Tải xuống
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- External link -->
                @if($resource->external_link)
                <div class="mb-4">
                    <h6 class="text-muted mb-2"><i class="fas fa-link"></i> Link liên kết</h6>
                    <a href="{{ $resource->external_link }}" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i> Mở link
                    </a>
                    <p class="text-muted small mt-2">{{ $resource->external_link }}</p>
                </div>
                @endif

                <!-- Tags -->
                @if($resource->tags && count($resource->tags) > 0)
                <div class="mb-4">
                    <h6 class="text-muted mb-2"><i class="fas fa-tags"></i> Tags</h6>
                    @foreach($resource->tags as $tag)
                        <span class="badge bg-secondary me-1">{{ trim($tag) }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Thống kê -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <i class="fas fa-eye fa-2x text-primary"></i>
                    </div>
                    <div class="text-end">
                        <h4 class="mb-0">{{ $resource->view_count }}</h4>
                        <small class="text-muted">Lượt xem</small>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-download fa-2x text-success"></i>
                    </div>
                    <div class="text-end">
                        <h4 class="mb-0">{{ $resource->download_count }}</h4>
                        <small class="text-muted">Lượt tải xuống</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông tin chi tiết -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin chi tiết</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted"><i class="fas fa-hashtag"></i> ID:</td>
                        <td class="text-end"><strong>#{{ $resource->id }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-tag"></i> Loại:</td>
                        <td class="text-end">{{ $resource->resource_type_label }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-building"></i> CLB:</td>
                        <td class="text-end">{{ $resource->club->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-user"></i> Người tạo:</td>
                        <td class="text-end">{{ $resource->user->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-calendar-plus"></i> Ngày tạo:</td>
                        <td class="text-end">{{ $resource->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-calendar-edit"></i> Cập nhật:</td>
                        <td class="text-end">{{ $resource->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-toggle-on"></i> Trạng thái:</td>
                        <td class="text-end">
                            <span class="badge bg-{{ $resource->status == 'active' ? 'success' : ($resource->status == 'inactive' ? 'warning' : 'secondary') }}">
                                {{ $resource->status_label }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-tools"></i> Hành động</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.club-resources.edit', $resource->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                    @if($resource->file_path)
                        <a href="{{ route('admin.club-resources.download', $resource->id) }}" class="btn btn-success">
                            <i class="fas fa-download"></i> Tải xuống
                        </a>
                    @endif
                    <a href="{{ route('admin.club-resources.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                    <hr>
                    <form method="POST" action="{{ route('admin.club-resources.destroy', $resource->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Bạn có chắc chắn muốn xóa tài nguyên này?')">
                            <i class="fas fa-trash"></i> Xóa tài nguyên
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



