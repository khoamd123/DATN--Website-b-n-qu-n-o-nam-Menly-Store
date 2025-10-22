@extends('admin.layouts.app')

@section('title', 'Chi tiết sự kiện')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-calendar-alt"></i> Chi tiết sự kiện</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.plans-schedule') }}">Kế hoạch</a></li>
                <li class="breadcrumb-item active">Chi tiết sự kiện</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex flex-row flex-nowrap gap-3 align-items-start">
        <!-- Thông tin sự kiện -->
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin sự kiện</h5>
                </div>
                <div class="card-body">
                    <h4>{{ $event->title }}</h4>
                    <p class="text-muted">{{ $event->description }}</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Thời gian bắt đầu:</strong><br>
                                <span class="text-primary">{{ $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('d/m/Y H:i') : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Thời gian kết thúc:</strong><br>
                                <span class="text-primary">{{ $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('d/m/Y H:i') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Chế độ:</strong><br>
                                @php
                                    $modeLabels = [
                                        'offline' => 'Tại chỗ',
                                        'online' => 'Trực tuyến',
                                        'hybrid' => 'Kết hợp'
                                    ];
                                @endphp
                                <span class="badge bg-info">{{ $modeLabels[$event->mode] ?? ucfirst($event->mode) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Địa điểm:</strong><br>
                                <span>{{ $event->location ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Số lượng tối đa:</strong><br>
                                <span>{{ $event->max_participants ?? 'Không giới hạn' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Trạng thái:</strong><br>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'ongoing' => 'success',
                                        'completed' => 'primary',
                                        'cancelled' => 'danger'
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Bản nháp',
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'ongoing' => 'Đang diễn ra',
                                        'completed' => 'Hoàn thành',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$event->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$event->status] ?? ucfirst($event->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Câu lạc bộ:</strong><br>
                                <span class="text-primary">{{ $event->club->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Người tạo:</strong><br>
                                <span>{{ $event->creator->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Ngày tạo:</strong><br>
                                <span class="text-muted">{{ $event->created_at ? $event->created_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Cập nhật lần cuối:</strong><br>
                                <span class="text-muted">{{ $event->updated_at ? $event->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    </div>

                    @if($event->image)
                    <hr>
                    <div class="mt-3">
                        <h6 class="mb-2"><i class="fas fa-image"></i> Thông tin hình ảnh sự kiện</h6>
                        <div style="max-width: 280px;">
                            <div style="width: 100%; height: 160px; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.06); border: 1px solid #e9ecef; background: #f8f9fa; display:flex; align-items:center; justify-content:center;">
                                <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}" style="width: 100%; height: 100%; object-fit: cover; display:block;">
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Thống kê và hành động -->
        <div class="flex-shrink-0" style="width: 360px; max-width: 100%;">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê</h5>
                </div>
                <div class="card-body">
                    @php
                        $registrationsCount = \App\Models\EventRegistration::where('event_id', $event->id)->count();
                        $commentsCount = \App\Models\EventComment::where('event_id', $event->id)->count();
                    @endphp
                    
                    <div class="text-center">
                        <h3 class="text-primary">{{ $registrationsCount }}</h3>
                        <small class="text-muted">Đăng ký tham gia</small>
                    </div>
                    
                    <div class="text-center mt-3">
                        <h3 class="text-success">{{ $commentsCount }}</h3>
                        <small class="text-muted">Bình luận</small>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Hành động</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($event->status === 'pending')
                            <form method="POST" action="{{ route('admin.events.approve', $event->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Bạn có chắc muốn duyệt sự kiện này?')">
                                    <i class="fas fa-check"></i> Duyệt sự kiện
                                </button>
                            </form>
                            
                            <form method="POST" action="{{ route('admin.events.cancel', $event->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Bạn có chắc chắn muốn hủy sự kiện này?')">
                                    <i class="fas fa-times"></i> Hủy sự kiện
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tạo sự kiện mới
                        </a>
                        
                        <a href="{{ route('admin.plans-schedule') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection