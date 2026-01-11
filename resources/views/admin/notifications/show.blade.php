@extends('admin.layouts.app')

@section('title', 'Chi tiết thông báo - Admin')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-bell me-2"></i>Chi tiết thông báo</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.notifications') }}">Thông báo</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.notifications') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Nội dung thông báo</h5>
                </div>
                <div class="card-body">
                    @php
                        // Xác định icon và màu sắc dựa trên tiêu đề
                        $icon = 'fa-info-circle';
                        $textColor = 'text-primary';
                        $bgColor = 'bg-primary';
                        if (str_contains(strtolower($notification->title), 'duyệt') || str_contains(strtolower($notification->title), 'thành công')) {
                            $icon = 'fa-check-circle';
                            $textColor = 'text-success';
                            $bgColor = 'bg-success';
                        } elseif (str_contains(strtolower($notification->title), 'từ chối') || str_contains(strtolower($notification->title), 'thất bại')) {
                            $icon = 'fa-times-circle';
                            $textColor = 'text-danger';
                            $bgColor = 'bg-danger';
                        } elseif (str_contains(strtolower($notification->title), 'clb') || str_contains(strtolower($notification->title), 'câu lạc bộ')) {
                            $icon = 'fa-users';
                            $textColor = 'text-info';
                            $bgColor = 'bg-info';
                        } elseif (str_contains(strtolower($notification->title), 'sự kiện') || str_contains(strtolower($notification->title), 'event')) {
                            $icon = 'fa-calendar';
                            $textColor = 'text-warning';
                            $bgColor = 'bg-warning';
                        }
                    @endphp
                    
                    <div class="text-center mb-4">
                        <div class="d-inline-block p-4 rounded-circle {{ $bgColor }} text-white mb-3">
                            <i class="fas {{ $icon }} fa-3x"></i>
                        </div>
                        <h3 class="{{ $textColor }}">{{ $notification->title }}</h3>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-2"><i class="fas fa-align-left me-2"></i>Nội dung:</h6>
                        <div class="p-3 bg-light rounded">
                            <p class="mb-0" style="white-space: pre-wrap;">{{ $notification->message }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2"><i class="fas fa-user me-2"></i>Người gửi:</h6>
                            <p class="mb-0">
                                @if($notification->sender)
                                    <strong>{{ $notification->sender->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $notification->sender->email }}</small>
                                @else
                                    <strong>Hệ thống</strong>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2"><i class="fas fa-clock me-2"></i>Thời gian:</h6>
                            <p class="mb-0">
                                <strong>{{ $notification->created_at->format('d/m/Y H:i:s') }}</strong>
                                <br>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </p>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h6 class="text-muted mb-2"><i class="fas fa-info-circle me-2"></i>Trạng thái:</h6>
                        @if($notification->is_read)
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Đã đọc</span>
                        @else
                            <span class="badge bg-primary"><i class="fas fa-envelope me-1"></i>Chưa đọc</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

