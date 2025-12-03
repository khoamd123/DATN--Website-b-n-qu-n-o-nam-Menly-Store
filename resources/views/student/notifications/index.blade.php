@extends('layouts.student')

@section('title', 'Thông báo - UniClubs')

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Page Header -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-bell text-teal"></i> Thông báo
                    </h2>
                    <p class="text-muted mb-0">Cập nhật mới nhất từ UniClubs và câu lạc bộ</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active">Tất cả</button>
                    <button type="button" class="btn btn-outline-primary">Chưa đọc</button>
                    <button type="button" class="btn btn-outline-primary">Đã đọc</button>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="content-card">
            @forelse($notifications as $notification)
                <div class="notification-item {{ !$notification->is_read ? 'unread' : '' }}">
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
                            @if(!$notification->is_read)
                                <form action="{{ route('student.notifications.mark-read', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-muted">Đánh dấu đã đọc</button>
                                </form>
                            @else
                                <span class="text-muted small"><i class="fas fa-check me-1"></i> Đã đọc</span>
                            @endif
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

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="sidebar">
            <h5 class="sidebar-title">
                <i class="fas fa-filter"></i> Lọc thông báo
            </h5>
            <div class="list-group list-group-flush">
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-info-circle me-2 text-primary"></i> Thông báo hệ thống
                    <span class="badge bg-primary rounded-pill ms-auto">1</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-calendar me-2 text-success"></i> Sự kiện
                    <span class="badge bg-success rounded-pill ms-auto">2</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-users me-2 text-info"></i> Câu lạc bộ
                    <span class="badge bg-info rounded-pill ms-auto">1</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-trophy me-2 text-warning"></i> Giải thưởng
                    <span class="badge bg-warning rounded-pill ms-auto">1</span>
                </a>
            </div>
        </div>

        <div class="sidebar mt-4">
            <h5 class="sidebar-title">
                <i class="fas fa-cog"></i> Cài đặt thông báo
            </h5>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                <label class="form-check-label" for="emailNotifications">
                    Thông báo qua email
                </label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="pushNotifications" checked>
                <label class="form-check-label" for="pushNotifications">
                    Thông báo đẩy
                </label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="eventNotifications" checked>
                <label class="form-check-label" for="eventNotifications">
                    Thông báo sự kiện
                </label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="clubNotifications" checked>
                <label class="form-check-label" for="clubNotifications">
                    Thông báo từ CLB
                </label>
            </div>
            <button class="btn btn-primary btn-sm w-100">
                <i class="fas fa-save me-2"></i> Lưu cài đặt
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
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
    
    .notification-item.unread {
        background-color: #f0fdfa;
        border-left: 4px solid #14b8a6;
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
