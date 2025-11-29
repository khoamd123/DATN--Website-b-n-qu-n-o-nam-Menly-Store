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
            @if(isset($notifications) && $notifications->count() > 0)
                @foreach($notifications as $notification)
                    @php
                        $isRead = in_array($notification->id, $readNotificationIds ?? []);
                        $iconClass = 'bg-primary';
                        $icon = 'fa-info-circle';
                        
                        if ($notification->type === 'event_registration') {
                            $iconClass = 'bg-success';
                            $icon = 'fa-calendar-check';
                        } elseif ($notification->type === 'club_rejection') {
                            $iconClass = 'bg-danger';
                            $icon = 'fa-times-circle';
                        }
                    @endphp
                    <div class="notification-item {{ !$isRead ? 'unread' : '' }}" data-notification-id="{{ $notification->id }}">
                        <div class="notification-icon {{ $iconClass }}">
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
                                @if($notification->type === 'event_registration' && $notification->related_id)
                                    <a href="{{ route('student.events.show', $notification->related_id) }}" class="btn btn-sm btn-outline-primary">Xem sự kiện</a>
                                @elseif($notification->type === 'club_rejection')
                                    <a href="{{ route('student.clubs.index') }}" class="btn btn-sm btn-outline-primary">Xem CLB</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Bạn chưa có thông báo nào.</p>
                </div>
            @endif
        </div>

    </div>

    {{-- Modal Thông báo --}}
    @if(isset($latestAnnouncement) && $latestAnnouncement)
    <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 0; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header" style="border-bottom: 1px solid #e0e0e0; padding: 1.5rem 2rem; position: relative;">
                    <div class="w-100 text-center">
                        <h1 class="modal-title fw-bold m-0" id="announcementModalLabel" style="font-size: 1.3rem; color: #333; text-transform: uppercase; letter-spacing: 1px;">
                            THÔNG BÁO
                        </h1>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="position: absolute; top: 1rem; right: 1rem; font-size: 1rem; opacity: 0.7;"></button>
                </div>
                <div class="modal-body" style="padding: 2rem; max-height: calc(100vh - 60px); overflow-y: auto;">
                    <h2 class="mb-3" style="font-size: 1.3rem; color: #333; font-weight: 600; line-height: 1.4;">
                        {{ $latestAnnouncement->title }}
                    </h2>
                    <div class="announcement-content" style="line-height: 1.8; color: #333; font-size: 1rem;">
                        {!! $latestAnnouncement->content !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="sidebar">
            <h5 class="sidebar-title">
                <i class="fas fa-filter"></i> Lọc thông báo
            </h5>
            <div class="list-group list-group-flush">
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-info-circle me-2 text-primary"></i> Thông báo hệ thống
                    <span class="badge bg-primary rounded-pill ms-auto">{{ $stats['system'] ?? 0 }}</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-bullhorn me-2 text-warning"></i> Thông báo CLB
                    <span class="badge bg-warning rounded-pill ms-auto">{{ $stats['announcements'] ?? 0 }}</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-users me-2 text-info"></i> Câu lạc bộ
                    <span class="badge bg-info rounded-pill ms-auto">{{ $stats['clubs'] ?? 0 }}</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-trophy me-2 text-secondary"></i> Giải thưởng
                    <span class="badge bg-secondary rounded-pill ms-auto">{{ $stats['awards'] ?? 0 }}</span>
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
    
    /* Modal Announcement Styles */
    #announcementModal .modal-content {
        animation: modalFadeIn 0.3s ease-out;
    }
    
    #announcementModal .modal-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: nowrap !important;
        visibility: visible !important;
    }
    
    #announcementModal .modal-title {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-size: 2rem !important;
        color: #333 !important;
        margin: 0 !important;
        padding: 0 !important;
        flex: 1 1 auto !important;
        margin-right: 1rem !important;
    }
    
    #announcementModal .modal-title::before,
    #announcementModal .modal-title::after {
        display: none !important;
    }
    
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    #announcementModal .modal-dialog {
        max-width: 85%;
    }
    
    #announcementModal .modal-body {
        max-height: calc(100vh - 60px);
        overflow-y: auto;
    }
    
    #announcementModal .announcement-content {
        max-height: none;
        overflow-y: visible;
    }
    
    #announcementModal .announcement-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1rem 0;
    }
    
    #announcementModal .announcement-content p {
        margin-bottom: 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // Đánh dấu notification là đã đọc khi click
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.notification-item').forEach(function(item) {
            item.addEventListener('click', function() {
                const notificationId = this.getAttribute('data-notification-id');
                if (notificationId && !this.classList.contains('read')) {
                    // Gửi request đánh dấu đã đọc
                    fetch('/student/notifications/' + notificationId + '/read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    }).then(function() {
                        this.classList.remove('unread');
                        this.classList.add('read');
                    }.bind(this));
                }
            });
        });
    });

    @if(isset($latestAnnouncement) && $latestAnnouncement)
    document.addEventListener('DOMContentLoaded', function() {
        // Hiển thị modal mỗi khi trang load
        var modalElement = document.getElementById('announcementModal');
        if (modalElement) {
            // Thêm delay nhỏ để đảm bảo Bootstrap đã load
            setTimeout(function() {
                var modal = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
                modal.show();
            }, 100);
        }
    });
    @endif
</script>
@endpush
@endsection
