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
                <button class="nav-link @if(!$isMember) disabled @endif" id="forum-tab" data-bs-toggle="tab" data-bs-target="#forum" type="button" role="tab" aria-controls="forum" aria-selected="false">
                    <i class="fas fa-newspaper me-1"></i> Bài viết
                </button>
            </li>
            <li class="nav-item" role="presentation">
                @if($isMember)
                    <a class="nav-link" href="{{ route('student.club-management.reports') }}?club={{ $club->id }}" role="tab">
                        <i class="fas fa-chart-bar me-1"></i> Báo cáo
                    </a>
                @else
                    <button class="nav-link disabled" type="button" role="tab" disabled>
                        <i class="fas fa-chart-bar me-1"></i> Báo cáo
                    </button>
                @endif
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="clubDetailTabsContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                <!-- Giới thiệu CLB -->
                <div class="content-card mb-4">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-info-circle text-teal me-2"></i> Giới thiệu về CLB
                    </h5>
                    <div class="introduction-content">
                        @if($club->introduction)
                            {!! nl2br(e($club->introduction)) !!}
                        @else
                            <p class="text-muted">Chưa có bài viết giới thiệu chi tiết.</p>
                        @endif
                    </div>
                </div>

                @if($isMember)
                <!-- Thống kê nhanh -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="content-card text-center">
                            <div class="stat-icon-large bg-primary mb-2">
                                <i class="fas fa-users"></i>
                            </div>
                            <h4 class="mb-1">{{ $club->members_count }}</h4>
                            <p class="text-muted mb-0">Thành viên</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="content-card text-center">
                            <div class="stat-icon-large bg-success mb-2">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h4 class="mb-1">{{ $club->events->count() ?? 0 }}</h4>
                            <p class="text-muted mb-0">Sự kiện</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="content-card text-center">
                            <div class="stat-icon-large bg-info mb-2">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <h4 class="mb-1">{{ isset($announcements) ? $announcements->count() : 0 }}</h4>
                            <p class="text-muted mb-0">Thông báo</p>
                        </div>
                    </div>
                </div>

                <!-- Hoạt động gần đây -->
                @if(isset($events) && $events->count() > 0)
                <div class="content-card">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-clock text-teal me-2"></i> Sự kiện sắp tới
                    </h5>
                    <div class="list-group list-group-flush">
                        @foreach($events->take(3) as $event)
                        <div class="list-group-item px-0 border-0 border-bottom">
                            <div class="d-flex align-items-start">
                                <div class="event-date-badge me-3 text-center">
                                    <div class="text-danger fw-bold fs-5">{{ \Carbon\Carbon::parse($event->start_time)->format('d') }}</div>
                                    <div class="text-muted small">{{ \Carbon\Carbon::parse($event->start_time)->format('M') }}</div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">{{ $event->title }}</h6>
                                    <p class="text-muted mb-1 small">
                                        <i class="fas fa-clock me-1"></i> 
                                        {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($event->start_time)->format('d/m/Y') }}
                                    </p>
                                    @if($event->location)
                                    <p class="text-muted mb-0 small">
                                        <i class="fas fa-map-marker-alt me-1"></i> {{ $event->location }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($events->count() > 3)
                    <div class="mt-3 text-center">
                        <a href="#events" class="btn btn-outline-primary btn-sm" data-bs-toggle="tab" data-bs-target="#events">
                            Xem tất cả sự kiện <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    @endif
                </div>
                @endif
                @endif
            </div>

            @if($isMember)
                <!-- Events Tab -->
                <div class="tab-pane fade" id="events" role="tabpanel" aria-labelledby="events-tab">
                    <div class="content-card">
                        <h5 class="card-title mb-4">Sự kiện sắp tới và đã diễn ra</h5>
                        @if($club->events && $club->events->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($club->events as $event)
                                    <a href="#" class="list-group-item list-group-item-action px-0">
                                        <div class="row align-items-center">
                                            <div class="col-auto text-center" style="width: 80px;">
                                                <div class="text-danger fw-bold fs-5">{{ \Carbon\Carbon::parse($event->start_time)->format('d') }}</div>
                                                <div class="text-muted small">Thg {{ \Carbon\Carbon::parse($event->start_time)->format('m') }}</div>
                                            </div>
                                            <div class="col">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1 fw-bold">{{ $event->name }}</h6>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($event->start_time)->diffForHumans() }}</small>
                                                </div>
                                                <p class="text-muted mb-1 small">
                                                    <i class="fas fa-clock me-1"></i> {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('H:i, d/m/Y') }}
                                                </p>
                                                <p class="text-muted mb-0 small"><i class="fas fa-map-marker-alt me-1"></i> {{ $event->location }}</p>
                                                <p class="mb-0 mt-2">
                                                    @php
                                                        $desc = $event->description ?? '';
                                                        $desc = mb_strlen($desc) > 150 ? mb_substr($desc, 0, 150) . '...' : $desc;
                                                    @endphp
                                                    {{ $desc }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Hiện tại chưa có sự kiện nào được lên lịch.</p>
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
                <!-- Forum Tab - Sử dụng Posts như diễn đàn -->
                <div class="tab-pane fade" id="forum" role="tabpanel" aria-labelledby="forum-tab">
                    <div class="content-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-newspaper text-teal me-2"></i> Bài viết
                            </h5>
                            @if($isMember)
                            <a href="{{ route('student.posts.create') }}?club_id={{ $club->id }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Tạo chủ đề mới
                            </a>
                            @endif
                        </div>
                        
                        @if(isset($posts) && $posts->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($posts as $post)
                            <a href="{{ route('student.posts.show', $post->id) }}" class="list-group-item list-group-item-action px-0 border-0 border-bottom">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">{{ $post->title }}</h6>
                                        <p class="text-muted mb-2 small">
                                            @php
                                                $content = strip_tags($post->content ?? '');
                                                $content = mb_strlen($content) > 150 ? mb_substr($content, 0, 150) . '...' : $content;
                                            @endphp
                                            {{ $content }}
                                        </p>
                                        <div class="d-flex align-items-center gap-3">
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i> {{ $post->user->name ?? 'N/A' }}
                                            </small>
                                            <small class="text-muted">
                                                <i class="far fa-clock me-1"></i> {{ $post->created_at->diffForHumans() }}
                                            </small>
                                            @if($post->comments_count ?? $post->comments->count() ?? 0 > 0)
                                            <small class="text-muted">
                                                <i class="fas fa-comments me-1"></i> {{ $post->comments_count ?? $post->comments->count() }} bình luận
                                            </small>
                                            @endif
                                            @if($post->views ?? 0 > 0)
                                            <small class="text-muted">
                                                <i class="fas fa-eye me-1"></i> {{ number_format($post->views) }} lượt xem
                                            </small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        @if($post->image)
                                        <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" 
                                             class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                        @endif
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        
                        <div class="mt-3 text-center">
                            <a href="{{ route('student.posts') }}?club_id={{ $club->id }}" class="btn btn-outline-primary">
                                Xem tất cả bài viết <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted mb-2">Chưa có bài viết nào</h5>
                            <p class="text-muted mb-4">Hãy tạo bài viết đầu tiên của CLB!</p>
                            @if($isMember)
                            <a href="{{ route('student.posts.create') }}?club_id={{ $club->id }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Tạo chủ đề mới
                            </a>
                            @endif
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
                    <a href="#events" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-alt me-2 text-teal"></i> Lịch sự kiện
                    </a>
                    <a href="#announcements" class="list-group-item list-group-item-action">
                        <i class="fas fa-bullhorn me-2 text-teal"></i> Thông báo
                    </a>
                    <a href="#forum" class="list-group-item list-group-item-action">
                        <i class="fas fa-newspaper me-2 text-teal"></i> Bài viết
                    </a>
                    <a href="{{ route('student.club-management.reports') }}?club={{ $club->id }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar me-2 text-teal"></i> Báo cáo
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
    .gallery-item img {
        width: 100%;
        height: 250px; /* Or any fixed height you prefer */
        object-fit: cover;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .gallery-item:hover img {
        transform: scale(1.05);
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }
    
    .stat-icon-large {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        margin: 0 auto;
    }
    
    .event-date-badge {
        min-width: 60px;
        background: #f0fdfa;
        border-radius: 8px;
        padding: 0.5rem;
    }
    
    .introduction-content {
        line-height: 1.8;
        color: #495057;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Example Chart for Activity Progress
        var ctx = document.getElementById('activityChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
                    datasets: [{
                        label: 'Điểm hoạt động',
                        data: [10, 15, 20, 25, 30, 35], // Replace with actual data
                        borderColor: '#14b8a6',
                        backgroundColor: 'rgba(20, 184, 166, 0.2)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
