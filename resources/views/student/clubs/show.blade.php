@extends('layouts.student')

@section('title', $club->name . ' - Chi tiết CLB')

@php
@endphp

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Club Header/Overview -->
        <div class="content-card mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="club-logo-large me-4">
                    @if($club->logo)
                        <img src="{{ asset($club->logo) }}" alt="{{ $club->name }} Logo" class="img-fluid rounded-circle">
                    @else
                        {{ substr($club->name, 0, 2) }}
                    @endif
                </div>
                <div>
                    <h2 class="mb-1">{{ $club->name }}</h2>
                    <p class="text-muted mb-2">
                        <i class="fas fa-users me-1"></i> {{ $club->members_count }} thành viên
                        <span class="mx-2">|</span>
                        <i class="fas fa-tag me-1"></i> {{ $club->field->name ?? 'Chưa phân loại' }}
                    </p>
                    <p class="mb-0">{{ $club->description }}</p>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center">
                @if($isMember && $clubMember)
                    <div>
                        <span class="badge bg-success me-2">Đang hoạt động</span>
                        <small class="text-muted">Tham gia từ: {{ $clubMember->joined_at ? (\Carbon\Carbon::parse($clubMember->joined_at)->format('d/m/Y')) : 'N/A' }}</small>
                    </div>
                    <!-- Leave Club Button -->
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#leaveClubModal">
                        <i class="fas fa-sign-out-alt me-2"></i> Rời khỏi CLB
                    </button>
                @elseif($joinRequest)
                    <div>
                        <span class="badge bg-warning me-2">Đã gửi yêu cầu</span>
                        <small class="text-muted">Yêu cầu của bạn đang chờ được duyệt.</small>
                    </div>
                    <form action="{{ route('student.clubs.cancel_join_request', $club->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Hủy yêu cầu</button>
                    </form>
                @else
                    <span class="text-muted">Bạn chưa phải là thành viên của CLB này.</span>
                    <form action="{{ route('student.clubs.join', $club->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">Gửi yêu cầu tham gia</button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="clubDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                    <i class="fas fa-info-circle me-1"></i> Tổng quan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link @if(!$isMember) disabled @endif" id="events-tab" data-bs-toggle="tab" data-bs-target="#events" type="button" role="tab" aria-controls="events" aria-selected="false">
                    <i class="fas fa-calendar-alt me-1"></i> Sự kiện
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link @if(!$isMember) disabled @endif" id="announcements-tab" data-bs-toggle="tab" data-bs-target="#announcements" type="button" role="tab" aria-controls="announcements" aria-selected="false">
                    <i class="fas fa-bullhorn me-1"></i> Thông báo
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link @if(!$isMember) disabled @endif" id="posts-tab" data-bs-toggle="tab" data-bs-target="#posts" type="button" role="tab" aria-controls="posts" aria-selected="false">
                    <i class="fas fa-newspaper me-1"></i> Bài viết
                </button>
            </li>
            {{-- Management Tab --}}
            @if($isMember && in_array(session('club_roles')[$club->id] ?? null, ['leader', 'vice_president', 'officer']))
            <li class="nav-item" role="presentation">
                <a class="nav-link text-danger" href="{{ route('student.club-management.reports') }}">
                    <i class="fas fa-chart-line me-1"></i> Báo cáo & Quản lý</a>
            </li>
            @endif
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="clubDetailTabsContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                <div class="content-card mb-4">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-info-circle text-teal me-2"></i>Giới thiệu về CLB
                    </h5>
                    <div class="club-introduction">
                        <p class="text-muted mb-4">{{ $club->introduction ?? $club->description ?? 'Chưa có bài viết giới thiệu chi tiết.' }}</p>
                    </div>
                </div>

                <!-- Club Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="content-card text-center h-100 stat-card">
                            <div class="stat-icon mb-3">
                                <i class="fas fa-users fa-2x text-teal"></i>
                            </div>
                            <h3 class="mb-1 fw-bold">{{ $club->members_count ?? 0 }}</h3>
                            <p class="text-muted mb-0">Thành viên</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="content-card text-center h-100 stat-card">
                            <div class="stat-icon mb-3">
                                <i class="fas fa-calendar-check fa-2x text-primary"></i>
                            </div>
                            <h3 class="mb-1 fw-bold">{{ $club->events()->where('status', 'approved')->count() }}</h3>
                            <p class="text-muted mb-0">Sự kiện</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="content-card text-center h-100 stat-card">
                            <div class="stat-icon mb-3">
                                <i class="fas fa-newspaper fa-2x text-info"></i>
                            </div>
                            <h3 class="mb-1 fw-bold">{{ $club->posts()->where('type', 'post')->where('status', 'published')->count() }}</h3>
                            <p class="text-muted mb-0">Bài viết</p>
                        </div>
                    </div>
                </div>

                <!-- Club Details -->
                <div class="content-card">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-list-ul text-teal me-2"></i>Thông tin chi tiết
                    </h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="detail-item">
                                <i class="fas fa-tag text-teal me-2"></i>
                                <strong>Lĩnh vực:</strong>
                                <span class="ms-2">{{ $club->field->name ?? 'Chưa phân loại' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="detail-item">
                                <i class="fas fa-crown text-warning me-2"></i>
                                <strong>Trưởng CLB:</strong>
                                <span class="ms-2">{{ $club->leader->name ?? 'Chưa có' }}</span>
                            </div>
                        </div>
                        @if($club->established_at)
                        <div class="col-md-6 mb-3">
                            <div class="detail-item">
                                <i class="fas fa-calendar-plus text-info me-2"></i>
                                <strong>Ngày thành lập:</strong>
                                <span class="ms-2">{{ \Carbon\Carbon::parse($club->established_at)->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        @endif
                        @if($club->contact_email)
                        <div class="col-md-6 mb-3">
                            <div class="detail-item">
                                <i class="fas fa-envelope text-danger me-2"></i>
                                <strong>Email liên hệ:</strong>
                                <a href="mailto:{{ $club->contact_email }}" class="ms-2">{{ $club->contact_email }}</a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($isMember)
                <!-- Events Tab -->
                <div class="tab-pane fade" id="events" role="tabpanel" aria-labelledby="events-tab">
                    <div class="content-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>Sự kiện
                            </h5>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary active" data-filter="all">Tất cả</button>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-filter="upcoming">Sắp tới</button>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-filter="past">Đã qua</button>
                            </div>
                        </div>
                        
                        @php
                            $allEvents = $club->events()->where('status', 'approved')->orderBy('start_time', 'desc')->get();
                            $now = \Carbon\Carbon::now();
                            $upcomingEvents = $allEvents->filter(function($event) use ($now) {
                                return \Carbon\Carbon::parse($event->start_time)->gte($now);
                            });
                            $pastEvents = $allEvents->filter(function($event) use ($now) {
                                return \Carbon\Carbon::parse($event->start_time)->lt($now);
                            });
                        @endphp

                        @if($allEvents->count() > 0)
                            <!-- Upcoming Events -->
                            <div class="events-section" id="upcoming-events">
                                @if($upcomingEvents->count() > 0)
                                    <div class="upcoming-events-section">
                                        <h6 class="text-primary mb-3 upcoming-header">
                                            <i class="fas fa-arrow-up me-1"></i>Sự kiện sắp tới ({{ $upcomingEvents->count() }})
                                        </h6>
                                        <div class="list-group list-group-flush mb-4 upcoming-list">
                                            @foreach($upcomingEvents as $event)
                                                <a href="{{ route('student.events.show', $event->id) }}" class="text-decoration-none">
                                                    <div class="event-card list-group-item list-group-item-action px-0 mb-3 border rounded-3 shadow-sm upcoming-event">
                                                        <div class="row align-items-center g-0">
                                                            <div class="col-auto text-center p-3" style="width: 100px; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); border-radius: 12px 0 0 12px;">
                                                                <div class="text-white fw-bold fs-4">{{ \Carbon\Carbon::parse($event->start_time)->format('d') }}</div>
                                                                <div class="text-white-50 small">{{ \Carbon\Carbon::parse($event->start_time)->format('M') }}</div>
                                                                <div class="text-white-50 small">{{ \Carbon\Carbon::parse($event->start_time)->format('Y') }}</div>
                                                            </div>
                                                            <div class="col p-3">
                                                                <div class="d-flex w-100 justify-content-between align-items-start mb-2">
                                                                    <h6 class="mb-1 fw-bold text-primary">{{ $event->name }}</h6>
                                                                    <span class="badge bg-success">Sắp diễn ra</span>
                                                                </div>
                                                                <div class="event-info mb-2">
                                                                    <p class="text-muted mb-1 small">
                                                                        <i class="fas fa-clock text-teal me-1"></i> 
                                                                        {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('H:i, d/m/Y') }}
                                                                    </p>
                                                                    <p class="text-muted mb-1 small">
                                                                        <i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $event->location ?? 'Chưa có địa điểm' }}
                                                                    </p>
                                                                </div>
                                                                <p class="mb-0 text-muted">{{ Str::limit(strip_tags($event->description ?? ''), 200) }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Past Events -->
                                @if($pastEvents->count() > 0)
                                    <div class="past-events-section">
                                        <h6 class="text-muted mb-3 past-header">
                                            <i class="fas fa-history me-1"></i>Sự kiện đã qua ({{ $pastEvents->count() }})
                                        </h6>
                                        <div class="list-group list-group-flush past-list">
                                            @foreach($pastEvents as $event)
                                                <a href="{{ route('student.events.show', $event->id) }}" class="text-decoration-none">
                                                    <div class="event-card list-group-item list-group-item-action px-0 mb-3 border rounded-3 shadow-sm past-event" style="opacity: 0.8;">
                                                        <div class="row align-items-center g-0">
                                                            <div class="col-auto text-center p-3" style="width: 100px; background: #6c757d; border-radius: 12px 0 0 12px;">
                                                                <div class="text-white fw-bold fs-4">{{ \Carbon\Carbon::parse($event->start_time)->format('d') }}</div>
                                                                <div class="text-white-50 small">{{ \Carbon\Carbon::parse($event->start_time)->format('M') }}</div>
                                                                <div class="text-white-50 small">{{ \Carbon\Carbon::parse($event->start_time)->format('Y') }}</div>
                                                            </div>
                                                            <div class="col p-3">
                                                                <div class="d-flex w-100 justify-content-between align-items-start mb-2">
                                                                    <h6 class="mb-1 fw-bold">{{ $event->name }}</h6>
                                                                    <span class="badge bg-secondary">Đã kết thúc</span>
                                                                </div>
                                                                <div class="event-info mb-2">
                                                                    <p class="text-muted mb-1 small">
                                                                        <i class="fas fa-clock me-1"></i> 
                                                                        {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('H:i, d/m/Y') }}
                                                                    </p>
                                                                    <p class="text-muted mb-1 small">
                                                                        <i class="fas fa-map-marker-alt me-1"></i> {{ $event->location ?? 'Chưa có địa điểm' }}
                                                                    </p>
                                                                </div>
                                                                <p class="mb-0 text-muted">{{ Str::limit(strip_tags($event->description ?? ''), 200) }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Chưa có sự kiện</h5>
                                <p class="text-muted">CLB này chưa tổ chức sự kiện nào.</p>
                            </div>
                        @endif
                    </div>
                </div>
                <!-- Announcements Tab -->
                <div class="tab-pane fade" id="announcements" role="tabpanel" aria-labelledby="announcements-tab">
                    <div class="content-card">
                        <h5 class="card-title mb-4">Thông báo mới nhất</h5>
                        @if($club->announcements && $club->announcements->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($club->announcements as $announcement)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1 fw-bold">{{ $announcement->title }}</h6>
                                            <small class="text-muted">{{ $announcement->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1">{{ $announcement->content }}</p>
                                        <small class="text-muted">Đăng bởi: {{ $announcement->user->name ?? 'Ban chủ nhiệm' }}</small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Chưa có thông báo nào.</p>
                            </div>
                        @endif
                    </div>
                </div>
                <!-- Posts Tab -->
                <div class="tab-pane fade" id="posts" role="tabpanel" aria-labelledby="posts-tab">
                    <div class="content-card">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-newspaper text-info me-2"></i>Bài viết của CLB
                        </h5>
                        @if($posts && $posts->count() > 0)
                            <div class="row">
                                @foreach($posts as $post)
                                    <div class="col-md-6 mb-4">
                                        <a href="{{ route('student.posts.show', $post->id) }}" class="text-decoration-none">
                                            <div class="card h-100 border-0 shadow-sm post-card">
                                                @if($post->image)
                                                    <img src="{{ asset($post->image) }}" class="card-img-top" alt="{{ $post->title }}" style="height: 200px; object-fit: cover;">
                                                @elseif($post->attachments && $post->attachments->count() > 0)
                                                    <img src="{{ asset($post->attachments->first()->file_path) }}" class="card-img-top" alt="{{ $post->title }}" style="height: 200px; object-fit: cover;">
                                                @else
                                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                        <i class="fas fa-newspaper fa-3x text-muted"></i>
                                                    </div>
                                                @endif
                                                <div class="card-body">
                                                    <h6 class="card-title fw-bold mb-2 text-dark">
                                                        {{ Str::limit($post->title, 60) }}
                                                    </h6>
                                                    <p class="card-text text-muted small mb-3">{{ Str::limit(strip_tags($post->content), 120) }}</p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">
                                                            <i class="fas fa-user me-1"></i>{{ $post->user->name ?? 'CLB' }}
                                                        </small>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>{{ $post->created_at->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                    @if($post->views > 0)
                                                        <small class="text-muted d-block mt-2">
                                                            <i class="fas fa-eye me-1"></i>{{ $post->views }} lượt xem
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                            
                            @if($club->posts()->where('type', 'post')->where('status', 'published')->count() > $posts->count())
                                <div class="text-center mt-4">
                                    <a href="{{ route('student.posts', ['club_id' => $club->id]) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-arrow-right me-2"></i>Xem tất cả bài viết
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Chưa có bài viết</h5>
                                <p class="text-muted">CLB này chưa đăng bài viết nào.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
            {{-- Giao diện cho người chưa phải là thành viên --}}
            <div class="content-card text-center py-5">
                <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                <h4 class="mb-3">Nội dung dành cho thành viên</h4>
                <p class="text-muted">Bạn cần là thành viên của câu lạc bộ để xem các hoạt động, bài viết và thông tin nội bộ.</p>
                <p>Hãy gửi yêu cầu tham gia để không bỏ lỡ các hoạt động thú vị!</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="sidebar">
            <h5 class="sidebar-title">
                <i class="fas fa-info-circle"></i> Thông tin CLB
            </h5>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div>
                    <div class="fw-bold">Trưởng CLB</div>
                    <small class="text-muted">{{ $club->leader->name ?? 'Chưa có' }}</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <div class="fw-bold">Email liên hệ</div>
                    <small class="text-muted">{{ $club->contact_email ?? 'Chưa cập nhật' }}</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div>
                    <div class="fw-bold">Ngày thành lập</div>
                    <small class="text-muted">{{ $club->established_at ? $club->established_at->format('d/m/Y') : 'Chưa cập nhật' }}</small>
                </div>
            </div>
        </div>

        <div class="sidebar mt-4">
            <h5 class="sidebar-title">
                <i class="fas fa-link"></i> Liên kết nhanh
            </h5>
            @if($isMember)
                <div class="list-group list-group-flush">
                    <a href="#events" class="list-group-item list-group-item-action" onclick="document.getElementById('events-tab').click(); return false;">
                        <i class="fas fa-calendar-alt me-2 text-teal"></i> Lịch sự kiện
                    </a>
                    <a href="#announcements" class="list-group-item list-group-item-action" onclick="document.getElementById('announcements-tab').click(); return false;">
                        <i class="fas fa-bullhorn me-2 text-teal"></i> Thông báo
                    </a>
                    <a href="#posts" class="list-group-item list-group-item-action" onclick="document.getElementById('posts-tab').click(); return false;">
                        <i class="fas fa-newspaper me-2 text-teal"></i> Bài viết
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Leave Club Confirmation Modal -->
<div class="modal fade" id="leaveClubModal" tabindex="-1" aria-labelledby="leaveClubModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="leaveClubModalLabel">Xác nhận rời khỏi CLB</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn rời khỏi CLB <strong>{{ $club->name }}</strong> không?</p>
                <p class="text-danger">Hành động này không thể hoàn tác và bạn sẽ mất tất cả vai trò, quyền hạn cũng như thành tích trong CLB này.</p>
                <p>Nếu bạn là trưởng CLB hoặc cán sự, vui lòng đảm bảo đã bàn giao công việc trước khi rời đi.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <form action="{{ route('student.clubs.leave', $club->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Xác nhận rời CLB</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .club-logo-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #14b8a6;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: bold;
        overflow: hidden; /* Ensure image fits */
    }
    .club-logo-large img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .text-teal {
        color: #14b8a6 !important;
    }
    .bg-teal {
        background-color: #14b8a6 !important;
    }
    .nav-tabs .nav-link.active {
        color: #14b8a6;
        border-color: #14b8a6 #14b8a6 #fff;
    }
    .nav-tabs .nav-link {
        color: #6c757d;
    }
    .nav-tabs .nav-link.text-danger:hover {
        border-color: #f8d7da;
        background-color: #fdf2f2;
    }
    .content-card {
        margin-bottom: 1.5rem;
    }
    .sidebar-item .sidebar-icon {
        background: #f0fdfa;
        color: #14b8a6;
    }
    .progress-card {
        background-color: #f0fdfa;
        border-color: #a7f3d0 !important;
    }
    .stat-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #e2e8f0;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }
    .stat-icon {
        margin-bottom: 1rem;
    }
    .detail-item {
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: 8px;
        border-left: 3px solid #14b8a6;
    }
    .event-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }
    .event-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }
    a .event-card {
        color: inherit;
    }
    a:hover .event-card {
        text-decoration: none;
    }
    .post-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }
    .post-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
    }
    a .post-card {
        color: inherit;
    }
    a:hover .post-card {
        text-decoration: none;
    }
    .past-event {
        display: block;
    }
    .events-section .past-event.hidden {
        display: none;
    }
    .upcoming-events-section,
    .past-events-section {
        display: block;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Event filter functionality
        const filterButtons = document.querySelectorAll('[data-filter]');
        const upcomingSection = document.querySelector('.upcoming-events-section');
        const pastSection = document.querySelector('.past-events-section');
        
        if (filterButtons.length > 0) {
            filterButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const filter = this.getAttribute('data-filter');
                    
                    // Update active button
                    filterButtons.forEach(btn => {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // Filter events
                    if (filter === 'upcoming') {
                        // Show upcoming, hide past
                        if (upcomingSection) {
                            upcomingSection.style.display = 'block';
                        }
                        if (pastSection) {
                            pastSection.style.display = 'none';
                        }
                    } else if (filter === 'past') {
                        // Hide upcoming, show past
                        if (upcomingSection) {
                            upcomingSection.style.display = 'none';
                        }
                        if (pastSection) {
                            pastSection.style.display = 'block';
                        }
                    } else {
                        // Show all
                        if (upcomingSection) {
                            upcomingSection.style.display = 'block';
                        }
                        if (pastSection) {
                            pastSection.style.display = 'block';
                        }
                    }
                });
            });
        }
    });
</script>
@endpush
@endsection
