@extends('layouts.student')

@section('title', 'Sự kiện - UniClubs')

@section('content')
<!-- Toast Notification Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
    <div id="toastNotification" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="fas fa-check-circle text-success me-2" id="toastIcon"></i>
            <strong class="me-auto" id="toastTitle">Thông báo</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            <!-- Message will be inserted here -->
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Page Header -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-calendar-alt text-teal"></i> Sự kiện
                    </h2>
                    <p class="text-muted mb-0">Khám phá các sự kiện thú vị và đăng ký tham gia</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-filter="all">Tất cả</button>
                    <button type="button" class="btn btn-outline-primary" data-filter="upcoming">Sắp tới</button>
                    <button type="button" class="btn btn-outline-primary" data-filter="registered">Đã tham gia</button>
                </div>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="content-card" id="upcoming-events-section">
            <h4 class="mb-3">
                <i class="fas fa-clock text-warning me-2"></i> Sự kiện sắp tới
            </h4>
            
            <div class="row" id="events-list">
                @forelse($upcomingEvents as $event)
                    @php
                        $registrationCount = $eventRegistrations[$event->id] ?? 0;
                        $availableSlots = $event->max_participants > 0 ? $event->max_participants - $registrationCount : null;
                        $isRegistered = in_array($event->id, $registeredEvents->pluck('id')->toArray());
                        $isFull = $event->max_participants > 0 && $registrationCount >= $event->max_participants;
                        $isDeadlinePassed = $event->registration_deadline && $event->registration_deadline < now();
                    @endphp
                    <div class="col-12 mb-4 event-item" data-type="upcoming">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <div class="event-date text-center">
                                            <div class="date-day">{{ $event->start_time->format('d') }}</div>
                                            <div class="date-month">{{ strtoupper($event->start_time->format('M')) }}</div>
                                            <div class="date-year">{{ $event->start_time->format('Y') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="card-title mb-2">
                                            <a href="{{ route('student.events.show', $event->id) }}" class="text-decoration-none text-dark">
                                                {{ $event->title }}
                                            </a>
                                        </h5>
                                        <p class="card-text text-muted mb-2">
                                            <i class="fas fa-users me-2"></i> {{ $event->club->name ?? 'N/A' }}
                                        </p>
                                        @if($event->location)
                                        <p class="card-text text-muted mb-2">
                                            <i class="fas fa-map-marker-alt me-2"></i> {{ $event->location }}
                                        </p>
                                        @endif
                                        <p class="card-text mb-0">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 100) }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="mb-2">
                                            @if($isFull)
                                                <span class="badge bg-danger">Đã đầy</span>
                                            @elseif($isDeadlinePassed)
                                                <span class="badge bg-secondary">Hết hạn đăng ký</span>
                                            @elseif($availableSlots !== null && $availableSlots <= 5)
                                                <span class="badge bg-warning">Còn {{ $availableSlots }} chỗ</span>
                                            @elseif($availableSlots !== null)
                                                <span class="badge bg-success">Còn {{ $availableSlots }} chỗ</span>
                                            @else
                                                <span class="badge bg-info">Không giới hạn</span>
                                            @endif
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-users me-1"></i> 
                                                {{ $registrationCount }}{{ $event->max_participants > 0 ? '/' . $event->max_participants : '' }} người đăng ký
                                            </small>
                                        </div>
                                        @if($isRegistered)
                                            <div class="d-flex gap-2 justify-content-end">
                                                <button class="btn btn-success btn-sm" disabled>
                                                    <i class="fas fa-check me-1"></i> Đã đăng ký
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm" onclick="cancelRegistration({{ $event->id }}, this)">
                                                    <i class="fas fa-times me-1"></i> Hủy đăng ký
                                                </button>
                                            </div>
                                        @elseif($isFull || $isDeadlinePassed)
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                <i class="fas fa-ban me-1"></i> Không thể đăng ký
                                            </button>
                                        @else
                                            <button class="btn btn-primary btn-sm" onclick="registerEvent({{ $event->id }}, this)">
                                                <i class="fas fa-plus me-1"></i> Đăng ký
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Không có sự kiện sắp tới</h5>
                            <p class="text-muted">Hãy quay lại sau để xem các sự kiện mới!</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- My Events -->
        <div class="content-card" id="registered-events-section" style="display: none;">
            <h4 class="mb-3">
                <i class="fas fa-check-circle text-success me-2"></i> Sự kiện đã đăng ký
            </h4>
            
            <div class="row" id="registered-events-list">
                @forelse($registeredEvents as $event)
                    <div class="col-12 mb-4 event-item" data-type="registered">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <div class="event-date text-center">
                                            <div class="date-day">{{ $event->start_time->format('d') }}</div>
                                            <div class="date-month">{{ strtoupper($event->start_time->format('M')) }}</div>
                                            <div class="date-year">{{ $event->start_time->format('Y') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="card-title mb-2">
                                            <a href="{{ route('student.events.show', $event->id) }}" class="text-decoration-none text-dark">
                                                {{ $event->title }}
                                            </a>
                                        </h5>
                                        <p class="card-text text-muted mb-2">
                                            <i class="fas fa-users me-2"></i> {{ $event->club->name ?? 'N/A' }}
                                        </p>
                                        @if($event->location)
                                        <p class="card-text text-muted mb-2">
                                            <i class="fas fa-map-marker-alt me-2"></i> {{ $event->location }}
                                        </p>
                                        @endif
                                        <p class="card-text mb-0">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 100) }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="mb-2">
                                            <span class="badge bg-success">Đã đăng ký</span>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i> 
                                                {{ $event->start_time->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('student.events.show', $event->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye me-1"></i> Xem chi tiết
                                            </a>
                                            <button class="btn btn-outline-danger btn-sm" onclick="cancelRegistration({{ $event->id }}, this)">
                                                <i class="fas fa-times me-1"></i> Hủy đăng ký
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Bạn chưa đăng ký sự kiện nào</h5>
                            <p class="text-muted">Hãy khám phá và đăng ký các sự kiện thú vị!</p>
                            <button type="button" class="btn btn-primary" onclick="showAllEvents()">
                                <i class="fas fa-search me-2"></i> Xem sự kiện
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="sidebar">
            <h5 class="sidebar-title">
                <i class="fas fa-calendar-week"></i> Lịch sự kiện
            </h5>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div>
                    <div class="fw-bold">Hôm nay</div>
                    <small class="text-muted">{{ $todayEvents }} sự kiện</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <div class="fw-bold">Tuần này</div>
                    <small class="text-muted">{{ $thisWeekEvents }} sự kiện</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div>
                    <div class="fw-bold">Tháng này</div>
                    <small class="text-muted">{{ $thisMonthEvents }} sự kiện</small>
                </div>
            </div>
        </div>

        <div class="sidebar mt-4">
            <h5 class="sidebar-title">
                <i class="fas fa-fire"></i> Sự kiện hot
            </h5>
            @forelse($hotEvents as $event)
                <div class="sidebar-item">
                    <div class="sidebar-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div>
                        <div class="fw-bold">{{ \Illuminate\Support\Str::limit($event->title, 25) }}</div>
                        <small class="text-muted">{{ $event->registration_percentage }}% đã đăng ký</small>
                    </div>
                </div>
            @empty
                <div class="sidebar-item">
                    <div class="sidebar-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Chưa có sự kiện hot</div>
                        <small class="text-muted">Hãy quay lại sau</small>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('styles')
<style>
    .event-date {
        background: #f0fdfa;
        border: 2px solid #a7f3d0;
        border-radius: 12px;
        padding: 1rem;
        color: #14b8a6;
    }
    
    .date-day {
        font-size: 2rem;
        font-weight: bold;
        line-height: 1;
    }
    
    .date-month {
        font-size: 0.9rem;
        font-weight: 500;
        margin-top: 0.25rem;
    }
    
    .date-year {
        font-size: 0.8rem;
        opacity: 0.7;
    }
    
    .text-teal {
        color: #14b8a6 !important;
    }
    
    /* Toast Notification Styles */
    .toast {
        min-width: 300px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .toast-header {
        font-weight: 600;
    }
    
    .toast-body {
        font-size: 0.95rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // Filter buttons functionality
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('[data-filter]');
        const upcomingSection = document.getElementById('upcoming-events-section');
        const registeredSection = document.getElementById('registered-events-section');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                
                if (filter === 'all') {
                    upcomingSection.style.display = 'block';
                    registeredSection.style.display = 'none';
                } else if (filter === 'upcoming') {
                    upcomingSection.style.display = 'block';
                    registeredSection.style.display = 'none';
                } else if (filter === 'registered') {
                    upcomingSection.style.display = 'none';
                    registeredSection.style.display = 'block';
                }
            });
        });
    });
    
    // Show toast notification
    function showToast(message, type = 'success') {
        const toastElement = document.getElementById('toastNotification');
        const toastIcon = document.getElementById('toastIcon');
        const toastTitle = document.getElementById('toastTitle');
        const toastMessage = document.getElementById('toastMessage');
        
        // Set icon and title based on type
        if (type === 'success') {
            toastIcon.className = 'fas fa-check-circle text-success me-2';
            toastTitle.textContent = 'Thành công';
            toastElement.classList.remove('text-bg-danger', 'text-bg-warning');
            toastElement.classList.add('text-bg-success');
        } else if (type === 'error') {
            toastIcon.className = 'fas fa-exclamation-circle text-danger me-2';
            toastTitle.textContent = 'Lỗi';
            toastElement.classList.remove('text-bg-success', 'text-bg-warning');
            toastElement.classList.add('text-bg-danger');
        } else {
            toastIcon.className = 'fas fa-info-circle text-info me-2';
            toastTitle.textContent = 'Thông báo';
            toastElement.classList.remove('text-bg-success', 'text-bg-danger');
            toastElement.classList.add('text-bg-warning');
        }
        
        toastMessage.textContent = message;
        
        // Show toast
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 3000
        });
        toast.show();
    }
    
    // Register event function
    function registerEvent(eventId, buttonElement) {
        // Disable button to prevent multiple clicks
        const button = buttonElement;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang xử lý...';
        
        fetch(`/student/events/${eventId}/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Đăng ký tham gia sự kiện thành công', 'success');
                // Reload after a short delay to show the toast
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(data.message || 'Có lỗi xảy ra khi đăng ký', 'error');
                // Re-enable button on error
                button.disabled = false;
                button.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Có lỗi xảy ra khi đăng ký', 'error');
            // Re-enable button on error
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }
    
    // Show all events
    function showAllEvents() {
        const allButton = document.querySelector('[data-filter="all"]');
        if (allButton) {
            allButton.click();
        }
    }
    
    // View event details - removed, using direct link now
    
    // Cancel event registration function
    function cancelRegistration(eventId, buttonElement) {
        // Disable button to prevent multiple clicks
        const button = buttonElement;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang xử lý...';
        
        fetch(`/student/events/${eventId}/cancel-registration`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Hủy đăng ký sự kiện thành công', 'success');
                // Reload after a short delay to show the toast
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(data.message || 'Có lỗi xảy ra khi hủy đăng ký', 'error');
                // Re-enable button on error
                button.disabled = false;
                button.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Có lỗi xảy ra khi hủy đăng ký', 'error');
            // Re-enable button on error
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }
</script>
@endpush
@endsection
