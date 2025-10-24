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

    <div class="row">
        <!-- Thông tin sự kiện chính -->
        <div class="col-lg-8">
            <!-- Header sự kiện -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>{{ $event->title }}</h4>
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
                        <span class="badge bg-light text-dark fs-6 px-3 py-2">
                            {{ $statusLabels[$event->status] ?? ucfirst($event->status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if($event->description)
                        <div class="event-description mb-4">
                            <div class="description-header">
                                <div class="description-icon">
                                    <i class="fas fa-align-left"></i>
                                </div>
                                <h5 class="description-title">Mô tả sự kiện</h5>
                            </div>
                            <div class="description-content">
                                <div class="description-text">
                                    {!! nl2br(e($event->description)) !!}
                                </div>
                                <div class="description-footer">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Thông tin chi tiết về sự kiện
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Thông tin thời gian -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-icon bg-primary">
                                    <i class="fas fa-play"></i>
                                </div>
                                <div class="info-content">
                                    <h6 class="info-label">Thời gian bắt đầu</h6>
                                    <p class="info-value">{{ $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('d/m/Y H:i') : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-icon bg-danger">
                                    <i class="fas fa-stop"></i>
                                </div>
                                <div class="info-content">
                                    <h6 class="info-label">Thời gian kết thúc</h6>
                                    <p class="info-value">{{ $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('d/m/Y H:i') : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin chi tiết -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-icon bg-info">
                                    <i class="fas fa-{{ $event->mode === 'offline' ? 'map-marker-alt' : ($event->mode === 'online' ? 'video' : 'users') }}"></i>
                                </div>
                                <div class="info-content">
                                    <h6 class="info-label">Hình thức</h6>
                                    @php
                                        $modeLabels = [
                                            'offline' => 'Tại chỗ',
                                            'online' => 'Trực tuyến',
                                            'hybrid' => 'Kết hợp'
                                        ];
                                    @endphp
                                    <p class="info-value">{{ $modeLabels[$event->mode] ?? ucfirst($event->mode) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-icon bg-warning">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="info-content">
                                    <h6 class="info-label">Địa điểm</h6>
                                    <p class="info-value">{{ $event->location ?? 'Chưa xác định' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin bổ sung -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-icon bg-success">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="info-content">
                                    <h6 class="info-label">Số lượng tối đa</h6>
                                    <p class="info-value">{{ $event->max_participants ?? 'Không giới hạn' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-icon bg-secondary">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="info-content">
                                    <h6 class="info-label">Câu lạc bộ</h6>
                                    <p class="info-value">{{ $event->club->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin người tạo -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-icon bg-dark">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="info-content">
                                    <h6 class="info-label">Người tạo</h6>
                                    <p class="info-value">{{ $event->creator->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-icon bg-light text-dark">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="info-content">
                                    <h6 class="info-label">Cập nhật lần cuối</h6>
                                    <p class="info-value">{{ $event->updated_at ? $event->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hình ảnh sự kiện -->
            @php
                $hasImages = $event->images && $event->images->count() > 0;
                $hasOldImage = !empty($event->image);
                $totalImages = ($hasImages ? $event->images->count() : 0) + ($hasOldImage ? 1 : 0);
            @endphp
            
            @if($hasImages || $hasOldImage)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-images me-2"></i>Hình ảnh sự kiện 
                        <span class="badge bg-primary ms-2">{{ $totalImages }} ảnh</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($hasImages)
                        <div class="row g-3">
                            @foreach($event->images as $index => $image)
                                <div class="col-md-4 col-lg-3">
                                    <div class="image-gallery-item">
                                        <div class="image-container">
                                            <img src="{{ $image->image_url }}" 
                                                 alt="{{ $image->alt_text }}" 
                                                 class="img-fluid rounded"
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#imageModal{{ $index }}"
                                                 style="cursor: pointer; transition: transform 0.3s ease;">
                                            <div class="image-overlay">
                                                <div class="image-number">{{ $index + 1 }}</div>
                                                <div class="image-actions">
                                                    <button class="btn btn-sm btn-light" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#imageModal{{ $index }}">
                                                        <i class="fas fa-expand"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    @if($hasOldImage)
                        <div class="row g-3">
                            <div class="col-md-4 col-lg-3">
                                <div class="image-gallery-item">
                                    <div class="image-container">
                                        <img src="{{ asset('storage/' . $event->image) }}" 
                                             alt="{{ $event->title }}" 
                                             class="img-fluid rounded"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#imageModalOld"
                                             style="cursor: pointer; transition: transform 0.3s ease;">
                                        <div class="image-overlay">
                                            <div class="image-number">{{ $hasImages ? $event->images->count() + 1 : 1 }}</div>
                                            <div class="image-actions">
                                                <button class="btn btn-sm btn-light" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#imageModalOld">
                                                    <i class="fas fa-expand"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Thống kê -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Thống kê sự kiện</h5>
                </div>
                <div class="card-body">
                    @php
                        $registrationsCount = \App\Models\EventRegistration::where('event_id', $event->id)->count();
                        $commentsCount = \App\Models\EventComment::where('event_id', $event->id)->count();
                    @endphp
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <h3 class="stat-number text-primary">{{ $registrationsCount }}</h3>
                                <p class="stat-label">Đăng ký</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <h3 class="stat-number text-success">{{ $commentsCount }}</h3>
                                <p class="stat-label">Bình luận</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hành động -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Hành động</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($event->status === 'pending')
                            <form method="POST" action="{{ route('admin.events.approve', $event->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Bạn có chắc muốn duyệt sự kiện này?')">
                                    <i class="fas fa-check me-2"></i>Duyệt sự kiện
                                </button>
                            </form>
                            
                            <form method="POST" action="{{ route('admin.events.cancel', $event->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-lg" onclick="return confirm('Bạn có chắc chắn muốn hủy sự kiện này?')">
                                    <i class="fas fa-times me-2"></i>Hủy sự kiện
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa
                        </a>
                        
                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Tạo sự kiện mới
                        </a>
                        
                        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                        </a>
                    </div>
                </div>
            </div>

            <!-- Thông tin nhanh -->
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="quick-info">
                        <div class="quick-info-item">
                            <i class="fas fa-hashtag text-muted"></i>
                            <span>ID: <strong>{{ $event->id }}</strong></span>
                        </div>
                        <div class="quick-info-item">
                            <i class="fas fa-calendar-plus text-muted"></i>
                            <span>Tạo: <strong>{{ $event->created_at ? $event->created_at->format('d/m/Y') : 'N/A' }}</strong></span>
                        </div>
                        <div class="quick-info-item">
                            <i class="fas fa-clock text-muted"></i>
                            <span>Cập nhật: <strong>{{ $event->updated_at ? $event->updated_at->format('d/m/Y') : 'N/A' }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Event Description */
.event-description {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 0;
    border: 1px solid #dee2e6;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

.event-description:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.description-header {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.description-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    transform: translate(30px, -30px);
}

.description-icon {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.2rem;
    backdrop-filter: blur(10px);
}

.description-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.description-content {
    padding: 1.5rem;
    position: relative;
}

.description-text {
    font-size: 1.1rem;
    line-height: 1.7;
    color: #2c3e50;
    margin-bottom: 1rem;
    text-align: justify;
    position: relative;
    padding-left: 1rem;
    border-left: 4px solid #007bff;
}

.description-text::before {
    content: '"';
    position: absolute;
    top: -10px;
    left: -5px;
    font-size: 3rem;
    color: #007bff;
    opacity: 0.3;
    font-family: serif;
    line-height: 1;
}

.description-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
    background: rgba(0,123,255,0.05);
    margin: 0 -1.5rem -1.5rem -1.5rem;
    padding: 1rem 1.5rem;
}

.description-footer small {
    font-weight: 500;
    color: #6c757d !important;
}

/* Responsive cho description */
@media (max-width: 768px) {
    .description-header {
        padding: 1rem;
    }
    
    .description-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
        margin-right: 0.75rem;
    }
    
    .description-title {
        font-size: 1.1rem;
    }
    
    .description-content {
        padding: 1rem;
    }
    
    .description-text {
        font-size: 1rem;
        padding-left: 0.75rem;
    }
    
    .description-text::before {
        font-size: 2.5rem;
        top: -8px;
        left: -3px;
    }
}

/* Info Cards */
.info-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    margin-bottom: 1rem;
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.info-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: white;
    font-size: 1.2rem;
}

.info-content {
    flex: 1;
}

.info-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
    font-weight: 600;
}

.info-value {
    font-size: 1rem;
    color: #212529;
    margin-bottom: 0;
    font-weight: 500;
}

/* Image Gallery */
.image-gallery-item {
    position: relative;
    margin-bottom: 1rem;
}

.image-container {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.image-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.image-container img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.image-container:hover img {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(0,0,0,0.7), rgba(0,0,0,0.3));
    opacity: 0;
    transition: opacity 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-container:hover .image-overlay {
    opacity: 1;
}

.image-number {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.image-actions {
    position: absolute;
    bottom: 10px;
    right: 10px;
}

/* Statistics */
.stat-item {
    text-align: center;
    padding: 1rem;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: white;
    font-size: 1.5rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 0;
    font-weight: 500;
}

/* Quick Info */
.quick-info-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}

.quick-info-item:last-child {
    border-bottom: none;
}

.quick-info-item i {
    width: 20px;
    margin-right: 0.75rem;
    font-size: 1rem;
}

/* Gradient backgrounds */
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
}

.bg-gradient-secondary {
    background: linear-gradient(135deg, #6c757d, #545b62);
}

/* Responsive */
@media (max-width: 768px) {
    .info-card {
        flex-direction: column;
        text-align: center;
    }
    
    .info-icon {
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .stat-item {
        padding: 0.5rem;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
}
</style>
@endpush
@endsection