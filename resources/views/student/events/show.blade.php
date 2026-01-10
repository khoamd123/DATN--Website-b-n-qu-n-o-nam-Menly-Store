@extends('layouts.student')

@section('title', $event->title . ' - UniClubs')

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
    <div class="col-12">
        <article class="content-card">
            <!-- Back Button -->
            <div class="mb-3">
                @if(isset($isClubMember) && $isClubMember)
                    <a href="{{ route('student.events.manage') }}" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại quản lý sự kiện
                    </a>
                @else
                    <a href="{{ route('student.events.index') }}" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách sự kiện
                    </a>
                @endif
            </div>

            <!-- Cancellation Info -->
            @if($event->status === 'cancelled' && $event->cancellation_reason)
                <div class="alert alert-danger mb-4">
                    <h5 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>Sự kiện đã bị hủy
                    </h5>
                    <p class="mb-0">
                        <strong>Lý do hủy:</strong> {{ $event->cancellation_reason }}
                    </p>
                    @if($event->cancelled_at)
                        <hr>
                        <p class="mb-0">
                            <small>Thời gian hủy: {{ $event->cancelled_at->format('d/m/Y H:i') }}</small>
                        </p>
                    @endif
                </div>
            @endif

            <!-- Event Header -->
            <div class="mb-4">
                <h1 class="mb-3">{{ $event->title }}</h1>
                
                <!-- Event Meta Information -->
                <div class="d-flex flex-wrap align-items-center text-muted mb-3">
                    <small class="me-3">
                        <i class="fas fa-users me-1"></i>{{ $event->club->name ?? 'UniClubs' }}
                    </small>
                    <small class="me-3">
                        <i class="fas fa-user me-1"></i>{{ $event->creator->name ?? 'Hệ thống' }}
                    </small>
                    <small class="me-3">
                        <i class="far fa-clock me-1"></i>{{ $event->created_at->format('d/m/Y H:i') }}
                    </small>
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
                    <span class="badge bg-{{ $statusColors[$event->status] ?? 'secondary' }} me-2">
                        {{ $statusLabels[$event->status] ?? ucfirst($event->status) }}
                    </span>
                    @if($event->status === 'approved' || $event->status === 'ongoing')
                        @if($event->start_time > now())
                            <span class="badge bg-info me-2">Sắp diễn ra</span>
                        @elseif($event->end_time < now())
                            <span class="badge bg-secondary me-2">Đã kết thúc</span>
                        @else
                            <span class="badge bg-success me-2">Đang diễn ra</span>
                        @endif
                    @endif
                    @if(isset($activeViewersCount) && $activeViewersCount > 0)
                        <small class="me-3">
                            <i class="fas fa-eye me-1 text-primary"></i>
                            <span id="viewersCount">{{ $activeViewersCount }}</span> người đang xem
                        </small>
                    @endif
                </div>

                <!-- Viewers Status Card -->
                @if(isset($activeViewersCount) && $activeViewersCount > 0)
                    <div class="mb-3">
                        <div class="card border-0 shadow-sm" id="viewersCard">
                            <div class="card-body py-2">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <small class="text-muted">
                                            <i class="fas fa-users me-1"></i>
                                            <strong id="viewersCountText">{{ $activeViewersCount }}</strong> người đang xem sự kiện này
                                        </small>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#viewersList" aria-expanded="false">
                                        <i class="fas fa-chevron-down"></i> Xem danh sách
                                    </button>
                                </div>
                                <div class="collapse mt-2" id="viewersList">
                                    <div id="viewersListContent" class="small">
                                        <div class="text-center text-muted py-2">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Đang tải...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Event Images Gallery -->
                @php
                    $allImages = collect();
                    // Thêm ảnh từ event_images table
                    if ($event->images && $event->images->count() > 0) {
                        foreach ($event->images as $img) {
                            $allImages->push([
                                'url' => $img->image_url,
                                'alt' => $img->alt_text ?? $event->title,
                                'type' => 'gallery'
                            ]);
                        }
                    }
                    // Thêm ảnh cũ từ image field nếu có và chưa có trong gallery
                    if ($event->image) {
                        $oldImageUrl = asset('storage/' . $event->image);
                        $exists = $allImages->contains(function($img) use ($oldImageUrl) {
                            return $img['url'] === $oldImageUrl;
                        });
                        if (!$exists) {
                            $allImages->prepend([
                                'url' => $oldImageUrl,
                                'alt' => $event->title . ' - Banner',
                                'type' => 'banner'
                            ]);
                        }
                    }
                @endphp

                @if($allImages->count() > 0)
                    <div class="mb-4">
                        @if($allImages->count() == 1)
                            <!-- Single Image -->
                            <div class="event-image-single">
                                <img src="{{ $allImages->first()['url'] }}" 
                                     alt="{{ $allImages->first()['alt'] }}" 
                                     class="img-fluid rounded shadow-sm" 
                                     style="max-height: 500px; width: 100%; object-fit: cover; cursor: pointer;"
                                     onclick="openImageModal('{{ $allImages->first()['url'] }}', '{{ $allImages->first()['alt'] }}')">
                            </div>
                        @else
                            <!-- Image Carousel -->
                            <div id="eventImageCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach($allImages as $index => $img)
                                        <button type="button" data-bs-target="#eventImageCarousel" data-bs-slide-to="{{ $index }}" 
                                                class="{{ $index === 0 ? 'active' : '' }}" 
                                                aria-current="{{ $index === 0 ? 'true' : 'false' }}" 
                                                aria-label="Slide {{ $index + 1 }}"></button>
                                    @endforeach
                                </div>
                                <div class="carousel-inner rounded shadow-sm">
                                    @foreach($allImages as $index => $img)
                                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                            <img src="{{ $img['url'] }}" 
                                                 alt="{{ $img['alt'] }}" 
                                                 class="d-block w-100" 
                                                 style="max-height: 500px; object-fit: cover; cursor: pointer;"
                                                 onclick="openImageModal('{{ $img['url'] }}', '{{ $img['alt'] }}')">
                                            @if($img['type'] === 'banner')
                                                <div class="carousel-caption d-none d-md-block">
                                                    <span class="badge bg-primary">Banner sự kiện</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#eventImageCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#eventImageCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                            
                            <!-- Image Thumbnails Grid -->
                            <div class="mt-3">
                                <h6 class="mb-2 text-teal">
                                    <i class="fas fa-images me-2"></i>Hình ảnh sự kiện ({{ $allImages->count() }})
                                </h6>
                                <div class="row g-2">
                                    @foreach($allImages as $index => $img)
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <div class="image-thumbnail-wrapper position-relative">
                                                <img src="{{ $img['url'] }}" 
                                                     alt="{{ $img['alt'] }}" 
                                                     class="img-fluid rounded shadow-sm" 
                                                     style="width: 100%; height: 120px; object-fit: cover; cursor: pointer; transition: transform 0.2s;"
                                                     onclick="openImageModal('{{ $img['url'] }}', '{{ $img['alt'] }}')"
                                                     onmouseover="this.style.transform='scale(1.05)'"
                                                     onmouseout="this.style.transform='scale(1)'">
                                                @if($img['type'] === 'banner')
                                                    <span class="badge bg-primary position-absolute top-0 start-0 m-1" style="font-size: 0.7rem;">Banner</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Event Details Grid -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title text-teal mb-3">
                                <i class="fas fa-calendar-alt me-2"></i>Thời gian
                            </h6>
                            <p class="mb-2">
                                <strong>Bắt đầu:</strong><br>
                                <span class="text-muted">{{ $event->start_time->format('d/m/Y H:i') }}</span>
                            </p>
                            <p class="mb-2">
                                <strong>Kết thúc:</strong><br>
                                <span class="text-muted">{{ $event->end_time->format('d/m/Y H:i') }}</span>
                            </p>
                            @if($event->registration_deadline)
                                <p class="mb-0">
                                    <strong>Hạn đăng ký:</strong><br>
                                    <span class="text-muted">{{ $event->registration_deadline->format('d/m/Y H:i') }}</span>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title text-teal mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i>Địa điểm & Hình thức
                            </h6>
                            @if($event->location)
                                <p class="mb-2">
                                    <strong>Địa điểm:</strong><br>
                                    <span class="text-muted">{{ $event->location }}</span>
                                </p>
                            @endif
                            @if($event->mode)
                                <p class="mb-0">
                                    <strong>Hình thức:</strong><br>
                                    <span class="text-muted">
                                    @if($event->mode === 'online')
                                            <i class="fas fa-laptop me-1"></i>Trực tuyến
                                    @elseif($event->mode === 'offline')
                                            <i class="fas fa-map-pin me-1"></i>Trực tiếp
                                    @elseif($event->mode === 'hybrid')
                                            <i class="fas fa-network-wired me-1"></i>Kết hợp
                                    @else
                                        {{ ucfirst($event->mode) }}
                                    @endif
                                    </span>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title text-teal mb-3">
                                <i class="fas fa-eye me-2"></i>Chế độ hiển thị
                            </h6>
                            @php
                                $visibility = $event->visibility ?? 'public';
                                $visibilityLabel = $visibility === 'internal' ? 'Chỉ nội bộ CLB' : 'Công khai';
                                $visibilityIcon = $visibility === 'internal' ? 'fa-lock' : 'fa-globe';
                                $visibilityColor = $visibility === 'internal' ? 'warning' : 'info';
                            @endphp
                            <p class="mb-0">
                                <span class="badge bg-{{ $visibilityColor }} px-3 py-2">
                                    <i class="fas {{ $visibilityIcon }} me-1"></i>{{ $visibilityLabel }}
                                </span>
                            </p>
                            <small class="text-muted d-block mt-2">
                                @if($visibility === 'internal')
                                    Chỉ thành viên của {{ $event->club->name ?? 'CLB' }} mới có thể xem sự kiện này.
                                @else
                                    Tất cả mọi người đều có thể xem sự kiện này.
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registration Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="card-title text-teal mb-3">
                        <i class="fas fa-users me-2"></i>Thông tin đăng ký
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Số người đã đăng ký:</strong> {{ $registrationCount }}
                                @if($event->max_participants > 0)
                                    / {{ $event->max_participants }}
                                @endif
                            </p>
                            @if($event->max_participants > 0)
                                <p class="mb-0">
                                    <strong>Chỗ còn lại:</strong> 
                                    @if($availableSlots > 0)
                                        <span class="text-success">{{ $availableSlots }} chỗ</span>
                                    @else
                                        <span class="text-danger">Đã đầy</span>
                                    @endif
                                </p>
                            @else
                                <p class="mb-0 text-info">Không giới hạn số lượng</p>
                            @endif
                        </div>
                        <div class="col-md-6 text-end">
                            @if($isRegistered)
                                <span class="badge bg-success fs-6 px-3 py-2 mb-2">
                                    <i class="fas fa-check me-1"></i>Bạn đã đăng ký
                                </span>
                            @elseif($isFull)
                                <span class="badge bg-danger fs-6 px-3 py-2 mb-2">
                                    <i class="fas fa-times me-1"></i>Đã đầy
                                </span>
                            @elseif($isDeadlinePassed)
                                <span class="badge bg-secondary fs-6 px-3 py-2 mb-2">
                                    <i class="fas fa-calendar-times me-1"></i>Hết hạn đăng ký
                                </span>
                            @elseif($event->status === 'cancelled')
                                <span class="badge bg-danger fs-6 px-3 py-2 mb-2">
                                    <i class="fas fa-ban me-1"></i>Sự kiện đã bị hủy
                                </span>
                            @else
                                <button class="btn btn-primary btn-lg" onclick="registerEvent({{ $event->id }}, this)">
                                    <i class="fas fa-plus me-1"></i> Đăng ký tham gia
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách người đăng ký (chỉ cán bộ CLB và admin mới xem được) -->
            @if(isset($canViewRegistrations) && $canViewRegistrations && isset($registrations))
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="card-title text-teal mb-0">
                                <i class="fas fa-list me-2"></i>Danh sách người đăng ký
                                <span class="badge bg-primary ms-2">{{ $registrations->count() }}</span>
                            </h6>
                            @if($registrations->count() > 0)
                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#registrationsList" aria-expanded="true">
                                    <i class="fas fa-chevron-down"></i> Xem danh sách
                                </button>
                            @endif
                        </div>
                        
                        @if($registrations->count() > 0)
                            <div class="collapse show" id="registrationsList">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">STT</th>
                                                <th>Họ và tên</th>
                                                <th>Email</th>
                                                <th>Mã sinh viên</th>
                                                <th>Thời gian đăng ký</th>
                                                <th>Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($registrations as $index => $registration)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($registration->user->avatar)
                                                                <img src="{{ asset('storage/' . $registration->user->avatar) }}" 
                                                                     alt="{{ $registration->user->name }}" 
                                                                     class="rounded-circle me-2" 
                                                                     style="width: 32px; height: 32px; object-fit: cover;">
                                                            @else
                                                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" 
                                                                     style="width: 32px; height: 32px; font-size: 0.875rem;">
                                                                    {{ strtoupper(substr($registration->user->name, 0, 1)) }}
                                                                </div>
                                                            @endif
                                                            <strong>{{ $registration->user->name }}</strong>
                                                        </div>
                                                    </td>
                                                    <td>{{ $registration->user->email ?? 'N/A' }}</td>
                                                    <td>{{ $registration->user->student_id ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($registration->joined_at)
                                                            {{ $registration->joined_at->format('d/m/Y H:i') }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusColors = [
                                                                'registered' => 'success',
                                                                'pending' => 'warning',
                                                                'approved' => 'info',
                                                                'rejected' => 'danger',
                                                                'canceled' => 'secondary'
                                                            ];
                                                            $statusLabels = [
                                                                'registered' => 'Đã đăng ký',
                                                                'pending' => 'Chờ duyệt',
                                                                'approved' => 'Đã duyệt',
                                                                'rejected' => 'Từ chối',
                                                                'canceled' => 'Đã hủy'
                                                            ];
                                                        @endphp
                                                        <span class="badge bg-{{ $statusColors[$registration->status] ?? 'secondary' }}">
                                                            {{ $statusLabels[$registration->status] ?? ucfirst($registration->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <p class="mb-0">Chưa có người đăng ký</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Event Description -->
            @if($event->description)
                <div class="mb-4">
                    <h4 class="mb-3 text-teal">
                        <i class="fas fa-align-left me-2"></i>Mô tả sự kiện
                    </h4>
                    <div class="event-description" style="line-height: 1.8; font-size: 1.05rem;">
                        {!! $event->description !!}
                    </div>
                </div>
            @endif

            <!-- Additional Information -->
            @if($event->main_organizer || $event->organizing_team || $event->co_organizers || ($event->contact_info && is_array($event->contact_info)) || ($event->guests && is_array($event->guests)))
                <div class="mb-4">
                    <h4 class="mb-3 text-teal">
                        <i class="fas fa-info-circle me-2"></i>Thông tin bổ sung
                    </h4>
                    <div class="row">
                @if($event->main_organizer)
                    <div class="col-md-6 mb-3">
                                <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="card-title text-teal mb-3">
                                    <i class="fas fa-user-tie me-2"></i>Ban tổ chức chính
                                </h6>
                                        <p class="mb-0"><strong>{{ $event->main_organizer }}</strong></p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($event->organizing_team)
                    <div class="col-md-6 mb-3">
                                <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="card-title text-teal mb-3">
                                    <i class="fas fa-users-cog me-2"></i>Đội ngũ tổ chức
                                </h6>
                                        <p class="mb-0" style="white-space: pre-line;">{{ $event->organizing_team }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($event->co_organizers)
                    <div class="col-md-6 mb-3">
                                <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="card-title text-teal mb-3">
                                    <i class="fas fa-handshake me-2"></i>Đồng tổ chức
                                </h6>
                                        <p class="mb-0" style="white-space: pre-line;">{{ $event->co_organizers }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                        @php
                            $contact = null;
                            if ($event->contact_info) {
                                $contact = is_array($event->contact_info) ? $event->contact_info : json_decode($event->contact_info, true);
                            }
                        @endphp
                        @if($contact && (isset($contact['phone']) || isset($contact['email'])))
                    <div class="col-md-6 mb-3">
                                <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="card-title text-teal mb-3">
                                    <i class="fas fa-phone me-2"></i>Thông tin liên hệ
                                </h6>
                                        @if(isset($contact['phone']))
                                    <p class="mb-2">
                                                <i class="fas fa-phone text-primary me-2"></i>
                                                <a href="tel:{{ $contact['phone'] }}" class="text-decoration-none">{{ $contact['phone'] }}</a>
                                    </p>
                                @endif
                                        @if(isset($contact['email']))
                                    <p class="mb-0">
                                                <i class="fas fa-envelope text-primary me-2"></i>
                                                <a href="mailto:{{ $contact['email'] }}" class="text-decoration-none">{{ $contact['email'] }}</a>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                        @php
                            $guestData = null;
                            if ($event->guests) {
                                $guestData = is_array($event->guests) ? $event->guests : (is_string($event->guests) ? json_decode($event->guests, true) : null);
                            }
                        @endphp
                        @if($guestData && (isset($guestData['types']) || isset($guestData['other_info'])))
                    <div class="col-md-12 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title text-teal mb-3">
                                    <i class="fas fa-user-friends me-2"></i>Khách mời
                                </h6>
                                        @if(isset($guestData['types']) && is_array($guestData['types']) && count($guestData['types']) > 0)
                                            <p class="mb-3">
                                                <strong>Loại khách mời:</strong><br>
                                                @foreach($guestData['types'] as $type)
                                                    <span class="badge bg-info me-2 mb-2 px-3 py-2">
                                                @if($type === 'lecturer')
                                                            <i class="fas fa-chalkboard-teacher me-1"></i>Giảng viên
                                                @elseif($type === 'student')
                                                            <i class="fas fa-user-graduate me-1"></i>Sinh viên
                                                @elseif($type === 'sponsor')
                                                            <i class="fas fa-hand-holding-usd me-1"></i>Nhà tài trợ
                                                @else
                                                            <i class="fas fa-users me-1"></i>{{ ucfirst($type) }}
                                                @endif
                                            </span>
                                        @endforeach
                                    </p>
                                @endif
                                        @if(isset($guestData['other_info']) && !empty(trim($guestData['other_info'])))
                                            <div class="mt-3 pt-3 border-top">
                                                <strong>Thông tin chi tiết:</strong>
                                                <p class="mb-0 mt-2" style="white-space: pre-line;">{{ $guestData['other_info'] }}</p>
                                            </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
                </div>
            @endif

            <!-- Tài liệu và File -->
            @if($event->proposal_file || $event->poster_file || $event->permit_file)
                <div class="mb-4">
                    <h4 class="mb-3 text-teal">
                        <i class="fas fa-file-alt me-2"></i>Tài liệu và File
                    </h4>
                    <div class="row">
                        @if($event->proposal_file)
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="fas fa-file-pdf me-2 text-danger"></i>Kế hoạch chi tiết
                                        </h6>
                                        @php
                                            $filePath = storage_path('app/public/' . $event->proposal_file);
                                            $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                                            $fileSizeFormatted = $fileSize > 0 ? ($fileSize >= 1048576 ? number_format($fileSize / 1048576, 2) . ' MB' : number_format($fileSize / 1024, 2) . ' KB') : 'N/A';
                                        @endphp
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-info-circle me-1"></i>Kích thước: {{ $fileSizeFormatted }}
                                        </p>
                                        <a href="{{ asset('storage/' . $event->proposal_file) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                            <i class="fas fa-download me-2"></i>Tải xuống
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($event->poster_file)
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="fas fa-image me-2 text-primary"></i>Poster / Ấn phẩm
                                        </h6>
                                        @php
                                            $filePath = storage_path('app/public/' . $event->poster_file);
                                            $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                                            $fileSizeFormatted = $fileSize > 0 ? ($fileSize >= 1048576 ? number_format($fileSize / 1048576, 2) . ' MB' : number_format($fileSize / 1024, 2) . ' KB') : 'N/A';
                                            $isImage = in_array(strtolower(pathinfo($event->poster_file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        @endphp
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-info-circle me-1"></i>Kích thước: {{ $fileSizeFormatted }}
                                        </p>
                                        <div class="d-flex gap-2">
                                            @if($isImage)
                                                <a href="{{ asset('storage/' . $event->poster_file) }}" target="_blank" class="btn btn-sm btn-outline-info flex-fill" data-bs-toggle="modal" data-bs-target="#posterModal">
                                                    <i class="fas fa-eye me-2"></i>Xem
                                                </a>
                                            @endif
                                            <a href="{{ asset('storage/' . $event->poster_file) }}" target="_blank" class="btn btn-sm btn-outline-primary {{ $isImage ? 'flex-fill' : 'w-100' }}">
                                                <i class="fas fa-download me-2"></i>Tải xuống
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($event->permit_file)
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="fas fa-file-alt me-2 text-warning"></i>Giấy phép / Công văn
                                        </h6>
                                        @php
                                            $filePath = storage_path('app/public/' . $event->permit_file);
                                            $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                                            $fileSizeFormatted = $fileSize > 0 ? ($fileSize >= 1048576 ? number_format($fileSize / 1048576, 2) . ' MB' : number_format($fileSize / 1024, 2) . ' KB') : 'N/A';
                                        @endphp
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-info-circle me-1"></i>Kích thước: {{ $fileSizeFormatted }}
                                        </p>
                                        <a href="{{ asset('storage/' . $event->permit_file) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                            <i class="fas fa-download me-2"></i>Tải xuống
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="d-flex gap-2 mb-4">
                @if($canCancelRegistration)
                    <button class="btn btn-outline-danger" onclick="cancelRegistration({{ $event->id }}, this)">
                        <i class="fas fa-times me-1"></i> Hủy đăng ký
                    </button>
                @elseif($isRegistered)
                    <button class="btn btn-success" disabled>
                        <i class="fas fa-check me-1"></i> Đã đăng ký
                    </button>
                @elseif(!$isFull && !$isDeadlinePassed && $event->status !== 'cancelled')
                    <button class="btn btn-primary" onclick="registerEvent({{ $event->id }}, this)">
                        <i class="fas fa-plus me-1"></i> Đăng ký tham gia
                    </button>
                @endif
                <a href="{{ route('student.events.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
                </a>
            </div>
        </article>
    </div>
</div>

@push('styles')
<style>
    /* Toast Notification Styles */
    .toast {
        min-width: 300px;
        max-width: 400px;
    }
    
    .toast-header {
        font-weight: 600;
    }
    
    .toast-body {
        font-size: 0.95rem;
    }
    
    .text-teal {
        color: #14b8a6 !important;
    }
    
    .event-description {
        color: #333;
    }
    
    .event-description img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1rem 0;
    }
    
    .card {
        transition: transform 0.2s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .image-thumbnail-wrapper {
        overflow: hidden;
        border-radius: 8px;
    }
    
    .image-thumbnail-wrapper img {
        transition: transform 0.3s ease;
    }
    
    .image-thumbnail-wrapper:hover img {
        transform: scale(1.1);
    }
    
    /* Image Modal */
    .image-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        overflow: auto;
    }
    
    .image-modal-content {
        margin: auto;
        display: block;
        width: 90%;
        max-width: 1200px;
        margin-top: 50px;
        animation: zoom 0.3s;
    }
    
    .image-modal-content img {
        width: 100%;
        height: auto;
        border-radius: 8px;
    }
    
    @keyframes zoom {
        from {transform: scale(0.5)}
        to {transform: scale(1)}
    }
    
    .image-modal-close {
        position: absolute;
        top: 20px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        z-index: 10000;
    }
    
    .image-modal-close:hover {
        color: #fff;
    }
</style>
@endpush

@push('scripts')
<script>
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
        // Log để debug
        console.log('Register event called with eventId:', eventId, 'Type:', typeof eventId);
        
        // Đảm bảo eventId là số
        eventId = parseInt(eventId);
        if (isNaN(eventId) || eventId <= 0) {
            console.error('Invalid eventId:', eventId);
            showToast('ID sự kiện không hợp lệ', 'error');
            return;
        }
        
        // Disable button to prevent multiple clicks
        const button = buttonElement;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang xử lý...';
        
        // Sử dụng route helper từ Laravel để tạo URL đúng
        const registerUrl = '{{ route("student.events.register", ":id") }}'.replace(':id', eventId);
        console.log('Register URL:', registerUrl);
        
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
                button.disabled = false;
                button.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(error.message || 'Có lỗi xảy ra khi đăng ký', 'error');
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }
    
    // Cancel event registration function
    function cancelRegistration(eventId, buttonElement) {
        if (!confirm('Bạn có chắc chắn muốn hủy đăng ký sự kiện này?')) {
            return;
        }
        
        // Disable button to prevent multiple clicks
        const button = buttonElement;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang xử lý...';
        
        fetch(`/student/events/${eventId}/cancel-registration`, {
            method: 'DELETE',
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
                button.disabled = false;
                button.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Có lỗi xảy ra khi hủy đăng ký', 'error');
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }
    
    // Image Modal Functions
    function openImageModal(imageUrl, imageAlt) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        const modalCaption = document.getElementById('modalCaption');
        
        modal.style.display = 'block';
        modalImg.src = imageUrl;
        modalCaption.textContent = imageAlt;
        
        // Prevent body scroll when modal is open
        document.body.style.overflow = 'hidden';
    }
    
    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Viewers tracking functionality
    @if(isset($activeViewersCount) && $activeViewersCount > 0)
    const eventId = {{ $event->id }};
    let viewersUpdateInterval;
    let activityPingInterval;

    // Update viewer activity (ping server)
    function pingViewerActivity() {
        fetch(`/student/events/${eventId}/viewer-activity`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        }).catch(err => console.log('Ping failed:', err));
    }

    // Fetch and update viewers list
    function updateViewersList() {
        fetch(`/student/events/${eventId}/viewers`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update count
                    const countElements = document.querySelectorAll('#viewersCount, #viewersCountText');
                    countElements.forEach(el => {
                        if (el) el.textContent = data.count;
                    });

                    // Update list
                    const listContent = document.getElementById('viewersListContent');
                    if (listContent) {
                        if (data.viewers.length === 0) {
                            listContent.innerHTML = '<div class="text-center text-muted py-2">Không có ai đang xem</div>';
                        } else {
                            let html = '<div class="d-flex flex-wrap gap-2">';
                            data.viewers.forEach(viewer => {
                                const onlineBadge = viewer.is_online 
                                    ? '<span class="badge bg-success ms-1"><i class="fas fa-circle" style="font-size: 0.5rem;"></i></span>' 
                                    : '';
                                html += `
                                    <div class="d-flex align-items-center p-2 border rounded" style="background: #f8f9fa;">
                                        <img src="${viewer.avatar}" alt="${viewer.name}" 
                                             class="rounded-circle me-2" 
                                             style="width: 32px; height: 32px; object-fit: cover;">
                                        <div>
                                            <div class="fw-bold small">${viewer.name}${onlineBadge}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">${viewer.last_activity}</div>
                                        </div>
                                    </div>
                                `;
                            });
                            html += '</div>';
                            listContent.innerHTML = html;
                        }
                    }

                    // Show/hide viewers card
                    const viewersCard = document.getElementById('viewersCard');
                    if (viewersCard) {
                        if (data.count === 0) {
                            viewersCard.style.display = 'none';
                        } else {
                            viewersCard.style.display = 'block';
                        }
                    }
                }
            })
            .catch(err => console.log('Update viewers failed:', err));
    }

    // Initialize viewers tracking
    document.addEventListener('DOMContentLoaded', function() {
        // Ping immediately
        pingViewerActivity();

        // Ping every 30 seconds
        activityPingInterval = setInterval(pingViewerActivity, 30000);

        // Update viewers list immediately
        updateViewersList();

        // Update viewers list every 10 seconds
        viewersUpdateInterval = setInterval(updateViewersList, 10000);

        // Ping when user becomes active (visibility change)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                pingViewerActivity();
                updateViewersList();
            }
        });

        // Ping before page unload
        window.addEventListener('beforeunload', function() {
            // Send a final ping (navigator.sendBeacon if available)
            if (navigator.sendBeacon) {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
                navigator.sendBeacon(`/student/events/${eventId}/viewer-activity`, formData);
            }
        });
    });
    @endif
    
    // Close modal when clicking outside the image
    window.onclick = function(event) {
        const modal = document.getElementById('imageModal');
        if (event.target === modal) {
            closeImageModal();
        }
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeImageModal();
        }
    });
</script>

<!-- Image Modal -->
<div id="imageModal" class="image-modal">
    <span class="image-modal-close" onclick="closeImageModal()">&times;</span>
    <div class="image-modal-content">
        <img id="modalImage" src="" alt="">
        <div class="text-center text-white mt-3">
            <p id="modalCaption" class="mb-0"></p>
        </div>
    </div>
</div>

<!-- Modal xem poster -->
@if($event->poster_file && in_array(strtolower(pathinfo($event->poster_file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
<div class="modal fade" id="posterModal" tabindex="-1" aria-labelledby="posterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="posterModalLabel">
                    <i class="fas fa-image me-2"></i>Poster sự kiện
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('storage/' . $event->poster_file) }}" alt="Poster sự kiện" class="img-fluid rounded">
            </div>
            <div class="modal-footer">
                <a href="{{ asset('storage/' . $event->poster_file) }}" download class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Tải xuống
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endif
@endpush
@endsection

