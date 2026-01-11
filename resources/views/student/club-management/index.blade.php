@extends('layouts.student')

@section('title', 'Quản lý CLB - UniClubs')

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-12">
        <!-- Page Header -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-crown text-warning"></i> Quản lý CLB
                    </h2>
                    <p class="text-muted mb-0">Công cụ quản lý dành cho trưởng CLB, phó CLB và thủ quỹ</p>
                </div>
                <div class="user-role-badge">
                    @if($userPosition === 'leader')
                        <span class="badge bg-warning">
                            <i class="fas fa-crown me-1"></i> Trưởng CLB
                        </span>
                    @elseif($userPosition === 'vice_president')
                        <span class="badge bg-info">
                            <i class="fas fa-user-tie me-1"></i> Phó CLB
                        </span>
                    @elseif($userPosition === 'treasurer')
                        <span class="badge bg-success">
                            <i class="fas fa-wallet me-1"></i> Thủ quỹ
                        </span>
                    @elseif($userPosition === 'member')
                        <span class="badge bg-secondary">
                            <i class="fas fa-user me-1"></i> Thành viên
                        </span>
                    @else
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-question me-1"></i> Chưa tham gia CLB
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(isset($search) && !empty($search))
            <div class="content-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">
                        <i class="fas fa-search text-primary me-2"></i> Kết quả tìm kiếm cho "{{ $search }}"
                    </h4>
                    <a href="{{ route('student.club-management.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Xóa tìm kiếm
                    </a>
                </div>

                @php
                    $totalResults = ($clubMembers->count() ?? 0) + ($searchEvents->count() ?? 0) + ($searchPosts->count() ?? 0);
                @endphp

                @if($totalResults > 0)
                    <div class="row">
                        @if(isset($clubMembers) && $clubMembers->count() > 0)
                            <div class="col-12 mb-3">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-users me-2"></i> Thành viên ({{ $clubMembers->count() }})
                                </h6>
                                <div class="list-group">
                                    @foreach($clubMembers->take(5) as $member)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                <strong>{{ $member->user->name ?? 'Chưa xác định' }}</strong>
                                                    <span class="badge bg-secondary ms-2">{{ ucfirst($member->position) }}</span>
                                                </div>
                                                <a href="{{ route('student.club-management.members', ['club' => $clubId]) }}" class="btn btn-sm btn-outline-primary">
                                                    Xem chi tiết
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(isset($searchEvents) && $searchEvents->count() > 0)
                            <div class="col-12 mb-3">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-calendar me-2"></i> Sự kiện ({{ $searchEvents->count() }})
                                </h6>
                                <div class="list-group">
                                    @foreach($searchEvents as $event)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $event->title }}</strong>
                                                    <small class="text-muted d-block">{{ $event->start_time ? $event->start_time->format('d/m/Y H:i') : 'Chưa cập nhật' }}</small>
                                                </div>
                                                <a href="{{ route('student.events.show', $event->id) }}" class="btn btn-sm btn-outline-primary">
                                                    Xem chi tiết
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(isset($searchPosts) && $searchPosts->count() > 0)
                            <div class="col-12 mb-3">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-newspaper me-2"></i> Bài viết ({{ $searchPosts->count() }})
                                </h6>
                                <div class="list-group">
                                    @foreach($searchPosts as $post)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $post->title }}</strong>
                                                    <small class="text-muted d-block">{{ $post->created_at->format('d/m/Y H:i') }}</small>
                                                </div>
                                                <a href="{{ route('student.posts.show', $post->id) }}" class="btn btn-sm btn-outline-primary">
                                                    Xem chi tiết
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Không tìm thấy kết quả</h5>
                        <p class="text-muted">Không có thành viên, sự kiện hoặc bài viết nào phù hợp với từ khóa "{{ $search }}"</p>
                    </div>
                @endif
            </div>
        @endif

        @if(!$hasManagementRole)
        <!-- Access Denied Message -->
        <div class="content-card">
            <div class="text-center py-5">
                <div class="access-denied-icon mb-4">
                    <i class="fas fa-lock text-muted" style="font-size: 4rem;"></i>
                </div>
                <h4 class="mb-3">
                    @if($user->clubs->count() == 0)
                        Bạn chưa tham gia CLB nào
                    @else
                        Bạn không có quyền quản lý CLB
                    @endif
                </h4>
                
                @if($user->clubs->count() == 0)
                    <p class="text-muted mb-4">Để quản lý CLB, bạn cần tham gia hoặc tạo một CLB trước.</p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="{{ route('student.clubs.index') }}" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i> Tìm CLB để tham gia
                        </a>
                        <a href="{{ route('student.clubs.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i> Tạo CLB mới
                        </a>
                    </div>
                @else
                    <p class="text-muted mb-4">
                        Chỉ <strong>Trưởng CLB</strong>, <strong>Phó CLB</strong> và <strong>Thủ quỹ</strong> mới có thể quản lý CLB.<br>
                        Vai trò hiện tại của bạn: <strong>
                            @php
                                $roleMap = [
                                    'leader' => 'Trưởng CLB',
                                    'vice_president' => 'Phó CLB',
                                    'treasurer' => 'Thủ quỹ',
                                    'member' => 'Thành viên',
                                    'owner' => 'Chủ nhiệm',
                                ];
                            @endphp
                            {{ $roleMap[$userPosition] ?? 'Thành viên' }}
                        </strong>
                    </p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="{{ route('student.clubs.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-users me-2"></i> Xem CLB của tôi
                        </a>
                        <a href="{{ route('student.clubs.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Tạo CLB mới
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @else

        <!-- Management Cards -->
        <div class="row">
            @if($userClub && $clubId && $user->hasPermission('quan_ly_thanh_vien', $clubId))
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
                                <strong>{{ data_get($clubStats, 'members.active', 0) }}</strong>
                                <small>Thành viên</small>
                            </span>
                            <span class="stat-item">
                                    <strong>{{ data_get($clubStats, 'members.pending', 0) }}</strong>
                                <small>Đang chờ</small>
                            </span>
                        </div>
                        <a href="{{ $clubId ? route('student.club-management.members', ['club' => $clubId]) : '#' }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i> Quản lý
                        </a>
                    </div>
                </div>
            </div>
            @endif
            @if($userClub && $clubId && $user->hasPermission('tao_su_kien', $clubId))
            <div class="col-md-6 mb-4">
                <div class="management-card">
                    <div class="management-icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="management-content">
                        <h5 class="management-title">Quản lý sự kiện</h5>
                        <p class="management-description">Tổ chức và quản lý các sự kiện của CLB</p>
                        <div class="management-stats">
                            <span class="stat-item">
                                <strong>{{ data_get($clubStats, 'events.total', 0) }}</strong>
                                <small>Sự kiện</small>
                            </span>
                            <span class="stat-item">
                                <strong>{{ data_get($clubStats, 'events.upcoming', 0) }}</strong>
                                <small>Sắp tới</small>
                            </span>
                        </div>
                        <a href="{{ route('student.events.manage') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i> Quản lý
                        </a>
                    </div>
                </div>
            </div>
            @endif
            @if($userClub && $clubId && $user->hasPermission('dang_thong_bao', $clubId))
            <div class="col-md-6 mb-4">
                <div class="management-card">
                    <div class="management-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="management-content">
                        <h5 class="management-title">Quản lý bài viết</h5>
                        <p class="management-description">Tạo và quản lý bài viết, thông báo của CLB</p>
                        <div class="management-stats">
                            <span class="stat-item">
                                <strong>{{ data_get($clubStats, 'posts', 0) }}</strong>
                                <small>Bài viết</small>
                            </span>
                            <span class="stat-item">
                                <strong>{{ data_get($clubStats, 'announcements.total', 0) }}</strong>
                                <small>Thông báo</small>
                            </span>
                        </div>
                        <a href="{{ route('student.club-management.posts', ['club' => $clubId]) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i> Quản lý
                        </a>
                    </div>
                </div>
            </div>
            @endif
            @if($userClub && $clubId && $user->hasPermission('xem_bao_cao', $clubId))
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
                                <strong>{{ data_get($clubStats, 'members.active', 0) }}</strong>
                                <small>Thành viên</small>
                            </span>
                            <span class="stat-item">
                                <strong>{{ data_get($clubStats, 'events.total', 0) }}</strong>
                                <small>Sự kiện</small>
                            </span>
                        </div>
                        <a href="{{ route('student.club-management.reports', ['club' => $clubId]) }}" class="btn btn-primary btn-sm text-white">
                            <i class="fas fa-chart-line me-1"></i> Xem báo cáo
                        </a>
                    </div>
                </div>
            </div>
            @endif
            @php
                // Lấy position - ưu tiên từ biến đã được tính sẵn trong controller
                $currentPosition = $userPosition ?? null;
                
                // Nếu chưa có position và có clubId, lấy từ model
                if (!$currentPosition && $clubId) {
                    $currentPosition = $user->getPositionInClub($clubId);
                }
                
                // Kiểm tra quyền quản lý quỹ
                // Leader và Treasurer luôn có quyền quản lý quỹ (theo logic business)
                // Logic: Chỉ Leader và Treasurer mới có quyền quản lý quỹ
                $canManageFund = false;
                if ($clubId && $currentPosition) {
                    // Leader và Treasurer có quyền quản lý quỹ
                    $canManageFund = in_array($currentPosition, ['leader', 'treasurer']);
                }
                
                $fundExists = data_get($clubStats, 'fund.exists', false);
            @endphp
            @if($userClub && $clubId && $canManageFund)
            <div class="col-md-6 mb-4">
                <div class="management-card" style="border-color: #f59e0b;">
                    <div class="management-icon" style="background: #fef3c7; color: #f59e0b;">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="management-content">
                        <h5 class="management-title">
                            <i class="fas fa-wallet me-1"></i> Quản lý quỹ
                        </h5>
                        <p class="management-description">
                            @if($fundExists)
                                Quản lý quỹ CLB, giao dịch và yêu cầu cấp kinh phí
                            @else
                                Tạo và quản lý quỹ CLB, theo dõi giao dịch tài chính
                            @endif
                        </p>
                        @if($fundExists)
                            <div class="management-stats">
                                <span class="stat-item">
                                    <strong>{{ number_format(data_get($clubStats, 'fund.balance', 0), 0, ',', '.') }}</strong>
                                    <small>VNĐ (Số dư)</small>
                                </span>
                                <span class="stat-item">
                                    <strong>{{ number_format(data_get($clubStats, 'fund.income', 0), 0, ',', '.') }}</strong>
                                    <small>VNĐ (Thu)</small>
                                </span>
                            </div>
                        @endif
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('student.club-management.fund-transactions') }}?club={{ $clubId }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-wallet me-1"></i> 
                                {{ $fundExists ? 'Giao dịch quỹ' : 'Tạo quỹ CLB' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if($userClub && $clubId && $user->hasPermission('quan_ly_clb', $clubId) && $user->getPositionInClub($clubId) === 'leader')
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
                                <strong>{{ data_get($clubStats, 'members.active', 0) }}</strong>
                                <small>Thành viên</small>
                            </span>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ $clubId ? route('student.club-management.settings', ['club' => $clubId]) : '#' }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit me-1"></i> Cài đặt
                            </a>
                            <a href="{{ route('student.club-management.payment-qr', ['club' => $clubId]) }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-qrcode me-1"></i> Quản lý QR Code
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Phân quyền chi tiết đã chuyển sang trang riêng --}}
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
        min-width: 0;
        overflow: hidden;
    }
    
    .management-content .d-flex {
        flex-wrap: wrap;
        gap: 0.5rem;
        width: 100%;
        max-width: 100%;
    }
    
    .management-content .btn {
        white-space: nowrap;
        flex: 0 1 auto;
        max-width: 100%;
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    
    .management-content .btn-group {
        width: 100%;
        max-width: 100%;
    }
    
    .management-content .dropdown-menu {
        position: absolute;
        z-index: 1000;
        min-width: 200px;
    }
    
    @media (max-width: 576px) {
        .management-content .btn {
            width: 100%;
            flex: 1 1 100%;
        }
        
        .management-content .btn-group {
            flex-direction: column;
        }
        
        .management-content .dropdown-toggle-split {
            width: 100%;
        }
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
    
    .permission-item a {
        transition: color 0.2s ease;
    }
    
    .permission-item a:hover {
        color: #14b8a6 !important;
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

@endif
@endsection
