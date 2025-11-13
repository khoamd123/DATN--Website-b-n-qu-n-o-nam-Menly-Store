@extends('layouts.student')

@section('title', $club->name . ' - Chi tiết CLB')

@php
    // Lấy thông tin thành viên của người dùng hiện tại trong CLB này (nếu là thành viên)
    // Biến $isMember và $user đã được truyền từ StudentController
    $clubMember = $isMember ? $club->clubMembers->where('user_id', $user->id)->first() : null;

    // Kiểm tra xem người dùng đã gửi yêu cầu tham gia chưa
    $joinRequest = $club->joinRequests()->where('user_id', $user->id)->where('status', 'pending')->first();

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
                @if($isMember && $clubMember)
                    <div>
                        <span class="badge bg-success me-2">Đang hoạt động</span>
                        <small class="text-muted">Tham gia từ: {{ $clubMember->joined_at->format('d/m/Y') }}</small>
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

        @if($isMember)
            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs mb-4" id="clubDetailTabs" role="tablist">
                {{-- ... CÁC TAB SẼ ĐƯỢC GIỮ NGUYÊN Ở ĐÂY ... --}}
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="clubDetailTabsContent">
                {{-- ... NỘI DUNG CÁC TAB SẼ ĐƯỢC GIỮ NGUYÊN Ở ĐÂY ... --}}
            </div>
        @else
            {{-- Giao diện cho người chưa phải là thành viên --}}
            <div class="content-card text-center py-5">
                <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                <h4 class="mb-3">Nội dung dành cho thành viên</h4>
                <p class="text-muted">Bạn cần là thành viên của câu lạc bộ để xem các hoạt động, diễn đàn và thông tin nội bộ.</p>
                <p>Hãy gửi yêu cầu tham gia để không bỏ lỡ các hoạt động thú vị!</p>
            </div>
        @endif
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
                        <i class="fas fa-comments me-2 text-teal"></i> Diễn đàn
                    </a>
                    <a href="#gallery" class="list-group-item list-group-item-action">
                        <i class="fas fa-images me-2 text-teal"></i> Thư viện ảnh
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
