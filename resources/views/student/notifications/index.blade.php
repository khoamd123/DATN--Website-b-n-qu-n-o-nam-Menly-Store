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
                        <form action="{{ route('student.notifications.read', $notification->id) }}" method="POST" class="d-inline">
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
                <a href="{{ route('student.notifications.index', ['category' => 'system']) }}" 
                   class="list-group-item list-group-item-action {{ ($category ?? '') === 'system' ? 'active' : '' }}">
                    <i class="fas fa-info-circle me-2 text-primary"></i> Thông báo hệ thống
                    @if(isset($stats['system']) && $stats['system'] > 0)
                        <span class="badge bg-primary rounded-pill ms-auto">{{ $stats['system'] }}</span>
                    @endif
                </a>
                <a href="{{ route('student.notifications.index', ['category' => 'announcements']) }}" 
                   class="list-group-item list-group-item-action {{ ($category ?? '') === 'announcements' ? 'active' : '' }}">
                    <i class="fas fa-bullhorn me-2 text-success"></i> Thông báo
                    @if(isset($stats['announcements']) && $stats['announcements'] > 0)
                        <span class="badge bg-success rounded-pill ms-auto">{{ $stats['announcements'] }}</span>
                    @endif
                </a>
                <a href="{{ route('student.notifications.index', ['category' => 'clubs']) }}" 
                   class="list-group-item list-group-item-action {{ ($category ?? '') === 'clubs' ? 'active' : '' }}">
                    <i class="fas fa-users me-2 text-info"></i> Câu lạc bộ
                    @if(isset($stats['clubs']) && $stats['clubs'] > 0)
                        <span class="badge bg-info rounded-pill ms-auto">{{ $stats['clubs'] }}</span>
                    @endif
                </a>
            </div>
        </div>

        <div class="sidebar mt-4">
            <h5 class="sidebar-title">
                <i class="fas fa-cog"></i> Cài đặt thông báo
            </h5>
            <form id="notificationSettingsForm">
                @csrf
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="emailNotifications" name="email" {{ ($notificationSettings['email'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="emailNotifications">
                        Thông báo qua email
                    </label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="pushNotifications" name="push" {{ ($notificationSettings['push'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="pushNotifications">
                        Thông báo đẩy
                    </label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="eventNotifications" name="event" {{ ($notificationSettings['event'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="eventNotifications">
                        Thông báo sự kiện
                    </label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="clubNotifications" name="club" {{ ($notificationSettings['club'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="clubNotifications">
                        Thông báo từ CLB
                    </label>
                </div>
                <button type="submit" class="btn btn-teal btn-sm w-100" style="background-color: #14b8a6; color: white;">
                    <i class="fas fa-save me-2"></i> Lưu cài đặt
                </button>
            </form>
        </div>
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

@push('scripts')
    // Xử lý form lưu cài đặt thông báo
    document.getElementById('notificationSettingsForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const data = {
            email: document.getElementById('emailNotifications').checked,
            push: document.getElementById('pushNotifications').checked,
            event: document.getElementById('eventNotifications').checked,
            club: document.getElementById('clubNotifications').checked,
        };
        
        fetch('{{ route("student.notifications.settings") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Hiển thị thông báo thành công
                const btn = this.querySelector('button[type="submit"]');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check me-2"></i> Đã lưu!';
                btn.classList.remove('btn-teal');
                btn.classList.add('btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-teal');
                }, 2000);
            } else {
                alert('Có lỗi xảy ra khi lưu cài đặt');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi lưu cài đặt');
        });
    });
@endpush
@endsection
