@extends('layouts.student')

@section('title', $club->name . ' - Chi tiết CLB')

@php
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Club Header/Overview -->
        <div class="content-card mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="club-logo-large me-4">
                    @php
                        $logoUrl = null;
                        $hasLogo = false;
                        if ($club->logo) {
                            $logoPath = $club->logo;
                            if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
                                $logoUrl = $logoPath;
                                $hasLogo = true;
                            } else {
                                $fullPath = public_path($logoPath);
                                if (file_exists($fullPath)) {
                                    $logoUrl = asset($logoPath);
                                    $hasLogo = true;
                                }
                            }
                        }
                    @endphp
                    @if($hasLogo && $logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $club->name }} Logo" class="img-fluid rounded-circle club-logo-img-large" 
                             onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <span class="club-logo-fallback-large" style="display: none;">{{ substr($club->name, 0, 2) }}</span>
                    @else
                        <span class="club-logo-fallback-large">{{ substr($club->name, 0, 2) }}</span>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <h2 class="mb-2">{{ $club->name }}</h2>
                    <div class="d-flex flex-wrap align-items-center gap-3 text-muted mb-3">
                        <span><i class="fas fa-users me-1"></i> {{ $membersCount ?? $club->members_count ?? 0 }} thành viên</span>
                        <span class="text-muted">|</span>
                        <span><i class="fas fa-tag me-1"></i> {{ $club->field->name ?? 'Chưa phân loại' }}</span>
                    </div>
                    @php $desc = $club->description; @endphp
                    @if(!empty($desc))
                        <div class="mb-0 text-muted">
                            {!! html_entity_decode($desc, ENT_QUOTES, 'UTF-8') !!}
                        </div>
                    @else
                        <p class="mb-0 text-muted">Chưa có mô tả.</p>
                    @endif
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                @if($isMember && $clubMember)
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i> Đang hoạt động
                        </span>
                        @if($clubMember->position)
                            @php
                                $roleMap = [
                                    'leader' => 'Trưởng CLB',
                                    'vice_president' => 'Phó CLB',
                                    'treasurer' => 'Thủ quỹ',
                                    'member' => 'Thành viên',
                                    'owner' => 'Chủ nhiệm',
                                ];
                            @endphp
                            <span class="badge bg-info">
                                <i class="fas fa-user-tag me-1"></i> 
                                {{ $roleMap[$clubMember->position] ?? $clubMember->position }}
                            </span>
                        @endif
                        <small class="text-muted">
                            <i class="far fa-calendar me-1"></i> 
                            Ngày thành lập: {{ $club->created_at ? $club->created_at->format('d/m/Y') : 'Chưa cập nhật' }}
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('student.club-management.fund-deposit', ['club' => $club->id]) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-wallet me-1"></i> Nộp quỹ
                        </a>
                        @if(in_array($clubMember->position ?? '', ['leader', 'vice_president', 'treasurer']))
                            <a href="{{ route('student.club-management.index') }}?club={{ $club->id }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-cog me-1"></i> Quản lý CLB
                            </a>
                        @endif
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#leaveClubModal">
                            <i class="fas fa-sign-out-alt me-1"></i> Rời khỏi CLB
                        </button>
                    </div>
                @elseif($joinRequest)
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        <span class="badge bg-warning">
                            <i class="fas fa-clock me-1"></i> Đã gửi yêu cầu
                        </span>
                        <small class="text-muted">Yêu cầu tham gia của bạn đang chờ được duyệt bởi ban quản trị CLB.</small>
                    </div>
                    <form action="{{ route('student.clubs.cancel_join_request', $club->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-secondary btn-sm" onclick="return confirm('Bạn có chắc chắn muốn hủy yêu cầu tham gia CLB này?');">
                            <i class="fas fa-times me-1"></i> Hủy yêu cầu
                        </button>
                    </form>
                @else
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        <span class="badge bg-secondary">
                            <i class="fas fa-info-circle me-1"></i> Chưa tham gia
                        </span>
                        <small class="text-muted">Bạn chưa phải là thành viên của CLB này. Hãy gửi yêu cầu để tham gia!</small>
                    </div>
                    <form action="{{ route('student.clubs.join', $club->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i> Gửi yêu cầu tham gia
                        </button>
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
                @if($isMember)
                    <button class="nav-link" id="events-tab" data-bs-toggle="tab" data-bs-target="#events" type="button" role="tab" aria-controls="events" aria-selected="false">
                        <i class="fas fa-calendar-alt me-1"></i> Sự kiện
                    </button>
                @else
                    <button class="nav-link disabled" type="button" disabled title="Cần là thành viên để xem">
                        <i class="fas fa-calendar-alt me-1"></i> Sự kiện
                    </button>
                @endif
            </li>
            <li class="nav-item" role="presentation">
                @if($isMember)
                    <button class="nav-link" id="announcements-tab" data-bs-toggle="tab" data-bs-target="#announcements" type="button" role="tab" aria-controls="announcements" aria-selected="false">
                        <i class="fas fa-bullhorn me-1"></i> Thông báo
                    </button>
                @else
                    <button class="nav-link disabled" type="button" disabled title="Cần là thành viên để xem">
                        <i class="fas fa-bullhorn me-1"></i> Thông báo
                    </button>
                @endif
            </li>
            <li class="nav-item" role="presentation">
                @if($isMember)
                    <button class="nav-link" id="forum-tab" data-bs-toggle="tab" data-bs-target="#forum" type="button" role="tab" aria-controls="forum" aria-selected="false">
                        <i class="fas fa-newspaper me-1"></i> Bài viết
                    </button>
                @else
                    <button class="nav-link disabled" type="button" disabled title="Cần là thành viên để xem">
                        <i class="fas fa-newspaper me-1"></i> Bài viết
                    </button>
                @endif
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
                <!-- Giới thiệu CLB - ẩn theo yêu cầu -->

                @if($isMember)
                <!-- Thống kê nhanh -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('student.club-management.members', ['club' => $club->id]) }}"
                           class="text-decoration-none text-dark d-block">
                            <div class="content-card text-center stat-card h-100">
                                <div class="stat-icon-large bg-primary mb-2">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h4 class="mb-1">{{ $membersCount ?? 0 }}</h4>
                                <p class="text-muted mb-0">Thành viên</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="content-card text-center stat-card">
                            <div class="stat-icon-large bg-success mb-2">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h4 class="mb-1">{{ $eventsCount ?? 0 }}</h4>
                            <p class="text-muted mb-0">Sự kiện</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="content-card text-center stat-card">
                            <div class="stat-icon-large bg-info mb-2">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <h4 class="mb-1">{{ $announcementsCount ?? 0 }}</h4>
                            <p class="text-muted mb-0">Thông báo</p>
                        </div>
                    </div>
                </div>

                <!-- Hoạt động gần đây -->
                @if(isset($upcomingEvents) && $upcomingEvents->count() > 0)
                <div class="content-card">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-clock text-teal me-2"></i> Sự kiện sắp tới
                    </h5>
                    <div class="list-group list-group-flush">
                        @foreach($upcomingEvents as $event)
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
                    @if($club->events->count() > 3)
                    <div class="mt-3 text-center">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('events-tab').click();">
                            <i class="fas fa-calendar-alt me-1"></i> Xem tất cả sự kiện <i class="fas fa-arrow-right ms-1"></i>
                        </button>
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
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bullhorn text-teal me-2"></i> Thông báo mới nhất
                            </h5>
                            <a href="{{ route('student.notifications.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-bell me-1"></i> Xem tất cả thông báo
                            </a>
                        </div>
                        @if(isset($announcements) && $announcements->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($announcements as $announcement)
                                    <a href="{{ route('student.posts.show', $announcement->id) }}" class="list-group-item list-group-item-action px-0 border-0 border-bottom">
                                        <div class="d-flex w-100 justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-bold">{{ $announcement->title }}</h6>
                                                <p class="mb-2 text-muted">
                                                    @php
                                                        $content = strip_tags($announcement->content ?? '');
                                                        $content = mb_strlen($content) > 200 ? mb_substr($content, 0, 200) . '...' : $content;
                                                    @endphp
                                                    {{ $content }}
                                                </p>
                                                <div class="d-flex align-items-center gap-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-user me-1"></i> {{ $announcement->user->name ?? 'Ban chủ nhiệm' }}
                                                    </small>
                                                    <small class="text-muted">
                                                        <i class="far fa-clock me-1"></i> {{ $announcement->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </div>
                                            <i class="fas fa-chevron-right text-muted ms-2"></i>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted mb-2">Chưa có thông báo nào</h5>
                                <p class="text-muted">CLB này chưa có thông báo nào được đăng.</p>
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
                                                <i class="fas fa-user me-1"></i> {{ $post->user->name ?? 'Chưa xác định' }}
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
                            <p class="text-muted">CLB này chưa có bài viết nào được đăng.</p>
                        </div>
                        @endif
                    </div>
                </div>
            @else
            {{-- Giao diện cho người chưa phải là thành viên --}}
            <div class="content-card text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-lock fa-4x text-muted mb-3"></i>
                </div>
                <h4 class="mb-3">Nội dung dành cho thành viên</h4>
                <p class="text-muted mb-4">Bạn cần là thành viên của câu lạc bộ để xem các hoạt động, bài viết và thông tin nội bộ.</p>
                <div class="d-flex justify-content-center gap-3">
                    <form action="{{ route('student.clubs.join', $club->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i> Gửi yêu cầu tham gia ngay
                        </button>
                    </form>
                    <a href="{{ route('student.clubs.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i> Quay lại danh sách CLB
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Sidebar removed -->
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
                <p>Nếu bạn là trưởng CLB, phó CLB hoặc thủ quỹ, vui lòng đảm bảo đã bàn giao công việc trước khi rời đi.</p>
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
        overflow: hidden;
        position: relative;
    }
    
    .club-logo-img-large {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .club-logo-fallback-large {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }
    .text-teal {
        color: #14b8a6 !important;
    }
    .bg-teal {
        background-color: #14b8a6 !important;
    }
    .nav-tabs .nav-link.active {
        color: #14b8a6 !important;
        border-color: #14b8a6 #14b8a6 #fff;
        background-color: #fff !important;
        font-weight: 600;
    }
    .nav-tabs .nav-link {
        color: #6c757d !important;
    }
    .nav-tabs .nav-link:hover {
        color: #14b8a6 !important;
        border-color: #e9ecef #e9ecef #dee2e6;
    }
    .nav-tabs .nav-link.active i,
    .nav-tabs .nav-link.active {
        color: #14b8a6 !important;
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
    
    .stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .list-group-item-action:hover {
        background-color: #f8f9fa;
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
