@extends('admin.layouts.app')

@section('title', 'Thông báo - Admin')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-bell me-2"></i>Thông báo</h1>
            <p class="text-muted mb-0">Quản lý và xem tất cả thông báo hệ thống</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Bộ lọc và tìm kiếm -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.notifications') }}" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="Tìm kiếm thông báo..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="filter" class="form-select">
                                <option value="all" {{ request('filter') == 'all' ? 'selected' : '' }}>Tất cả</option>
                                <option value="unread" {{ request('filter') == 'unread' ? 'selected' : '' }}>Chưa đọc</option>
                                <option value="read" {{ request('filter') == 'read' ? 'selected' : '' }}>Đã đọc</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="sender_id" class="form-select">
                                <option value="">Tất cả người gửi</option>
                                @foreach($senders ?? [] as $sender)
                                    <option value="{{ $sender->id }}" {{ request('sender_id') == $sender->id ? 'selected' : '' }}>
                                        {{ $sender->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>Tìm kiếm
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách thông báo</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        @php
                            $errorMessage = session('error');
                            $shouldShowError = true;
                            // Không hiển thị alert "Không tìm thấy thông báo" nếu có thông báo
                            if (str_contains($errorMessage, 'Không tìm thấy thông báo') && $notifications->count() > 0) {
                                $shouldShowError = false;
                            }
                        @endphp
                        @if($shouldShowError)
                            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $errorMessage }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    @endif
                    
                    @if($notifications->count() > 0)
                        @foreach($notifications as $notification)
                        <div class="notification-item mb-3 p-3 border rounded {{ !$notification->is_read ? 'bg-light border-primary' : '' }}" style="cursor: pointer;" onclick="window.location.href='{{ route('admin.notifications.show', $notification->id) }}'">
                            <div class="d-flex align-items-start">
                                @php
                                    // Xác định icon và màu sắc dựa trên tiêu đề
                                    $icon = 'fa-info-circle';
                                    $textColor = 'text-primary';
                                    if (str_contains(strtolower($notification->title), 'duyệt') || str_contains(strtolower($notification->title), 'thành công')) {
                                        $icon = 'fa-check-circle';
                                        $textColor = 'text-success';
                                    } elseif (str_contains(strtolower($notification->title), 'từ chối') || str_contains(strtolower($notification->title), 'thất bại')) {
                                        $icon = 'fa-times-circle';
                                        $textColor = 'text-danger';
                                    } elseif (str_contains(strtolower($notification->title), 'clb') || str_contains(strtolower($notification->title), 'câu lạc bộ')) {
                                        $icon = 'fa-users';
                                        $textColor = 'text-info';
                                    } elseif (str_contains(strtolower($notification->title), 'sự kiện') || str_contains(strtolower($notification->title), 'event')) {
                                        $icon = 'fa-calendar';
                                        $textColor = 'text-warning';
                                    }
                                @endphp
                                <div class="me-3">
                                    <i class="fas {{ $icon }} fa-2x {{ $textColor }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0 {{ !$notification->is_read ? 'fw-bold' : '' }}">{{ $notification->title }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ $notification->created_at->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                    <p class="text-muted mb-2">{{ Str::limit($notification->message, 150) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            @if($notification->sender)
                                                <i class="fas fa-user me-1"></i>Gửi bởi: {{ $notification->sender->name }}
                                            @else
                                                <i class="fas fa-user me-1"></i>Hệ thống
                                            @endif
                                        </small>
                                        @if(!$notification->is_read)
                                            <span class="badge bg-primary">Chưa đọc</span>
                                        @else
                                            <span class="badge bg-secondary">Đã đọc</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có thông báo nào</h5>
                            <p class="text-muted">Thông báo mới sẽ xuất hiện ở đây khi có cập nhật.</p>
                        </div>
                    @endif

                    <!-- Pagination -->
                    @if($notifications->hasPages())
                        <div class="mt-4">
                            {{ $notifications->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .notification-item {
        transition: all 0.2s ease;
    }
    
    .notification-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
</style>
@endpush
@endsection

