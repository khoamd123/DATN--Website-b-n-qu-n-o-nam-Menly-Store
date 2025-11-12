@extends('layouts.student')

@section('title', $club->name . ' - Chi tiết CLB')

@php
    // Lấy thông tin thành viên của người dùng hiện tại trong CLB này
    $clubMember = $club->clubMembers->where('user_id', auth()->id())->first();

    // Lấy các sự kiện sắp tới của CLB
    $events = $club->events()->where('status', 'approved')->where('start_time', '>=', now())->orderBy('start_time', 'asc')->get();

    // Lấy các thông báo của CLB
    $announcements = $club->posts()->where('type', 'announcement')->where('status', 'published')->orderBy('created_at', 'desc')->get();

    // Lấy các bài viết diễn đàn của CLB
    $posts = $club->posts()->where('type', 'post')->where('status', 'published')->orderBy('created_at', 'desc')->get();

    // Lấy ảnh từ các sự kiện đã hoàn thành và các bài viết có ảnh
    $eventImages = \App\Models\EventImage::whereIn('event_id', $club->events()->where('status', 'completed')->pluck('id'))->get();
    $postImages = \App\Models\PostAttachment::whereIn('post_id', $club->posts()->whereNotNull('image')->pluck('id'))->get();
    $galleryImages = $eventImages->concat($postImages);

    // Lấy thành tích cá nhân (giả định, cần model Achievement)
    $achievements = collect([]); // Giả định chưa có model này, khởi tạo mảng rỗng

    // Lấy tiến trình hoạt động (giả định, cần logic tính điểm)
    $activityProgress = [
        'events_attended' => 0, 'posts_created' => 0, 'comments_made' => 0, 'total_points' => 0
    ];
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
                        <i class="fas fa-users me-1"></i> {{ $club->members->count() }} thành viên
                        <span class="mx-2">|</span>
                        <i class="fas fa-tag me-1"></i> {{ $club->field->name ?? 'Chưa phân loại' }}
                    </p>
                    <p class="mb-0">{{ $club->description }}</p>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center">
                @if($clubMember)
                    <div>
                        <span class="badge bg-success me-2">Đang hoạt động</span>
                        <small class="text-muted">Tham gia từ: {{ $clubMember->joined_at->format('d/m/Y') }}</small>
                    </div>
                @endif
                <!-- Leave Club Button -->
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#leaveClubModal">
                    <i class="fas fa-sign-out-alt me-2"></i> Rời khỏi CLB
                </button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="clubDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="events-tab" data-bs-toggle="tab" data-bs-target="#events" type="button" role="tab" aria-controls="events" aria-selected="true">
                    <i class="fas fa-calendar-alt me-2"></i> Lịch sự kiện
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="announcements-tab" data-bs-toggle="tab" data-bs-target="#announcements" type="button" role="tab" aria-controls="announcements" aria-selected="false">
                    <i class="fas fa-bullhorn me-2"></i> Thông báo
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="forum-tab" data-bs-toggle="tab" data-bs-target="#forum" type="button" role="tab" aria-controls="forum" aria-selected="false">
                    <i class="fas fa-comments me-2"></i> Diễn đàn
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#gallery" type="button" role="tab" aria-controls="gallery" aria-selected="false">
                    <i class="fas fa-images me-2"></i> Thư viện ảnh
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="my-role-tab" data-bs-toggle="tab" data-bs-target="#my-role" type="button" role="tab" aria-controls="my-role" aria-selected="false">
                    <i class="fas fa-user-shield me-2"></i> Vai trò & Thành tích
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="progress-tab" data-bs-toggle="tab" data-bs-target="#progress" type="button" role="tab" aria-controls="progress" aria-selected="false">
                    <i class="fas fa-chart-line me-2"></i> Tiến trình
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="clubDetailTabsContent">
            <!-- Lịch sự kiện CLB -->
            <div class="tab-pane fade show active" id="events" role="tabpanel" aria-labelledby="events-tab">
                <div class="content-card">
                    <h4 class="mb-3">Lịch sự kiện CLB</h4>
                    @if($events->count() > 0)
                        @foreach($events as $event)
                            <div class="event-item mb-3 p-3 border rounded">
                                <h5 class="mb-1">{{ $event->title }}</h5>
                                <p class="text-muted small mb-1">
                                    <i class="fas fa-clock me-1"></i> {{ \Carbon\Carbon::parse($event->start_time)->format('d/m/Y H:i') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('d/m/Y H:i') }}
                                </p>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i> {{ $event->location ?? 'Online' }}
                                </p>
                                <p>{{ Str::limit($event->description, 150) }}</p>
                                <a href="{{ route('student.events.show', $event->id) }}" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <p>Chưa có sự kiện nào sắp tới.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Thông báo nội bộ CLB -->
            <div class="tab-pane fade" id="announcements" role="tabpanel" aria-labelledby="announcements-tab">
                <div class="content-card">
                    <h4 class="mb-3">Thông báo nội bộ CLB</h4>
                    @if($announcements->count() > 0)
                        @foreach($announcements as $announcement)
                            <div class="announcement-item mb-3 p-3 border rounded">
                                <h5 class="mb-1">{{ $announcement->title }}</h5>
                                <p class="text-muted small mb-1">
                                    <i class="fas fa-clock me-1"></i> {{ $announcement->created_at->format('d/m/Y H:i') }}
                                </p>
                                <p>{{ Str::limit($announcement->content, 200) }}</p>
                                <a href="{{ route('student.posts.show', $announcement->id) }}" class="btn btn-sm btn-outline-info">Xem chi tiết</a>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-bullhorn fa-3x mb-3"></i>
                            <p>Chưa có thông báo nào.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Diễn đàn / thảo luận CLB -->
            <div class="tab-pane fade" id="forum" role="tabpanel" aria-labelledby="forum-tab">
                <div class="content-card">
                    <h4 class="mb-3">Diễn đàn / Thảo luận CLB</h4>
                    <a href="{{ route('student.club.forum.create', $club->id) }}" class="btn btn-primary mb-3">
                        <i class="fas fa-plus me-2"></i> Tạo bài viết mới
                    </a>
                    @if($posts->count() > 0)
                        @foreach($posts as $post)
                            <div class="forum-post-item mb-3 p-3 border rounded">
                                <h5 class="mb-1">{{ $post->title }}</h5>
                                <p class="text-muted small mb-1">
                                    <i class="fas fa-user me-1"></i> {{ $post->user->name ?? 'Người dùng ẩn danh' }}
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-clock me-1"></i> {{ $post->created_at->format('d/m/Y H:i') }}
                                </p>
                                <p>{{ Str::limit($post->content, 200) }}</p>
                                <a href="{{ route('student.posts.show', $post->id) }}" class="btn btn-sm btn-outline-secondary">Xem thảo luận</a>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-comments fa-3x mb-3"></i>
                            <p>Chưa có bài viết nào trong diễn đàn.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Thư viện ảnh / hoạt động -->
            <div class="tab-pane fade" id="gallery" role="tabpanel" aria-labelledby="gallery-tab">
                <div class="content-card">
                    <h4 class="mb-3">Thư viện ảnh / Hoạt động</h4>
                    @if($galleryImages->count() > 0)
                        <div class="row">
                            @foreach($galleryImages as $image)
                                <div class="col-md-4 mb-3">
                                    {{-- Assuming 'lightbox' is used for image viewing --}}
                                    <a href="{{ asset($image->image_path ?? $image->file_url) }}" data-lightbox="club-gallery">
                                        <img src="{{ asset($image->image_path ?? $image->file_url) }}" class="img-fluid rounded shadow-sm" alt="{{ $image->alt_text ?? 'Club Image' }}">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-images fa-3x mb-3"></i>
                            <p>Chưa có ảnh hoạt động nào.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Vai trò và thành tích cá nhân trong CLB -->
            <div class="tab-pane fade" id="my-role" role="tabpanel" aria-labelledby="my-role-tab">
                <div class="content-card">
                    <h4 class="mb-3">Vai trò và thành tích cá nhân</h4>
                    @if($clubMember)
                        <div class="mb-3">
                            <strong>Vai trò của bạn:</strong>
                            @php
                                $position = $clubMember->position;
                                $badgeClass = '';
                                $positionText = '';
                                switch($position) {
                                    case 'leader': $badgeClass = 'bg-warning'; $positionText = 'Trưởng CLB'; break;
                                    case 'vice_president': $badgeClass = 'bg-info'; $positionText = 'Phó CLB'; break;
                                    case 'officer': $badgeClass = 'bg-primary'; $positionText = 'Cán sự'; break;
                                    default: $badgeClass = 'bg-secondary'; $positionText = 'Thành viên'; break;
                                }
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $positionText }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Ngày tham gia:</strong> {{ $clubMember->joined_at->format('d/m/Y') }}
                        </div>
                    @endif
                    <hr>
                    <h5>Thành tích cá nhân</h5>
                    @if($achievements->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($achievements as $achievement)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-trophy text-warning me-2"></i> {{ $achievement->name }}
                                        <small class="text-muted d-block">{{ $achievement->description }}</small>
                                    </div>
                                    <span class="badge bg-info rounded-pill">{{ $achievement->date->format('Y') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-trophy fa-3x mb-3"></i>
                            <p>Chưa có thành tích nào được ghi nhận.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Theo dõi tiến trình hoạt động -->
            <div class="tab-pane fade" id="progress" role="tabpanel" aria-labelledby="progress-tab">
                <div class="content-card">
                    <h4 class="mb-3">Theo dõi tiến trình hoạt động</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="progress-card p-3 border rounded text-center">
                                <h5 class="text-teal mb-1">{{ $activityProgress['events_attended'] ?? 0 }}</h5>
                                <small class="text-muted">Sự kiện đã tham gia</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="progress-card p-3 border rounded text-center">
                                <h5 class="text-teal mb-1">{{ $activityProgress['posts_created'] ?? 0 }}</h5>
                                <small class="text-muted">Bài viết đã đăng</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="progress-card p-3 border rounded text-center">
                                <h5 class="text-teal mb-1">{{ $activityProgress['comments_made'] ?? 0 }}</h5>
                                <small class="text-muted">Bình luận đã gửi</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="progress-card p-3 border rounded text-center">
                                <h5 class="text-teal mb-1">{{ $activityProgress['total_points'] ?? 0 }}</h5>
                                <small class="text-muted">Điểm hoạt động</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h5>Biểu đồ hoạt động (ví dụ)</h5>
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>
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
            <div class="list-group list-group-flush">
                <a href="#events" class="list-group-item list-group-item-action">
                    <i class="fas fa-calendar-alt me-2 text-teal"></i> Lịch sự kiện
                </a>
                <a href="#announcements" class="list-group-item list-group-item-action">
                    <i class="fas fa-bullhorn me-2 text-teal"></i> Thông báo
                </a>
                <a href="#forum" class="list-group-item list-group-item-action">
                    <i class="fas fa-comments me-2 text-teal"></i> Diễn đàn
                </a>
                <a href="#gallery" class="list-group-item list-group-item-action">
                    <i class="fas fa-images me-2 text-teal"></i> Thư viện ảnh
                </a>
            </div>
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
