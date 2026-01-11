@extends('layouts.student')

@section('title', 'Thông báo - UniClubs')

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-12">
        <!-- Page Header -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-bell text-teal"></i> Thông báo
                    </h2>
                    <p class="text-muted mb-0">Cập nhật mới nhất từ UniClubs và câu lạc bộ</p>
                </div>
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('student.notifications.index', ['filter' => 'all']) }}" 
                       class="btn btn-outline-primary {{ ($filter ?? 'all') === 'all' ? 'active' : '' }}">
                        Tất cả
                    </a>
                    <a href="{{ route('student.notifications.index', ['filter' => 'unread']) }}" 
                       class="btn btn-outline-primary {{ ($filter ?? 'all') === 'unread' ? 'active' : '' }}">
                        Chưa đọc
                    </a>
                    <a href="{{ route('student.notifications.index', ['filter' => 'read']) }}" 
                       class="btn btn-outline-primary {{ ($filter ?? 'all') === 'read' ? 'active' : '' }}">
                        Đã đọc
                    </a>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="content-card">
            @forelse($notifications as $notification)
                <div class="notification-item notification-card" data-read="{{ $notification->is_read ? '1' : '0' }}">
                    @php
                        // Xác định icon và màu sắc dựa trên tiêu đề
                        $icon = 'fa-info-circle';
                        $bgColor = 'bg-primary';
                        if (str_contains(strtolower($notification->title), 'duyệt') || str_contains(strtolower($notification->title), 'thành công')) {
                            $icon = 'fa-check-circle';
                            $bgColor = 'bg-success';
                        } elseif (str_contains(strtolower($notification->title), 'từ chối') || str_contains(strtolower($notification->title), 'thất bại')) {
                            $icon = 'fa-times-circle';
                            $bgColor = 'bg-danger';
                        } elseif (str_contains(strtolower($notification->title), 'clb') || str_contains(strtolower($notification->title), 'câu lạc bộ')) {
                            $icon = 'fa-users';
                            $bgColor = 'bg-info';
                        } elseif (str_contains(strtolower($notification->title), 'sự kiện') || str_contains(strtolower($notification->title), 'event')) {
                            $icon = 'fa-calendar';
                            $bgColor = 'bg-warning';
                        }
                    @endphp
                    <div class="notification-icon {{ $bgColor }}">
                        <i class="fas {{ $icon }}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-header">
                            <h6 class="mb-1">{{ $notification->title }}</h6>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i> {{ $notification->created_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <p class="notification-text mb-2">
                            {{ $notification->message }}
                        </p>
                        <div class="notification-actions">
                            <a href="{{ route('student.notifications.show', $notification->id) }}" class="btn btn-sm btn-primary me-2">
                                <i class="fas fa-eye me-1"></i> Xem chi tiết
                            </a>
                            <form action="{{ route('student.notifications.read', $notification->id) }}" method="POST" class="d-inline mark-read-form">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-link text-muted">Đánh dấu đã đọc</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có thông báo nào</h5>
                    <p class="text-muted">Thông báo mới sẽ xuất hiện ở đây khi có cập nhật.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($notifications->hasPages())
            <div class="text-center mt-4">
                {{ $notifications->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>

</div>

@push('styles')
<style>
    /* Filter buttons - smaller size */
    .btn-group[role="group"] .btn {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    
    .notification-item {
        display: flex;
        padding: 1.5rem;
        border-bottom: 1px solid #f3f4f6;
        transition: all 0.2s ease;
    }
    
    .notification-item:last-child {
        border-bottom: none;
    }
    
    .notification-item:hover {
        background-color: #f9fafb;
    }
    
    /* Nền cho chưa đọc dùng data-read, bỏ class unread */
    .notification-card {
        border-left: 4px solid transparent;
    }
    .notification-card[data-read="0"] {
        background-color: #f0fdfa;
        border-left-color: #14b8a6;
    }
    .notification-card[data-read="1"] {
        background-color: #ffffff;
        border-left-color: transparent;
    }
    
    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .notification-content {
        flex-grow: 1;
    }
    
    .notification-header {
        display: flex;
        justify-content: between;
        align-items: flex-start;
        margin-bottom: 0.5rem;
    }
    
    .notification-text {
        color: #6b7280;
        line-height: 1.5;
    }
    
    .notification-actions {
        margin-top: 0.75rem;
    }
    
    .notification-actions .btn {
        margin-right: 0.5rem;
    }
    
    .text-teal {
        color: #14b8a6 !important;
    }
</style>
@endpush

@endsection
