@extends('admin.layouts.app')

@section('title', 'Chi tiết sự kiện')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-calendar-alt"></i> Chi tiết sự kiện</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.plans-schedule') }}">Kế hoạch</a></li>
                        <li class="breadcrumb-item active">Chi tiết sự kiện</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                @if($event->status !== 'cancelled')
                    <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> Chỉnh sửa
                    </a>
                    @if($event->status === 'pending')
                        <form method="POST" action="{{ route('admin.events.approve', $event->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Bạn có chắc muốn duyệt sự kiện này?')">
                                <i class="fas fa-check me-1"></i> Duyệt
                            </button>
                        </form>
                    @endif
                    @if(in_array($event->status, ['pending', 'approved']))
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelEventModal">
                            <i class="fas fa-times me-1"></i> Hủy
                        </button>
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
        <!-- Thông tin sự kiện chính -->
        <div class="col-lg-8">
            <!-- Header sự kiện -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $event->title }}</h4>
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
                        <span class="badge bg-{{ $statusColors[$event->status] ?? 'secondary' }} fs-6 px-3 py-2">
                                {{ $statusLabels[$event->status] ?? ucfirst($event->status) }}
                            </span>
                        @if($event->status === 'cancelled' && $event->cancellation_reason)
                                <div class="mt-2 p-2 bg-light rounded">
                                    <small class="text-danger d-block">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        <strong>Lý do hủy:</strong> 
                                    </small>
                                    <small class="text-dark">
                                    {{ $event->cancellation_reason }}
                                    </small>
                                </div>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($event->description)
                        <div class="event-description mb-4">
                            <div class="description-header">
                                <div class="description-icon">
                                    <i class="fas fa-align-left"></i>
                                </div>
                                <h5 class="description-title">Mô tả sự kiện</h5>
                            </div>
                            <div class="description-content">
                                <div class="description-text">
                                    {!! $event->description !!}
                                </div>
                                <div class="description-footer">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Thông tin chi tiết về sự kiện
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    
                    <!-- Lý do hủy sự kiện -->
                    @if($event->status === 'cancelled')
                        <div class="cancellation-info mb-4">
                            <div class="cancellation-header">
                                <div class="cancellation-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <h5 class="cancellation-title">Lý do hủy sự kiện</h5>
                            </div>
                            <div class="cancellation-content">
                                <div class="cancellation-text">
                                    {{ $event->cancellation_reason ?? 'Sự kiện đã bị hủy bởi quản trị viên. Vui lòng liên hệ để biết thêm thông tin chi tiết.' }}
                                </div>
                                <div class="cancellation-footer">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Hủy lúc: 
                                        @if(isset($event->cancelled_at) && $event->cancelled_at)
                                            {{ $event->cancelled_at->format('d/m/Y H:i') }}
                                        @else
                                            {{ $event->updated_at->format('d/m/Y H:i') }}
                                        @endif
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        Bởi: {{ $event->creator->name ?? 'Admin' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Thông tin Câu lạc bộ -->
                    @if($event->club)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Câu lạc bộ tổ chức</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="club-avatar me-3">
                                    @if($event->club->logo)
                                        <img src="{{ asset('storage/' . $event->club->logo) }}" alt="{{ $event->club->name }}" class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                            {{ substr($event->club->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <h5 class="mb-1">{{ $event->club->name }}</h5>
                                    @if($event->club->description)
                                        <p class="text-muted mb-0 small">{{ Str::limit($event->club->description, 100) }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Thông tin thời gian -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Thông tin thời gian</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="info-card">
                                        <div class="info-icon">
                                            <i class="fas fa-play"></i>
                                        </div>
                                        <div class="info-content">
                                            <h6 class="info-label">Thời gian bắt đầu</h6>
                                            <p class="info-value mb-1">{{ $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('d/m/Y H:i') : 'N/A' }}</p>
                                            @if($event->start_time)
                                                <small class="text-muted">
                                                    @php
                                                        $startTime = \Carbon\Carbon::parse($event->start_time);
                                                        $now = \Carbon\Carbon::now();
                                                    @endphp
                                                    @if($startTime->isPast())
                                                        <i class="fas fa-check-circle text-success me-1"></i>Đã bắt đầu
                                                    @else
                                                        <i class="fas fa-clock text-warning me-1"></i>Còn {{ $startTime->diffForHumans() }}
                                                    @endif
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="info-card">
                                        <div class="info-icon">
                                            <i class="fas fa-stop"></i>
                                        </div>
                                        <div class="info-content">
                                            <h6 class="info-label">Thời gian kết thúc</h6>
                                            <p class="info-value mb-1">{{ $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('d/m/Y H:i') : 'N/A' }}</p>
                                            @if($event->end_time)
                                                <small class="text-muted">
                                                    @php
                                                        $endTime = \Carbon\Carbon::parse($event->end_time);
                                                    @endphp
                                                    @if($endTime->isPast())
                                                        <i class="fas fa-check-circle text-success me-1"></i>Đã kết thúc
                                                    @else
                                                        <i class="fas fa-clock text-info me-1"></i>Còn {{ $endTime->diffForHumans() }}
                                                    @endif
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="info-card">
                                        <div class="info-icon">
                                            <i class="fas fa-calendar-times"></i>
                                        </div>
                                        <div class="info-content">
                                            <h6 class="info-label">Hạn chót đăng ký tham gia</h6>
                                            @if($event->registration_deadline)
                                                <p class="info-value mb-1">{{ $event->registration_deadline->format('d/m/Y H:i') }}</p>
                                                @php
                                                    $deadline = \Carbon\Carbon::parse($event->registration_deadline);
                                                @endphp
                                                <small class="text-muted">
                                                    @if($deadline->isPast())
                                                        <i class="fas fa-exclamation-triangle text-danger me-1"></i>Đã hết hạn đăng ký
                                                    @else
                                                        <i class="fas fa-clock text-warning me-1"></i>Còn {{ $deadline->diffForHumans() }} để đăng ký
                                                    @endif
                                                </small>
                                            @else
                                                <p class="info-value text-muted fst-italic">Chưa có thông tin</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin chi tiết -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Thông tin chi tiết</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="info-card">
                                        <div class="info-icon">
                                            <i class="fas fa-{{ $event->mode === 'offline' ? 'map-marker-alt' : ($event->mode === 'online' ? 'video' : 'users') }}"></i>
                                        </div>
                                        <div class="info-content">
                                            <h6 class="info-label">Hình thức tổ chức</h6>
                                            @php
                                                $modeLabels = [
                                                    'offline' => 'Tại chỗ',
                                                    'online' => 'Trực tuyến',
                                                    'hybrid' => 'Kết hợp'
                                                ];
                                            @endphp
                                            <p class="info-value">{{ $modeLabels[$event->mode] ?? ucfirst($event->mode) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="info-card">
                                        <div class="info-icon">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div class="info-content">
                                            <h6 class="info-label">Địa điểm</h6>
                                            <p class="info-value">{{ $event->location ?? 'Chưa xác định' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="info-card">
                                        <div class="info-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="info-content">
                                            <h6 class="info-label">Số lượng tối đa</h6>
                                            <p class="info-value">{{ $event->max_participants ?? 'Không giới hạn' }} người</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="info-card">
                                        <div class="info-icon">
                                            <i class="fas fa-hashtag"></i>
                                        </div>
                                        <div class="info-content">
                                            <h6 class="info-label">Mã sự kiện</h6>
                                            <p class="info-value">#{{ $event->id }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin tổ chức -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Thông tin tổ chức</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6><i class="fas fa-user-tie me-2"></i>Người phụ trách chính <span class="text-muted">(Chủ nhiệm / Trưởng ban tổ chức)</span></h6>
                                <p class="ms-4">
                                    @if($event->main_organizer)
                                        {{ $event->main_organizer }}
                                    @else
                                        <span class="text-muted fst-italic">Chưa có thông tin</span>
                                    @endif
                                </p>
                            </div>

                            <div class="mb-3">
                                <h6><i class="fas fa-user-friends me-2"></i>Ban tổ chức / Đội ngũ thực hiện</h6>
                                <p class="ms-4" style="white-space: pre-line;">
                                    @if($event->organizing_team)
                                        {{ $event->organizing_team }}
                                    @else
                                        <span class="text-muted fst-italic">Chưa có thông tin</span>
                                    @endif
                                </p>
                            </div>

                            <div class="mb-3">
                                <h6><i class="fas fa-handshake me-2"></i>Đơn vị phối hợp hoặc đồng tổ chức <span class="text-muted">(nếu có)</span></h6>
                                <p class="ms-4" style="white-space: pre-line;">
                                    @if($event->co_organizers)
                                        {{ $event->co_organizers }}
                                    @else
                                        <span class="text-muted fst-italic">Chưa có thông tin</span>
                                    @endif
                                </p>
                            </div>

                            <div class="mb-3">
                                <h6><i class="fas fa-phone-alt me-2"></i>Liên hệ / Thông tin người chịu trách nhiệm</h6>
                                <div class="ms-4">
                                    @php
                                        $contact = null;
                                        if ($event->contact_info) {
                                            $contact = is_array($event->contact_info) ? $event->contact_info : json_decode($event->contact_info, true);
                                        }
                                    @endphp
                                    @if($contact && (isset($contact['phone']) || isset($contact['email'])))
                                        @if(isset($contact['phone']) && $contact['phone'])
                                            <p class="mb-2"><i class="fas fa-phone me-2 text-primary"></i>Số điện thoại: <strong>{{ $contact['phone'] }}</strong></p>
                                        @endif
                                        @if(isset($contact['email']) && $contact['email'])
                                            <p class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i>Email: <strong><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></strong></p>
                                        @endif
                                    @else
                                        <p class="text-muted fst-italic"><i class="fas fa-info-circle me-2"></i>Chưa có thông tin liên hệ</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tài liệu và File -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Tài liệu và File</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 p-3 border rounded {{ !$event->proposal_file ? 'bg-light' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><i class="fas fa-file-pdf me-2 text-danger"></i>Kế hoạch chi tiết <span class="text-muted">(Proposal / Plan file)</span></h6>
                                        @if($event->proposal_file)
                                            <small class="text-muted">File kế hoạch chi tiết của sự kiện</small>
                                            @php
                                                $filePath = storage_path('app/public/' . $event->proposal_file);
                                                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                                                $fileSizeFormatted = $fileSize > 0 ? number_format($fileSize / 1024, 2) . ' KB' : 'N/A';
                                            @endphp
                                            <div class="mt-1">
                                                <small class="text-muted"><i class="fas fa-file me-1"></i>Kích thước: {{ $fileSizeFormatted }}</small>
                                            </div>
                                        @else
                                            <small class="text-muted fst-italic">Chưa có file</small>
                                        @endif
                                    </div>
                                    @if($event->proposal_file)
                                        <a href="{{ asset('storage/' . $event->proposal_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download me-2"></i>Tải xuống
                                        </a>
                                    @else
                                        <span class="badge bg-secondary">Chưa có</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3 p-3 border rounded {{ !$event->poster_file ? 'bg-light' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><i class="fas fa-image me-2 text-primary"></i>Poster / Ấn phẩm truyền thông <span class="text-muted">(nếu có)</span></h6>
                                        @if($event->poster_file)
                                            <small class="text-muted">Hình ảnh quảng bá sự kiện</small>
                                            @php
                                                $filePath = storage_path('app/public/' . $event->poster_file);
                                                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                                                $fileSizeFormatted = $fileSize > 0 ? number_format($fileSize / 1024, 2) . ' KB' : 'N/A';
                                            @endphp
                                            <div class="mt-1">
                                                <small class="text-muted"><i class="fas fa-file me-1"></i>Kích thước: {{ $fileSizeFormatted }}</small>
                                            </div>
                                        @else
                                            <small class="text-muted fst-italic">Chưa có file</small>
                                        @endif
                                    </div>
                                    @if($event->poster_file)
                                        <div>
                                            @if(in_array(strtolower(pathinfo($event->poster_file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                <a href="{{ asset('storage/' . $event->poster_file) }}" target="_blank" class="btn btn-sm btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#posterModal">
                                                    <i class="fas fa-eye me-2"></i>Xem
                                                </a>
                                            @endif
                                            <a href="{{ asset('storage/' . $event->poster_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download me-2"></i>Tải xuống
                                            </a>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary">Chưa có</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3 p-3 border rounded {{ !$event->permit_file ? 'bg-light' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><i class="fas fa-file-alt me-2 text-warning"></i>Giấy phép / Công văn xin tổ chức <span class="text-muted">(nếu cần)</span></h6>
                                        @if($event->permit_file)
                                            <small class="text-muted">Công văn xin tổ chức sự kiện</small>
                                            @php
                                                $filePath = storage_path('app/public/' . $event->permit_file);
                                                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                                                $fileSizeFormatted = $fileSize > 0 ? number_format($fileSize / 1024, 2) . ' KB' : 'N/A';
                                            @endphp
                                            <div class="mt-1">
                                                <small class="text-muted"><i class="fas fa-file me-1"></i>Kích thước: {{ $fileSizeFormatted }}</small>
                                            </div>
                                        @else
                                            <small class="text-muted fst-italic">Chưa có file</small>
                                        @endif
                                    </div>
                                    @if($event->permit_file)
                                        <a href="{{ asset('storage/' . $event->permit_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download me-2"></i>Tải xuống
                                        </a>
                                    @else
                                        <span class="badge bg-secondary">Chưa có</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Khách mời -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Các khách mời</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $guestData = null;
                                if ($event->guests) {
                                    $guestData = is_array($event->guests) ? $event->guests : (is_string($event->guests) ? json_decode($event->guests, true) : null);
                                }
                                $guestTypes = $guestData['types'] ?? [];
                                $guestLabels = [
                                    'lecturer' => 'Giảng viên',
                                    'student' => 'Sinh viên',
                                    'sponsor' => 'Nhà tài trợ',
                                    'other' => 'Khác'
                                ];
                                $guestIcons = [
                                    'lecturer' => 'fa-chalkboard-teacher',
                                    'student' => 'fa-user-graduate',
                                    'sponsor' => 'fa-hand-holding-usd',
                                    'other' => 'fa-ellipsis-h'
                                ];
                            @endphp
                            
                            <div class="mb-3">
                                <h6><i class="fas fa-list me-2"></i>Loại khách mời:</h6>
                                <div class="ms-4">
                                    @if(!empty($guestTypes))
                                        @foreach($guestTypes as $type)
                                            @if($type !== 'other')
                                                <span class="badge bg-primary me-2 mb-2" style="font-size: 0.9rem;">
                                                    <i class="fas {{ $guestIcons[$type] ?? 'fa-user' }} me-1"></i>
                                                    {{ $guestLabels[$type] ?? ucfirst($type) }}
                                                </span>
                                            @endif
                                        @endforeach
                                        @if(in_array('other', $guestTypes))
                                            <span class="badge bg-info me-2 mb-2" style="font-size: 0.9rem;">
                                                <i class="fas fa-ellipsis-h me-1"></i>
                                                Khác
                                            </span>
                                        @endif
                                    @else
                                        <p class="text-muted fst-italic"><i class="fas fa-info-circle me-2"></i>Chưa có thông tin về loại khách mời</p>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <h6><i class="fas fa-info-circle me-2"></i>Thông tin khách mời khác:</h6>
                                <div class="ms-4">
                                    @if(!empty($guestData['other_info']))
                                        <p style="white-space: pre-line; background: #f8f9fa; padding: 1rem; border-radius: 8px; border-left: 4px solid #17a2b8;">{{ $guestData['other_info'] }}</p>
                                    @else
                                        <p class="text-muted fst-italic" style="background: #f8f9fa; padding: 1rem; border-radius: 8px; border-left: 4px solid #dee2e6;">
                                            <i class="fas fa-info-circle me-2"></i>Chưa có thông tin chi tiết về khách mời khác
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin hệ thống -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Thông tin hệ thống</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="info-card">
                                        <div class="info-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="info-content">
                                            <h6 class="info-label">Người tạo</h6>
                                            <p class="info-value">{{ $event->creator->name ?? 'N/A' }}</p>
                                            @if($event->creator)
                                                <small class="text-muted">{{ $event->creator->email ?? '' }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="info-card">
                                        <div class="info-icon">
                                            <i class="fas fa-calendar-plus"></i>
                                        </div>
                                        <div class="info-content">
                                            <h6 class="info-label">Ngày tạo</h6>
                                            <p class="info-value">{{ $event->created_at ? $event->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                                            @if($event->created_at)
                                                <small class="text-muted">{{ $event->created_at->diffForHumans() }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="info-card">
                                        <div class="info-icon">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="info-content">
                                            <h6 class="info-label">Cập nhật lần cuối</h6>
                                            <p class="info-value">{{ $event->updated_at ? $event->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>
                                            @if($event->updated_at)
                                                <small class="text-muted">{{ $event->updated_at->diffForHumans() }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="info-card">
                                        <div class="info-icon">
                                            <i class="fas fa-link"></i>
                                        </div>
                                        <div class="info-content">
                                            <h6 class="info-label">Slug</h6>
                                            <p class="info-value">
                                                <code class="small">{{ $event->slug }}</code>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hình ảnh sự kiện -->
            @php
                $hasImages = $event->images && $event->images->count() > 0;
                $hasOldImage = !empty($event->image);
                $totalImages = ($hasImages ? $event->images->count() : 0) + ($hasOldImage ? 1 : 0);
            @endphp
            
            @if($hasImages || $hasOldImage)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Hình ảnh sự kiện 
                        <span class="badge bg-secondary ms-2">{{ $totalImages }} ảnh</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($hasImages)
                        <div class="row g-3">
                            @foreach($event->images as $index => $image)
                                <div class="col-md-4 col-lg-3">
                                    <div class="image-gallery-item">
                                        <div class="image-container">
                                            <img src="{{ $image->image_url }}" 
                                                 alt="{{ $image->alt_text }}" 
                                                 class="img-fluid rounded"
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#imageModal{{ $index }}"
                                                 style="cursor: pointer; transition: transform 0.3s ease;">
                                            <div class="image-overlay">
                                                <div class="image-number">{{ $index + 1 }}</div>
                                                <div class="image-actions">
                                                    <button class="btn btn-sm btn-light" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#imageModal{{ $index }}">
                                                        <i class="fas fa-expand"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    @if($hasOldImage)
                        <div class="row g-3">
                            <div class="col-md-4 col-lg-3">
                                <div class="image-gallery-item">
                                    <div class="image-container">
                                        <img src="{{ asset('storage/' . $event->image) }}" 
                                             alt="{{ $event->title }}" 
                                             class="img-fluid rounded"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#imageModalOld"
                                             style="cursor: pointer; transition: transform 0.3s ease;">
                                        <div class="image-overlay">
                                            <div class="image-number">{{ $hasImages ? $event->images->count() + 1 : 1 }}</div>
                                            <div class="image-actions">
                                                <button class="btn btn-sm btn-light" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#imageModalOld">
                                                    <i class="fas fa-expand"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Thống kê -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Thống kê sự kiện</h5>
                </div>
                <div class="card-body">
                    @php
                        $registrationsCount = \App\Models\EventRegistration::where('event_id', $event->id)->count();
                        $commentsCount = \App\Models\EventComment::where('event_id', $event->id)->count();
                    @endphp
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <h3 class="stat-number text-primary">{{ $registrationsCount }}</h3>
                                <p class="stat-label">Đăng ký</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <h3 class="stat-number text-success">{{ $commentsCount }}</h3>
                                <p class="stat-label">Bình luận</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Thông tin nhanh -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Thông tin nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="quick-info">
                        <div class="quick-info-item">
                            <i class="fas fa-hashtag text-muted"></i>
                            <span>ID: <strong>{{ $event->id }}</strong></span>
                        </div>
                        <div class="quick-info-item">
                            <i class="fas fa-calendar-plus text-muted"></i>
                            <span>Tạo: <strong>{{ $event->created_at ? $event->created_at->format('d/m/Y') : 'N/A' }}</strong></span>
                        </div>
                        <div class="quick-info-item">
                            <i class="fas fa-clock text-muted"></i>
                            <span>Cập nhật: <strong>{{ $event->updated_at ? $event->updated_at->format('d/m/Y') : 'N/A' }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Event Description */
.event-description {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 0;
    border: 1px solid #dee2e6;
    overflow: hidden;
}

/* Cancellation Info */
.cancellation-info {
    background: #fff5f5;
    border-radius: 8px;
    padding: 0;
    border: 1px solid #feb2b2;
    overflow: hidden;
}

.cancellation-header {
    background: #dc3545;
    color: white;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
}

.cancellation-icon {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.2rem;
    backdrop-filter: blur(10px);
}

.cancellation-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.cancellation-content {
    padding: 1.5rem;
    position: relative;
}

.cancellation-text {
    font-size: 1.1rem;
    line-height: 1.7;
    color: #2d3748;
    margin-bottom: 1rem;
    text-align: justify;
    position: relative;
    padding-left: 1rem;
    border-left: 4px solid #f56565;
}

.cancellation-text::before {
    content: '"';
    position: absolute;
    top: -10px;
    left: -5px;
    font-size: 3rem;
    color: #f56565;
    opacity: 0.3;
    font-family: serif;
    line-height: 1;
}

.cancellation-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 1rem;
    border-top: 1px solid #feb2b2;
    background: rgba(245, 101, 101, 0.05);
    margin: 0 -1.5rem -1.5rem -1.5rem;
    padding: 1rem 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.cancellation-footer small {
    font-weight: 500;
    color: #718096 !important;
}

/* Responsive cho cancellation info */
@media (max-width: 768px) {
    .cancellation-header {
        padding: 1rem;
    }
    
    .cancellation-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
        margin-right: 0.75rem;
    }
    
    .cancellation-title {
        font-size: 1.1rem;
    }
    
    .cancellation-content {
        padding: 1rem;
    }
    
    .cancellation-text {
        font-size: 1rem;
        padding-left: 0.75rem;
    }
    
    .cancellation-text::before {
        font-size: 2.5rem;
        top: -8px;
        left: -3px;
    }
    
    .cancellation-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}

.description-header {
    background: #007bff;
    color: white;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
}

.description-icon {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.2rem;
    backdrop-filter: blur(10px);
}

.description-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.description-content {
    padding: 1.5rem;
    position: relative;
}

.description-text {
    font-size: 1.1rem;
    line-height: 1.7;
    color: #2c3e50;
    margin-bottom: 1rem;
    text-align: justify;
    position: relative;
    padding-left: 1rem;
    border-left: 4px solid #007bff;
}

.description-text::before {
    content: '"';
    position: absolute;
    top: -10px;
    left: -5px;
    font-size: 3rem;
    color: #007bff;
    opacity: 0.3;
    font-family: serif;
    line-height: 1;
}

.description-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
    background: rgba(0,123,255,0.05);
    margin: 0 -1.5rem -1.5rem -1.5rem;
    padding: 1rem 1.5rem;
}

.description-footer small {
    font-weight: 500;
    color: #6c757d !important;
}

/* Responsive cho description */
@media (max-width: 768px) {
    .description-header {
        padding: 1rem;
    }
    
    .description-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
        margin-right: 0.75rem;
    }
    
    .description-title {
        font-size: 1.1rem;
    }
    
    .description-content {
        padding: 1rem;
    }
    
    .description-text {
        font-size: 1rem;
        padding-left: 0.75rem;
    }
    
    .description-text::before {
        font-size: 2.5rem;
        top: -8px;
        left: -3px;
    }
}

/* Info Cards */
.info-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    margin-bottom: 1rem;
}

/* Xóa bỏ thanh gạch ngang màu nếu có */
.info-card::before,
.info-card::after {
    display: none !important;
    content: none !important;
}

.info-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: #6c757d;
    font-size: 1.2rem;
    flex-shrink: 0;
    background: #fff;
    border: 1px solid #dee2e6;
}

/* Tắt tất cả màu nền của icon */
.info-icon.bg-primary,
.info-icon.bg-danger,
.info-icon.bg-warning,
.info-icon.bg-info,
.info-icon.bg-success,
.info-icon.bg-secondary,
.info-icon.bg-dark,
.info-icon.bg-light {
    background: transparent !important;
    background-color: transparent !important;
}

/* Xóa bỏ thanh gạch ngang màu ở icon nếu có */
.info-icon::before,
.info-icon::after {
    display: none !important;
    content: none !important;
}

/* Đảm bảo không có border-top tạo thanh gạch ngang */
.info-card > * {
    border-top: none !important;
}

/* Xóa bỏ bất kỳ thanh gạch ngang màu nào */
.info-card .info-icon,
.info-card .info-content {
    border-top: none !important;
    border-bottom: none !important;
    border-left: none !important;
    border-right: none !important;
}

/* Đảm bảo icon hiển thị đẹp và không có thanh gạch ngang */
.info-icon {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Xóa bỏ tất cả các thanh gạch ngang màu có thể xuất hiện */
.info-card::before,
.info-card::after,
.info-icon::before,
.info-icon::after,
.info-content::before,
.info-content::after {
    display: none !important;
    content: none !important;
    height: 0 !important;
    width: 0 !important;
    border: none !important;
    background: none !important;
}

/* Đảm bảo không có element nào tạo thanh gạch ngang */
.info-card > div::before,
.info-card > div::after {
    display: none !important;
    content: none !important;
}

.info-content {
    flex: 1;
}

.info-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
    font-weight: 600;
}

.info-value {
    font-size: 1rem;
    color: #212529;
    margin-bottom: 0;
    font-weight: 500;
}

/* Image Gallery */
.image-gallery-item {
    position: relative;
    margin-bottom: 1rem;
}

.image-container {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #dee2e6;
}

.image-container img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(0,0,0,0.7), rgba(0,0,0,0.3));
    opacity: 0;
    transition: opacity 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-container:hover .image-overlay {
    opacity: 1;
}

.image-number {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.image-actions {
    position: absolute;
    bottom: 10px;
    right: 10px;
}

/* Statistics */
.stat-item {
    text-align: center;
    padding: 1rem;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: white;
    font-size: 1.2rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 0;
    font-weight: 500;
}

/* Quick Info */
.quick-info-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}

.quick-info-item:last-child {
    border-bottom: none;
}

.quick-info-item i {
    width: 20px;
    margin-right: 0.75rem;
    font-size: 1rem;
}


/* Responsive */
@media (max-width: 768px) {
    .info-card {
        flex-direction: column;
        text-align: center;
    }
    
    .info-icon {
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .stat-item {
        padding: 0.5rem;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
}
</style>
@endpush

<!-- Modal hủy sự kiện (hủy nhanh) -->
@if($event->status !== 'ongoing' && in_array($event->status, ['pending', 'approved']))
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