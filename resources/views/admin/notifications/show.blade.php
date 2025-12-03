@extends('admin.layouts.app')

@section('title', 'Chi tiết Thông báo - CLB Admin')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-bell text-primary"></i> Chi tiết Thông báo</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.notifications') }}">Thông báo</a></li>
                    <li class="breadcrumb-item active">Chi tiết</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @if($notification->related_type === 'App\Models\Event' && $notification->related_id)
                <a href="{{ route('admin.events.show', $notification->related_id) }}" class="btn btn-primary">
                    <i class="fas fa-calendar-check me-1"></i> Xem sự kiện
                </a>
            @endif
            <a href="{{ route('admin.notifications') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Chi tiết thông báo -->
<div class="card">
    <div class="card-header {{ $notification->read_at ? '' : 'bg-primary text-white' }}">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                @if($notification->type === 'event_registration')
                    <i class="fas fa-calendar-check fa-2x me-3"></i>
                @elseif($notification->type === 'event_created')
                    <i class="fas fa-calendar-plus fa-2x me-3"></i>
                @else
                    <i class="fas fa-bell fa-2x me-3"></i>
                @endif
                <div>
                    <h4 class="mb-0 {{ $notification->read_at ? '' : 'text-white' }}">
                        {{ $notification->title }}
                    </h4>
                    @if(!$notification->read_at)
                        <span class="badge bg-light text-primary">Mới</span>
                    @endif
                </div>
            </div>
            <div class="text-end">
                @if($notification->read_at)
                    <small class="text-muted">
                        <i class="fas fa-check-circle text-success"></i> Đã đọc
                    </small>
                @else
                    <small class="text-white">
                        <i class="fas fa-circle text-warning"></i> Chưa đọc
                    </small>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Nội dung thông báo -->
        <div class="mb-4">
            <h5 class="text-muted mb-3">
                <i class="fas fa-file-alt me-2"></i>Nội dung thông báo
            </h5>
            <div class="alert alert-info">
                <p class="mb-0" style="font-size: 1.1rem; line-height: 1.6;">
                    {{ $notification->message }}
                </p>
            </div>
        </div>

        <hr>

        <!-- Thông tin chi tiết -->
        <div class="row">
            <div class="col-md-6">
                <h5 class="text-muted mb-3">
                    <i class="fas fa-info-circle me-2"></i>Thông tin chi tiết
                </h5>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th width="40%"><i class="fas fa-tag me-2"></i>Loại thông báo:</th>
                            <td>
                                @if($notification->type === 'event_registration')
                                    <span class="badge bg-primary">
                                        <i class="fas fa-calendar-check me-1"></i>Đăng ký sự kiện
                                    </span>
                                @elseif($notification->type === 'event_created')
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-calendar-plus me-1"></i>Sự kiện mới cần duyệt
                                    </span>
                                @else
                                    <span class="badge bg-info">
                                        <i class="fas fa-bell me-1"></i>Thông báo chung
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-user me-2"></i>Người gửi:</th>
                            <td>
                                @if($notification->sender)
                                    <strong>{{ $notification->sender->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $notification->sender->email }}</small>
                                @else
                                    <span class="text-muted">Hệ thống</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-clock me-2"></i>Thời gian tạo:</th>
                            <td>
                                <strong>{{ $notification->created_at->format('d/m/Y H:i:s') }}</strong>
                                <br>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </td>
                        </tr>
                        @if($notification->read_at)
                        <tr>
                            <th><i class="fas fa-check-circle me-2"></i>Thời gian đọc:</th>
                            <td>
                                <strong class="text-success">{{ $notification->read_at->format('d/m/Y H:i:s') }}</strong>
                                <br>
                                <small class="text-muted">{{ $notification->read_at->diffForHumans() }}</small>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <h5 class="text-muted mb-3">
                    <i class="fas fa-link me-2"></i>Liên kết liên quan
                </h5>
                @if($notification->related_type === 'App\Models\Event' && $notification->related)
                    <div class="card border-primary">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-calendar-check text-primary me-2"></i>
                                Sự kiện liên quan
                            </h6>
                            <p class="card-text">
                                <strong>{{ $notification->related->title }}</strong>
                            </p>
                            @if($notification->related->description)
                                <p class="card-text text-muted small">
                                    {{ Str::limit($notification->related->description, 100) }}
                                </p>
                            @endif
                            <div class="mt-3">
                                <a href="{{ route('admin.events.show', $notification->related->id) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i> Xem chi tiết sự kiện
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-secondary">
                        <i class="fas fa-info-circle me-2"></i>
                        Không có liên kết liên quan
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .card-header.bg-primary {
        border-bottom: 3px solid #0056b3;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .alert-info {
        background-color: #e7f3ff;
        border-left: 4px solid #0d6efd;
    }
</style>
@endsection

