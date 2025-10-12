@extends('layouts.student')

@section('title', 'Quản lý CLB - UniClubs')

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Page Header -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-crown text-warning"></i> Quản lý CLB
                    </h2>
                    <p class="text-muted mb-0">Công cụ quản lý dành cho trưởng CLB và cán sự</p>
                </div>
                <div class="user-role-badge">
                    @php
                        $userRole = $user->getPositionInClub($user->clubs->first()->id ?? null);
                    @endphp
                    @if($userRole === 'leader')
                        <span class="badge bg-warning">
                            <i class="fas fa-crown me-1"></i> Trưởng CLB
                        </span>
                    @elseif($userRole === 'vice_president')
                        <span class="badge bg-info">
                            <i class="fas fa-user-tie me-1"></i> Phó CLB
                        </span>
                    @elseif($userRole === 'officer')
                        <span class="badge bg-success">
                            <i class="fas fa-user-shield me-1"></i> Cán sự
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Management Cards -->
        <div class="row">
            @if($user->hasPermission('quan_ly_thanh_vien'))
            <div class="col-md-6 mb-4">
                <div class="management-card">
                    <div class="management-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="management-content">
                        <h5 class="management-title">Quản lý thành viên</h5>
                        <p class="management-description">Duyệt đơn đăng ký, quản lý thành viên CLB</p>
                        <div class="management-stats">
                            <span class="stat-item">
                                <strong>{{ $user->clubs->first()->members->count() ?? 0 }}</strong>
                                <small>Thành viên</small>
                            </span>
                            <span class="stat-item">
                                <strong>0</strong>
                                <small>Đang chờ</small>
                            </span>
                        </div>
                        <a href="#" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i> Quản lý
                        </a>
                    </div>
                </div>
            </div>
            @endif

            @if($user->hasPermission('tao_su_kien'))
            <div class="col-md-6 mb-4">
                <div class="management-card">
                    <div class="management-icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="management-content">
                        <h5 class="management-title">Tạo sự kiện</h5>
                        <p class="management-description">Tổ chức và quản lý các sự kiện của CLB</p>
                        <div class="management-stats">
                            <span class="stat-item">
                                <strong>0</strong>
                                <small>Sự kiện</small>
                            </span>
                            <span class="stat-item">
                                <strong>0</strong>
                                <small>Sắp tới</small>
                            </span>
                        </div>
                        <a href="#" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Tạo mới
                        </a>
                    </div>
                </div>
            </div>
            @endif

            @if($user->hasPermission('dang_thong_bao'))
            <div class="col-md-6 mb-4">
                <div class="management-card">
                    <div class="management-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="management-content">
                        <h5 class="management-title">Đăng thông báo</h5>
                        <p class="management-description">Gửi thông báo đến tất cả thành viên CLB</p>
                        <div class="management-stats">
                            <span class="stat-item">
                                <strong>0</strong>
                                <small>Đã gửi</small>
                            </span>
                            <span class="stat-item">
                                <strong>0</strong>
                                <small>Hôm nay</small>
                            </span>
                        </div>
                        <a href="#" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i> Viết thông báo
                        </a>
                    </div>
                </div>
            </div>
            @endif

            @if($user->hasPermission('xem_bao_cao'))
            <div class="col-md-6 mb-4">
                <div class="management-card">
                    <div class="management-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="management-content">
                        <h5 class="management-title">Báo cáo & Thống kê</h5>
                        <p class="management-description">Xem báo cáo hoạt động và thống kê CLB</p>
                        <div class="management-stats">
                            <span class="stat-item">
                                <strong>{{ $user->clubs->first()->members->count() ?? 0 }}</strong>
                                <small>Thành viên</small>
                            </span>
                            <span class="stat-item">
                                <strong>0</strong>
                                <small>Sự kiện</small>
                            </span>
                        </div>
                        <a href="#" class="btn btn-primary btn-sm">
                            <i class="fas fa-chart-line me-1"></i> Xem báo cáo
                        </a>
                    </div>
                </div>
            </div>
            @endif

            @if($user->hasPermission('quan_ly_clb') && $user->getPositionInClub($user->clubs->first()->id ?? null) === 'leader')
            <div class="col-md-6 mb-4">
                <div class="management-card special-card">
                    <div class="management-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div class="management-content">
                        <h5 class="management-title">Cài đặt CLB</h5>
                        <p class="management-description">Chỉnh sửa thông tin, quyền hạn và cài đặt CLB</p>
                        <div class="management-stats">
                            <span class="stat-item">
                                <strong>1</strong>
                                <small>CLB</small>
                            </span>
                            <span class="stat-item">
                                <strong>{{ $user->clubs->first()->members->count() ?? 0 }}</strong>
                                <small>Thành viên</small>
                            </span>
                        </div>
                        <a href="#" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit me-1"></i> Cài đặt
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Recent Activity -->
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-history text-teal me-2"></i> Hoạt động gần đây
            </h4>
            
            <div class="activity-list">
                <div class="activity-item">
                    <div class="activity-icon bg-primary">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="activity-content">
                        <h6 class="activity-title">Thành viên mới tham gia</h6>
                        <p class="activity-description">Nguyễn Văn A đã tham gia CLB Công nghệ thông tin</p>
                        <small class="activity-time">
                            <i class="fas fa-clock me-1"></i> 2 giờ trước
                        </small>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon bg-success">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="activity-content">
                        <h6 class="activity-title">Sự kiện mới được tạo</h6>
                        <p class="activity-description">Workshop "Lập trình Web hiện đại" đã được tạo</p>
                        <small class="activity-time">
                            <i class="fas fa-clock me-1"></i> 1 ngày trước
                        </small>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon bg-info">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="activity-content">
                        <h6 class="activity-title">Thông báo mới</h6>
                        <p class="activity-description">Thông báo về buổi họp mặt định kỳ đã được gửi</p>
                        <small class="activity-time">
                            <i class="fas fa-clock me-1"></i> 2 ngày trước
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="sidebar">
            <h5 class="sidebar-title">
                <i class="fas fa-shield-alt"></i> Quyền hạn của bạn
            </h5>
            <div class="permissions-list">
                @if($user->hasPermission('quan_ly_thanh_vien'))
                <div class="permission-item">
                    <i class="fas fa-users text-success"></i>
                    <span>Quản lý thành viên</span>
                </div>
                @endif
                @if($user->hasPermission('tao_su_kien'))
                <div class="permission-item">
                    <i class="fas fa-calendar-plus text-success"></i>
                    <span>Tạo sự kiện</span>
                </div>
                @endif
                @if($user->hasPermission('dang_thong_bao'))
                <div class="permission-item">
                    <i class="fas fa-bullhorn text-success"></i>
                    <span>Đăng thông báo</span>
                </div>
                @endif
                @if($user->hasPermission('xem_bao_cao'))
                <div class="permission-item">
                    <i class="fas fa-chart-bar text-success"></i>
                    <span>Xem báo cáo</span>
                </div>
                @endif
                @if($user->hasPermission('quan_ly_clb'))
                <div class="permission-item">
                    <i class="fas fa-cogs text-warning"></i>
                    <span>Quản lý CLB</span>
                </div>
                @endif
            </div>
        </div>

        <div class="sidebar mt-4">
            <h5 class="sidebar-title">
                <i class="fas fa-chart-pie"></i> Thống kê CLB
            </h5>
            <div class="club-stats">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="stat-number">{{ $user->clubs->first()->members->count() ?? 0 }}</div>
                        <div class="stat-label">Thành viên</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div>
                        <div class="stat-number">0</div>
                        <div class="stat-label">Sự kiện</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div>
                        <div class="stat-number">0</div>
                        <div class="stat-label">Thông báo</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .user-role-badge .badge {
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
    }
    
    .management-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .management-card:hover {
        box-shadow: 0 4px 12px rgba(20, 184, 166, 0.15);
        border-color: #14b8a6;
        transform: translateY(-2px);
    }
    
    .management-card.special-card {
        border-color: #fbbf24;
        background: linear-gradient(135deg, #fef3c7 0%, #ffffff 100%);
    }
    
    .management-card.special-card:hover {
        border-color: #f59e0b;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
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
        flex-shrink: 0;
    }
    
    .special-card .management-icon {
        background: #fef3c7;
        color: #f59e0b;
    }
    
    .management-content {
        flex-grow: 1;
    }
    
    .management-title {
        color: #333;
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
    }
    
    .management-description {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        line-height: 1.4;
    }
    
    .management-stats {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-item strong {
        display: block;
        font-size: 1.2rem;
        color: #14b8a6;
    }
    
    .stat-item small {
        color: #666;
        font-size: 0.8rem;
    }
    
    .activity-list {
        space-y: 1rem;
    }
    
    .activity-item {
        display: flex;
        align-items: flex-start;
        padding: 1rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .activity-content {
        flex-grow: 1;
    }
    
    .activity-title {
        color: #333;
        font-weight: 600;
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
    }
    
    .activity-description {
        color: #666;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    
    .activity-time {
        color: #999;
        font-size: 0.8rem;
    }
    
    .permissions-list {
        space-y: 0.5rem;
    }
    
    .permission-item {
        display: flex;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .permission-item:last-child {
        border-bottom: none;
    }
    
    .permission-item i {
        margin-right: 0.75rem;
        width: 20px;
    }
    
    .club-stats .stat-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .club-stats .stat-item:last-child {
        border-bottom: none;
    }
    
    .stat-icon {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: #f0fdfa;
        color: #14b8a6;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1rem;
    }
    
    .stat-number {
        font-size: 1.2rem;
        font-weight: bold;
        color: #14b8a6;
    }
    
    .stat-label {
        color: #666;
        font-size: 0.8rem;
    }
    
    .text-teal {
        color: #14b8a6 !important;
    }
</style>
@endpush
@endsection
