@extends('admin.layouts.app')

@section('title', 'Chi tiết sự kiện - ' . $event->title)

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Chi tiết sự kiện</h1>
            <p class="text-muted mb-0">{{ $event->title }}</p>
        </div>
        <div>
            <a href="{{ route('admin.plans-schedule') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="{{ route('admin.events.index') }}" class="btn btn-primary">
                <i class="fas fa-list"></i> Danh sách sự kiện
            </a>
        </div>
    </div>
</div>

<!-- Thông báo -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <!-- Thông tin chính -->
    <div class="col-lg-8">
        <!-- Thông tin cơ bản -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle"></i> Thông tin sự kiện
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Thông tin cơ bản</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>ID:</strong></td>
                                <td>{{ $event->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tên sự kiện:</strong></td>
                                <td>{{ $event->title }}</td>
                            </tr>
                            <tr>
                                <td><strong>Slug:</strong></td>
                                <td><code>{{ $event->slug }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>Câu lạc bộ:</strong></td>
                                <td>
                                    <span class="badge bg-primary">{{ $event->club->name ?? 'Không xác định' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Người tạo:</strong></td>
                                <td>{{ $event->creator->name ?? 'Không xác định' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Chế độ:</strong></td>
                                <td>
                                    @php
                                        $modeColors = [
                                            'offline' => 'primary',
                                            'online' => 'success',
                                            'hybrid' => 'info'
                                        ];
                                        $modeLabels = [
                                            'offline' => 'Trực tiếp',
                                            'online' => 'Trực tuyến',
                                            'hybrid' => 'Kết hợp'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $modeColors[$event->mode] ?? 'secondary' }}">
                                        <i class="fas fa-{{ $event->mode === 'offline' ? 'map-marker-alt' : ($event->mode === 'online' ? 'video' : 'users') }}"></i>
                                        {{ $modeLabels[$event->mode] ?? ucfirst($event->mode) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Trạng thái & Thời gian</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Trạng thái:</strong></td>
                                <td>
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
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Thời gian bắt đầu:</strong></td>
                                <td>{{ \Carbon\Carbon::parse($event->start_time)->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Thời gian kết thúc:</strong></td>
                                <td>{{ \Carbon\Carbon::parse($event->end_time)->format('d/m/Y H:i') }}</td>
                            </tr>
                            @if($event->location)
                            <tr>
                                <td><strong>Địa điểm:</strong></td>
                                <td>{{ $event->location }}</td>
                            </tr>
                            @endif
                            @if($event->max_participants)
                            <tr>
                                <td><strong>Số lượng tối đa:</strong></td>
                                <td>{{ $event->max_participants }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Tạo lúc:</strong></td>
                                <td>{{ \Carbon\Carbon::parse($event->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Cập nhật lúc:</strong></td>
                                <td>{{ \Carbon\Carbon::parse($event->updated_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Mô tả chi tiết</h6>
                        <div class="border p-3 rounded bg-light">
                            {{ $event->description }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lịch sử hoạt động -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history"></i> Lịch sử hoạt động
                </h5>
            </div>
            <div class="card-body">
                @if($event->logs->count() > 0)
                    <div class="timeline">
                        @foreach($event->logs as $log)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $log->action === 'created' ? 'success' : ($log->action === 'approved' ? 'info' : 'warning') }}"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">{{ ucfirst($log->action) }}</h6>
                                    <p class="text-muted mb-1">{{ $log->reason }}</p>
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> {{ $log->user->name ?? 'Unknown' }}
                                        <i class="fas fa-clock ms-2"></i> {{ $log->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">Chưa có lịch sử hoạt động nào.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Hành động -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cogs"></i> Hành động
                </h5>
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
                    @endif
                    
                    @if(in_array($event->status, ['pending', 'approved', 'ongoing']))
                        <form method="POST" action="{{ route('admin.events.cancel', $event->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Bạn có chắc muốn hủy sự kiện này?')">
                                <i class="fas fa-times"></i> Hủy sự kiện
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Thống kê -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar"></i> Thống kê
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $event->registrations->count() }}</h4>
                        <small class="text-muted">Đăng ký</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $event->comments->count() }}</h4>
                        <small class="text-muted">Bình luận</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông tin CLB -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users"></i> Thông tin CLB
                </h5>
            </div>
            <div class="card-body">
                @if($event->club)
                    <h6>{{ $event->club->name }}</h6>
                    <p class="text-muted">{{ Str::limit($event->club->description, 100) }}</p>
                    <small class="text-muted">
                        <i class="fas fa-user"></i> {{ $event->club->owner->name ?? 'Unknown' }}
                    </small>
                @else
                    <p class="text-muted">Không có thông tin CLB.</p>
                @endif
            </div>
        </div>

        <!-- Danh sách đăng ký -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-check"></i> Đăng ký tham gia
                </h5>
            </div>
            <div class="card-body">
                @if($event->registrations->count() > 0)
                    @foreach($event->registrations->take(5) as $registration)
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $registration->user->name ?? 'Unknown' }}</h6>
                                <small class="text-muted">{{ $registration->status }}</small>
                            </div>
                        </div>
                    @endforeach
                    @if($event->registrations->count() > 5)
                        <small class="text-muted">Và {{ $event->registrations->count() - 5 }} người khác...</small>
                    @endif
                @else
                    <p class="text-muted">Chưa có ai đăng ký tham gia.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.avatar-sm {
    width: 40px;
    height: 40px;
}
</style>
@endpush
@endsection
