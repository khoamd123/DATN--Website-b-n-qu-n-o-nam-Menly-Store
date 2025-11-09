@extends('layouts.student')

@section('title', 'Trang chủ - UniClubs')

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Welcome Message -->
        <div class="content-card text-center">
            <div class="profile-avatar mb-3">{{ substr($user->name, 0, 1) }}</div>
            <h3>Chào mừng, {{ $user->name }}!</h3>
            <p class="text-muted mb-4">
                <i class="fas fa-envelope me-2"></i> {{ $user->email }}<br>
                <i class="fas fa-id-card me-2"></i> {{ $user->student_id ?? 'Chưa có mã sinh viên' }}
                @if($user->clubs->count() > 0)
                    @php
                        $position = $user->getPositionInClub($user->clubs->first()->id);
                        $clubName = $user->clubs->first()->name;
                    @endphp
                    <br>
                    @if($position === 'leader')
                        <span class="badge bg-warning">
                            <i class="fas fa-crown me-1"></i> Trưởng CLB {{ $clubName }}
                        </span>
                    @elseif($position === 'vice_president')
                        <span class="badge bg-info">
                            <i class="fas fa-user-tie me-1"></i> Phó CLB {{ $clubName }}
                        </span>
                    @elseif($position === 'officer')
                        <span class="badge bg-success">
                            <i class="fas fa-user-shield me-1"></i> Cán sự {{ $clubName }}
                        </span>
                    @else
                        <span class="badge bg-secondary">
                            <i class="fas fa-user me-1"></i> Thành viên {{ $clubName }}
                        </span>
                    @endif
                @endif
            </p>
            <div class="row">
                <div class="col-md-4">
                    <div class="h3 mb-1 text-teal">{{ $user->clubs->count() }}</div>
                    <small class="text-muted">CLB đã tham gia</small>
                </div>
                <div class="col-md-4">
                    <div class="h3 mb-1 text-teal">0</div>
                    <small class="text-muted">Sự kiện đã tham gia</small>
                </div>
                <div class="col-md-4">
                    <div class="h3 mb-1 text-teal">0</div>
                    <small class="text-muted">Chứng chỉ</small>
                </div>
            </div>
        </div>

        <!-- My Clubs Section -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="fas fa-users text-teal me-2"></i> CLB của tôi
                </h4>
                <a href="{{ route('student.clubs.index') }}" class="btn btn-outline-primary">Xem tất cả</a>
            </div>
            
            @if($user->clubs->count() > 0)
                <div class="row">
                    @foreach($user->clubs as $club)
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="club-logo me-3">
                                        {{ substr($club->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-1">{{ $club->name }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-user-friends me-1"></i> {{ $club->members->count() }} thành viên
                                        </small>
                                    </div>
                                </div>
                                <p class="card-text">{{ Str::limit($club->description, 100) }}</p>
                                <a href="#" class="btn btn-sm btn-primary">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Bạn chưa tham gia câu lạc bộ nào</h5>
                    <p class="text-muted">Hãy khám phá và tham gia các câu lạc bộ thú vị!</p>
                    <a href="{{ route('student.clubs.index') }}" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i> Khám phá CLB
                    </a>
                </div>
            @endif
        </div>

        <!-- Club Management Section (for leaders/officers) -->
        @php
            $hasManagementRole = false;
            if ($user->clubs->count() > 0) {
                $clubId = $user->clubs->first()->id;
                $position = $user->getPositionInClub($clubId);
                $hasManagementRole = in_array($position, ['leader', 'vice_president', 'officer']);
            }
        @endphp
        @if($hasManagementRole)
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="fas fa-crown text-warning me-2"></i> Quản lý CLB
                </h4>
                <a href="{{ route('student.club-management.index') }}" class="btn btn-warning">Quản lý</a>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="management-icon mb-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <h6 class="card-title">Quản lý thành viên</h6>
                            <p class="card-text small text-muted">Duyệt đơn đăng ký, quản lý thành viên</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="management-icon mb-3">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <h6 class="card-title">Tạo sự kiện</h6>
                            <p class="card-text small text-muted">Tổ chức và quản lý sự kiện</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="management-icon mb-3">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <h6 class="card-title">Đăng thông báo</h6>
                            <p class="card-text small text-muted">Gửi thông báo đến thành viên</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Upcoming Events Section -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="fas fa-calendar-day text-teal me-2"></i> Sự kiện sắp tới
                </h4>
                <a href="{{ route('student.events.index') }}" class="btn btn-outline-primary">Xem tất cả</a>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="event-icon me-3">
                                    <i class="fas fa-laptop-code"></i>
                                </div>
                                <div>
                                    <h6 class="card-title mb-1">Workshop "Lập trình Web hiện đại"</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i> 15/01/2024, 09:00 - 12:00
                                    </small>
                                </div>
                            </div>
                            <p class="card-text">Tìm hiểu về các công nghệ mới nhất trong phát triển web.</p>
                            <button class="btn btn-sm btn-primary">Đăng ký</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="event-icon me-3">
                                    <i class="fas fa-gamepad"></i>
                                </div>
                                <div>
                                    <h6 class="card-title mb-1">Game Jam 2024</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i> 20/01/2024, 14:00 - 18:00
                                    </small>
                                </div>
                            </div>
                            <p class="card-text">Cơ hội để các bạn thể hiện tài năng làm game của mình.</p>
                            <button class="btn btn-sm btn-primary">Đăng ký</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="sidebar">
            <h5 class="sidebar-title">
                <i class="fas fa-bell"></i> Thông báo mới
            </h5>
            <div class="sidebar-item">
                <div class="sidebar-icon bg-primary text-white">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <div class="fw-bold">Chào mừng thành viên mới!</div>
                    <small class="text-muted">Bạn đã tham gia UniClubs.</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon bg-success text-white">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="fw-bold">Sự kiện mới</div>
                    <small class="text-muted">Workshop "Lập trình Web hiện đại".</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon bg-warning text-white">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <div class="fw-bold">Game Jam sắp hết hạn</div>
                    <small class="text-muted">Đăng ký ngay để không bỏ lỡ.</small>
                </div>
            </div>
        </div>

        <div class="sidebar mt-4">
            <h5 class="sidebar-title">
                <i class="fas fa-chart-line"></i> Thống kê cá nhân
            </h5>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="fw-bold">{{ $user->clubs->count() }}</div>
                    <small class="text-muted">CLB đã tham gia</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div class="fw-bold">0</div>
                    <small class="text-muted">Sự kiện đã tham gia</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div>
                    <div class="fw-bold">0</div>
                    <small class="text-muted">Giải thưởng</small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #14b8a6;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: bold;
        margin: 0 auto;
    }
    
    .club-logo, .event-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #14b8a6;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1rem;
    }
    
    .text-teal {
        color: #14b8a6 !important;
    }
    
    .management-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #f0fdfa;
        color: #14b8a6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto;
    }
</style>
@endpush
@endsection