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
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-filter="all">Tất cả</button>
                    <button type="button" class="btn btn-outline-primary" data-filter="ongoing">Đang diễn ra</button>
                    <button type="button" class="btn btn-outline-primary" data-filter="upcoming">Sắp tới</button>
                    <button type="button" class="btn btn-outline-primary" data-filter="registered">Đã tham gia</button>
                </div>
            </div>
        </div>

        <!-- Ongoing Events -->
        <div class="content-card" id="ongoing-events-section">
            <h4 class="mb-4 fw-bold">
                <i class="fas fa-play-circle text-success me-2"></i> Sự kiện đang diễn ra
            </h4>
            
            <div class="row" id="ongoing-events-list">
                @forelse($ongoingEvents as $event)
                    @php
                        $registrationCount = $eventRegistrations[$event->id] ?? 0;
                        $availableSlots = $event->max_participants > 0 ? $event->max_participants - $registrationCount : null;
                        $isRegistered = in_array($event->id, $registeredEvents->pluck('id')->toArray());
                        $isFull = $event->max_participants > 0 && $registrationCount >= $event->max_participants;
                        $hasImages = $event->images && $event->images->count() > 0;
                        $hasOldImage = !empty($event->image);
                    @endphp
                    <div class="col-md-6 mb-4 event-item" data-type="ongoing">
                        <div class="card border-0 shadow-sm h-100 event-card-hover">
                            @if($hasImages || $hasOldImage)
                                <div class="event-image-container">
                                    @if($hasImages)
                                        <img src="{{ $event->images->first()->image_url }}" 
                                             alt="{{ $event->title }}" 
                                             class="w-100 h-100" 
                                             style="object-fit: cover;">
                                    @elseif($hasOldImage)
                                        <img src="{{ asset('storage/' . $event->image) }}" 
                                             alt="{{ $event->title }}" 
                                             class="w-100 h-100" 
                                             style="object-fit: cover;">
                                    @endif
                                    <div class="event-status-overlay">
                                        <span class="badge bg-success">Đang diễn ra</span>
                                    </div>
                                </div>
                            @else
                                <div class="event-date-header bg-success text-white">
                                    <div class="date-day">{{ $event->start_time->format('d') }}</div>
                                    <div class="date-month-year">{{ strtoupper($event->start_time->format('M Y')) }}</div>
                                </div>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title mb-2">
                                    <a href="{{ route('student.events.show', $event->id) }}" class="text-decoration-none text-dark fw-bold">
                                        {{ $event->title }}
                                    </a>
                                </h5>
                                <p class="card-text text-muted small mb-2">
                                    <i class="fas fa-users me-1 text-teal"></i>{{ $event->club->name ?? 'N/A' }}
                                    @if($event->visibility === 'internal' && $event->club)
                                        <span class="badge bg-warning text-dark ms-2">
                                            <i class="fas fa-lock me-1"></i>Nội bộ {{ $event->club->name }}
                                        </span>
                                    @endif
                                </p>
                                <p class="card-text small mb-3">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 100) }}
                                </p>
                                <div class="mb-3">
                                    <div class="small text-muted mb-1">
                                        <i class="far fa-calendar me-1 text-teal"></i>
                                        <strong>Bắt đầu:</strong> {{ $event->start_time->format('d/m/Y H:i') }}
                                    </div>
                                    @if($event->location)
                                        <div class="small text-muted">
                                            <i class="fas fa-map-marker-alt me-1 text-teal"></i>{{ $event->location }}
                                        </div>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i> 
                                        {{ $registrationCount }}{{ $event->max_participants > 0 ? '/' . $event->max_participants : '' }} người đăng ký
                                    </small>
                                </div>
                                <div class="d-flex gap-2">
                                    @if($isRegistered)
                                        <button class="btn btn-success btn-sm flex-fill" disabled>
                                            <i class="fas fa-check me-1"></i> Đã đăng ký
                                        </button>
                                        <a href="{{ route('student.events.show', $event->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i> Chi tiết
                                        </a>
                                    @else
                                        <a href="{{ route('student.events.show', $event->id) }}" class="btn btn-primary btn-sm flex-fill">
                                            <i class="fas fa-eye me-1"></i> Xem chi tiết
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Không có sự kiện đang diễn ra</h5>
                            <p class="text-muted">Hiện tại không có sự kiện nào đang diễn ra.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="content-card" id="upcoming-events-section">
            <h4 class="mb-4 fw-bold">
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
                        $hasImages = $event->images && $event->images->count() > 0;
                        $hasOldImage = !empty($event->image);
                    @endphp
                    <div class="col-md-6 mb-4 event-item" data-type="upcoming">
                        <div class="card border-0 shadow-sm h-100 event-card-hover">
                            @if($hasImages || $hasOldImage)
                                <div class="event-image-container">
                                    @if($hasImages)
                                        <img src="{{ $event->images->first()->image_url }}" 
                                             alt="{{ $event->title }}" 
                                             class="w-100 h-100" 
                                             style="object-fit: cover;">
                                    @elseif($hasOldImage)
                                        <img src="{{ asset('storage/' . $event->image) }}" 
                                             alt="{{ $event->title }}" 
                                             class="w-100 h-100" 
                                             style="object-fit: cover;">
                                    @endif
                                    <div class="event-status-overlay">
                                        @if($isFull)
                                            <span class="badge bg-danger">Đã đầy</span>
                                        @elseif($isDeadlinePassed)
                                            <span class="badge bg-secondary">Hết hạn</span>
                                        @elseif($availableSlots !== null && $availableSlots <= 5)
                                            <span class="badge bg-warning">Còn {{ $availableSlots }} chỗ</span>
                                        @elseif($availableSlots !== null)
                                            <span class="badge bg-success">Còn {{ $availableSlots }} chỗ</span>
                                        @else
                                            <span class="badge bg-info">Không giới hạn</span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="event-date-header bg-primary text-white">
                                    <div class="date-day">{{ $event->start_time->format('d') }}</div>
                                    <div class="date-month-year">{{ strtoupper($event->start_time->format('M Y')) }}</div>
                                </div>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title mb-2">
                                    <a href="{{ route('student.events.show', $event->id) }}" class="text-decoration-none text-dark fw-bold">
                                        {{ $event->title }}
                                    </a>
                                </h5>
                                <p class="card-text text-muted small mb-2">
                                    <i class="fas fa-users me-1 text-teal"></i>{{ $event->club->name ?? 'N/A' }}
                                    @if($event->visibility === 'internal' && $event->club)
                                        <span class="badge bg-warning text-dark ms-2">
                                            <i class="fas fa-lock me-1"></i>Nội bộ {{ $event->club->name }}
                                        </span>
                                    @endif
                                </p>
                                <p class="card-text small mb-3">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 100) }}
                                </p>
                                <div class="mb-3">
                                    <div class="small text-muted mb-1">
                                        <i class="far fa-calendar me-1 text-teal"></i>
                                        <strong>Bắt đầu:</strong> {{ $event->start_time->format('d/m/Y H:i') }}
                                    </div>
                                    @if($event->location)
                                        <div class="small text-muted">
                                            <i class="fas fa-map-marker-alt me-1 text-teal"></i>{{ $event->location }}
                                        </div>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i> 
                                        {{ $registrationCount }}{{ $event->max_participants > 0 ? '/' . $event->max_participants : '' }} người đăng ký
                                    </small>
                                </div>
                                <div class="d-flex gap-2">
                                    @if($isRegistered)
                                        <button class="btn btn-success btn-sm flex-fill" disabled>
                                            <i class="fas fa-check me-1"></i> Đã đăng ký
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="cancelRegistration({{ $event->id }}, this)">
                                            <i class="fas fa-times me-1"></i> Hủy
                                        </button>
                                    @elseif($isFull || $isDeadlinePassed)
                                        <button class="btn btn-secondary btn-sm flex-fill" disabled>
                                            <i class="fas fa-ban me-1"></i> Không thể đăng ký
                                        </button>
                                    @else
                                        <button class="btn btn-primary btn-sm flex-fill" onclick="registerEvent({{ $event->id }}, this)">
                                            <i class="fas fa-plus me-1"></i> Đăng ký
                                        </button>
                                    @endif
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
            <h4 class="mb-4 fw-bold">
                <i class="fas fa-check-circle text-success me-2"></i> Sự kiện đã đăng ký
            </h4>
            
            <div class="row" id="registered-events-list">
                @forelse($registeredEvents as $event)
                    @php
                        $hasImages = $event->images && $event->images->count() > 0;
                        $hasOldImage = !empty($event->image);
                    @endphp
                    <div class="col-md-6 mb-4 event-item" data-type="registered">
                        <div class="card border-0 shadow-sm h-100 event-card-hover">
                            @if($hasImages || $hasOldImage)
                                <div class="event-image-container">
                                    @if($hasImages)
                                        <img src="{{ $event->images->first()->image_url }}" 
                                             alt="{{ $event->title }}" 
                                             class="w-100 h-100" 
                                             style="object-fit: cover;">
                                    @elseif($hasOldImage)
                                        <img src="{{ asset('storage/' . $event->image) }}" 
                                             alt="{{ $event->title }}" 
                                             class="w-100 h-100" 
                                             style="object-fit: cover;">
                                    @endif
                                    <div class="event-status-overlay">
                                        <span class="badge bg-success">Đã đăng ký</span>
                                    </div>
                                </div>
                            @else
                                <div class="event-date-header bg-success text-white">
                                    <div class="date-day">{{ $event->start_time->format('d') }}</div>
                                    <div class="date-month-year">{{ strtoupper($event->start_time->format('M Y')) }}</div>
                                </div>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title mb-2">
                                    <a href="{{ route('student.events.show', $event->id) }}" class="text-decoration-none text-dark fw-bold">
                                        {{ $event->title }}
                                    </a>
                                </h5>
                                <p class="card-text text-muted small mb-2">
                                    <i class="fas fa-users me-1 text-teal"></i>{{ $event->club->name ?? 'N/A' }}
                                    @if($event->visibility === 'internal' && $event->club)
                                        <span class="badge bg-warning text-dark ms-2">
                                            <i class="fas fa-lock me-1"></i>Nội bộ {{ $event->club->name }}
                                        </span>
                                    @endif
                                </p>
                                <p class="card-text small mb-3">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 100) }}
                                </p>
                                <div class="mb-3">
                                    <div class="small text-muted mb-1">
                                        <i class="far fa-calendar me-1 text-teal"></i>
                                        <strong>Bắt đầu:</strong> {{ $event->start_time->format('d/m/Y H:i') }}
                                    </div>
                                    @if($event->location)
                                        <div class="small text-muted">
                                            <i class="fas fa-map-marker-alt me-1 text-teal"></i>{{ $event->location }}
                                        </div>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('student.events.show', $event->id) }}" class="btn btn-primary btn-sm flex-fill">
                                        <i class="fas fa-eye me-1"></i> Xem chi tiết
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm" onclick="cancelRegistration({{ $event->id }}, this)">
                                        <i class="fas fa-times me-1"></i> Hủy
                                    </button>
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
    .text-teal {
        color: #14b8a6 !important;
    }
    
    /* Event Card Styles */
    .event-card-hover {
        transition: all 0.3s ease;
        border-radius: 16px;
        overflow: hidden;
    }
    
    .event-card-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Event Image Container */
    .event-image-container {
        height: 200px;
        overflow: hidden;
        background: linear-gradient(135deg, #f0fdfa 0%, #e0f2f1 100%);
        position: relative;
    }
    
    .event-image-container img {
        transition: transform 0.3s ease;
    }
    
    .event-card-hover:hover .event-image-container img {
        transform: scale(1.05);
    }
    
    .event-status-overlay {
        position: absolute;
        top: 12px;
        right: 12px;
    }
    
    /* Event Date Header (when no image) */
    .event-date-header {
        height: 120px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
    }
    
    .event-date-header .date-day {
        font-size: 2.5rem;
        font-weight: bold;
        line-height: 1;
    }
    
    .event-date-header .date-month-year {
        font-size: 0.875rem;
        font-weight: 500;
        margin-top: 0.5rem;
        text-transform: uppercase;
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
    
    /* Filter buttons - smaller size */
    .btn-group[role="group"] .btn {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    
    /* Card body improvements */
    .event-card-hover .card-body {
        padding: 1.25rem;
    }
    
    .event-card-hover .card-title {
        font-size: 1.1rem;
        line-height: 1.4;
    }
    
    .event-card-hover .card-title a:hover {
        color: #14b8a6 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Filter buttons functionality
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('[data-filter]');
        const ongoingSection = document.getElementById('ongoing-events-section');
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
                    ongoingSection.style.display = 'block';
                    upcomingSection.style.display = 'block';
                    registeredSection.style.display = 'none';
                } else if (filter === 'ongoing') {
                    ongoingSection.style.display = 'block';
                    upcomingSection.style.display = 'none';
                    registeredSection.style.display = 'none';
                } else if (filter === 'upcoming') {
                    ongoingSection.style.display = 'none';
                    upcomingSection.style.display = 'block';
                    registeredSection.style.display = 'none';
                } else if (filter === 'registered') {
                    ongoingSection.style.display = 'none';
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
        
        // Sử dụng route helper từ Laravel để tạo URL đúng
        const registerUrl = '{{ route("student.events.register", ":id") }}'.replace(':id', eventId);
        
        fetch(registerUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            // Kiểm tra response status
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        const json = JSON.parse(text);
                        throw new Error(json.message || 'Có lỗi xảy ra khi đăng ký');
                    } catch (e) {
                        if (e instanceof Error && e.message) {
                            throw e;
                        }
                        throw new Error('Có lỗi xảy ra khi đăng ký. Mã lỗi: ' + response.status);
                    }
                });
            }
            // Kiểm tra content-type
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error('Server trả về dữ liệu không hợp lệ');
                });
            }
            return response.json();
        })
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
            showToast(error.message || 'Có lỗi xảy ra khi đăng ký', 'error');
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
        
        // Sử dụng route helper từ Laravel để tạo URL đúng
        const cancelUrl = '{{ route("student.events.cancel_registration", ":id") }}'.replace(':id', eventId);
        
        fetch(cancelUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            // Kiểm tra response status
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        const json = JSON.parse(text);
                        throw new Error(json.message || 'Có lỗi xảy ra khi hủy đăng ký');
                    } catch (e) {
                        if (e instanceof Error && e.message) {
                            throw e;
                        }
                        throw new Error('Có lỗi xảy ra khi hủy đăng ký. Mã lỗi: ' + response.status);
                    }
                });
            }
            // Kiểm tra content-type
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error('Server trả về dữ liệu không hợp lệ');
                });
            }
            return response.json();
        })
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
            showToast(error.message || 'Có lỗi xảy ra khi hủy đăng ký', 'error');
            // Re-enable button on error
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }
</script>
@endpush
@endsection
