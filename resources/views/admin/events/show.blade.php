@extends('admin.layouts.app')

@section('title', 'Chi tiết sự kiện')

@section('content')
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
        <h1>Chi tiết sự kiện</h1>
            <div class="d-flex gap-2">
            @if($event->status === 'cancelled')
                @php
                    $canRestore = $event->end_time && $event->end_time->isFuture();
                @endphp
                @if($canRestore)
                    <a href="{{ route('admin.events.restore-test', $event->id) }}" class="btn btn-success" onclick="return confirm('Bạn có chắc chắn muốn khôi phục sự kiện này?')">
                        <i class="fas fa-undo me-1"></i> Khôi phục sự kiện
                    </a>
                @else
                    <button type="button" class="btn btn-secondary" disabled title="Không thể khôi phục sự kiện đã kết thúc">
                        <i class="fas fa-ban me-1"></i> Không thể khôi phục
                    </button>
                @endif
            @else
                    @if($event->status === 'pending')
                        <form method="POST" action="{{ route('admin.events.approve', $event->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Bạn có chắc muốn duyệt sự kiện này?')">
                            <i class="fas fa-check me-1"></i> Duyệt sự kiện
                            </button>
                        </form>
                    @endif
                
                @php
                    $canCancel = in_array($event->status, ['pending', 'approved']) 
                        && $event->start_time 
                        && $event->start_time->isFuture() 
                        && $event->end_time 
                        && $event->end_time->isFuture();
                @endphp
                @if($canCancel)
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelEventModal">
                        <i class="fas fa-times me-1"></i> Hủy sự kiện
                        </button>
                    @endif
                
                @if($event->status !== 'completed')
                    <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-warning text-white">
                        <i class="fas fa-edit me-1"></i> Chỉnh sửa
                    </a>
                @endif
                @endif
            
                <a href="{{ route('admin.plans-schedule') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>
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
    <div class="col-12">
        <article class="content-card">
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
                                </div>
                    </div>

            <!-- Quick Info Cards -->
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
                                <p class="mb-2">
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
                            @if($event->max_participants)
                                <p class="mb-0">
                                    <strong>Số lượng tối đa:</strong><br>
                                    <span class="text-muted">{{ $event->max_participants }} người</span>
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
            @php
                $registrationsCount = \App\Models\EventRegistration::where('event_id', $event->id)->count();
                $availableSlots = $event->max_participants > 0 ? $event->max_participants - $registrationsCount : null;
                $isFull = $event->max_participants > 0 && $registrationsCount >= $event->max_participants;
                                                    @endphp
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="card-title text-teal mb-3">
                        <i class="fas fa-users me-2"></i>Thông tin đăng ký
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Số người đã đăng ký:</strong> {{ $registrationsCount }}
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách người đăng ký -->
            @if(isset($registrations))
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
            @if($event->main_organizer || $event->organizing_team || $event->co_organizers || ($event->contact_info && (is_array($event->contact_info) || is_string($event->contact_info))) || ($event->guests && (is_array($event->guests) || is_string($event->guests))))
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
                        <i class="fas fa-file me-2"></i>Tài liệu và File
                    </h4>
                    <div class="row">
                                        @if($event->proposal_file)
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="fas fa-file-pdf me-2 text-danger"></i>Kế hoạch chi tiết
                                        </h6>
                                            @php
                                                $filePath = storage_path('app/public/' . $event->proposal_file);
                                                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                                                $fileSizeFormatted = $fileSize > 0 ? ($fileSize >= 1048576 ? number_format($fileSize / 1048576, 2) . ' MB' : number_format($fileSize / 1024, 2) . ' KB') : 'N/A';
                                            @endphp
                                        <p class="text-muted small mb-2">Kích thước: {{ $fileSizeFormatted }}</p>
                                        <a href="{{ asset('storage/' . $event->proposal_file) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                            <i class="fas fa-download me-2"></i>Tải xuống
                                        </a>
                                </div>
                            </div>
                            </div>
                        @endif

                                        @if($event->poster_file)
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="fas fa-image me-2 text-primary"></i>Poster / Ấn phẩm
                                        </h6>
                                            @php
                                                $filePath = storage_path('app/public/' . $event->poster_file);
                                                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                                                $fileSizeFormatted = $fileSize > 0 ? ($fileSize >= 1048576 ? number_format($fileSize / 1048576, 2) . ' MB' : number_format($fileSize / 1024, 2) . ' KB') : 'N/A';
                                            @endphp
                                        <p class="text-muted small mb-2">Kích thước: {{ $fileSizeFormatted }}</p>
                                        <div class="d-flex gap-2">
                                            @if(in_array(strtolower(pathinfo($event->poster_file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                <a href="{{ asset('storage/' . $event->poster_file) }}" target="_blank" class="btn btn-sm btn-outline-info flex-fill" data-bs-toggle="modal" data-bs-target="#posterModal">
                                                    <i class="fas fa-eye me-2"></i>Xem
                                                </a>
                                            @endif
                                            <a href="{{ asset('storage/' . $event->poster_file) }}" target="_blank" class="btn btn-sm btn-outline-primary flex-fill">
                                                <i class="fas fa-download me-2"></i>Tải xuống
                                            </a>
                                        </div>
                                </div>
                            </div>
                            </div>
                        @endif

                                        @if($event->permit_file)
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="fas fa-file-alt me-2 text-warning"></i>Giấy phép / Công văn
                                        </h6>
                                            @php
                                                $filePath = storage_path('app/public/' . $event->permit_file);
                                                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                                                $fileSizeFormatted = $fileSize > 0 ? ($fileSize >= 1048576 ? number_format($fileSize / 1048576, 2) . ' MB' : number_format($fileSize / 1024, 2) . ' KB') : 'N/A';
                                            @endphp
                                        <p class="text-muted small mb-2">Kích thước: {{ $fileSizeFormatted }}</p>
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

                    <!-- Thông tin hệ thống -->
            <div class="mb-4">
                <h4 class="mb-3 text-teal">
                    <i class="fas fa-database me-2"></i>Thông tin hệ thống
                </h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-user me-2"></i>Người tạo
                                </h6>
                                <p class="mb-1">{{ $event->creator->name ?? 'N/A' }}</p>
                                            @if($event->creator)
                                                <small class="text-muted">{{ $event->creator->email ?? '' }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-calendar-plus me-2"></i>Ngày tạo
                                </h6>
                                <p class="mb-1">{{ $event->created_at ? $event->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                                            @if($event->created_at)
                                                <small class="text-muted">{{ $event->created_at->diffForHumans() }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-clock me-2"></i>Cập nhật lần cuối
                                </h6>
                                <p class="mb-1">{{ $event->updated_at ? $event->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>
                                            @if($event->updated_at)
                                                <small class="text-muted">{{ $event->updated_at->diffForHumans() }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-hashtag me-2"></i>Mã sự kiện
                                </h6>
                                <p class="mb-0"><code>#{{ $event->id }}</code></p>
                            </div>
                        </div>
                    </div>
                </div>
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
        </article>
    </div>
</div>

@push('styles')
<style>
    .content-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
        margin-bottom: 2rem;
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
@endpush

<!-- Modal hủy sự kiện -->
@php
    $canCancel = in_array($event->status, ['pending', 'approved']) 
        && $event->start_time 
        && $event->start_time->isFuture() 
        && $event->end_time 
        && $event->end_time->isFuture();
@endphp
@if($canCancel)
<div class="modal fade" id="cancelEventModal" tabindex="-1" aria-labelledby="cancelEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancelEventModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Hủy sự kiện
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.events.cancel', $event->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Cảnh báo:</strong> Bạn sắp hủy sự kiện "{{ $event->title }}". Hành động này không thể hoàn tác.
                    </div>
                    
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">
                            Lý do hủy sự kiện <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('cancellation_reason') is-invalid @enderror" 
                                  id="cancellation_reason" 
                                  name="cancellation_reason" 
                                  rows="4" 
                                  placeholder="Vui lòng nhập lý do hủy sự kiện..." 
                                  required>{{ old('cancellation_reason') }}</textarea>
                        @error('cancellation_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Lý do hủy sẽ được hiển thị cho tất cả người dùng và không thể chỉnh sửa sau khi lưu.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Đóng
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Xác nhận hủy sự kiện
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

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

@endsection
