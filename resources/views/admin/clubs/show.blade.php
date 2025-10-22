@extends('admin.layouts.app')

@section('title', 'Chi tiết câu lạc bộ: ' . $club->name)

@section('content')
<div class="content-header">
    <h1>Chi tiết câu lạc bộ: <span class="text-primary">{{ $club->name }}</span></h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.clubs') }}">Quản lý CLB</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
        </ol>
    </nav>
</div>

<div class="container-fluid">
    <div class="row">
        {{-- Cột thông tin chính --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin chi tiết</h5>
                    <span class="badge bg-{{ $statusColors[$club->status] ?? 'secondary' }}">
                        {{ $statusLabels[$club->status] ?? ucfirst($club->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            @if($club->logo && file_exists(public_path($club->logo)))
                                <img src="{{ asset($club->logo) }}" alt="Logo {{ $club->name }}" class="img-fluid rounded border p-1 mb-3" style="max-height: 150px;">
                            @else
                                <div class="club-logo-fallback rounded d-flex align-items-center justify-content-center mx-auto mb-3" 
                                     style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 2.5rem; font-weight: bold;">
                                    {{ strtoupper(substr($club->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h2 class="card-title">{{ $club->name }}</h2>
                            <p class="text-muted">{{ $club->slug }}</p>
                            <hr>
                            <p><strong><i class="fas fa-tag me-2"></i>Lĩnh vực:</strong> {{ $club->field->name ?? 'Chưa xác định' }}</p>
                            <p><strong><i class="fas fa-user-crown me-2"></i>Chủ sở hữu:</strong> {{ $club->owner->name ?? 'Chưa xác định' }}</p>
                            <p><strong><i class="fas fa-calendar-alt me-2"></i>Ngày tạo:</strong> {{ $club->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5><i class="fas fa-file-alt me-2"></i>Mô tả</h5>
                        <div class="p-3 bg-light rounded">
                            {!! nl2br(e($club->description)) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cột thông tin phụ và hành động --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Thống kê</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Thành viên
                            <span class="badge bg-primary rounded-pill">{{ $club->clubMembers?->count() ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Bài viết
                            <span class="badge bg-info rounded-pill">{{ $club->posts?->count() ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Sự kiện
                            <span class="badge bg-success rounded-pill">{{ $club->events?->count() ?? 0 }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Hành động</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('admin.clubs.edit', $club->id) }}" class="btn btn-warning"><i class="fas fa-edit me-1"></i> Chỉnh sửa</a>
                    <a href="{{ route('admin.clubs.members', $club->id) }}" class="btn btn-info"><i class="fas fa-users me-1"></i> Quản lý thành viên</a>
                    <a href="{{ route('admin.clubs') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Quay lại danh sách</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
