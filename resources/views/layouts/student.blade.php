<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UniClubs - Sinh viên')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0fdfa;
            min-height: 100vh;
        }
        
        /* Header */
        .main-header {
            background: #14b8a6;
            color: white;
            padding: 0.85rem 0;
            box-shadow: 0 2px 4px rgba(20, 184, 166, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #0d9488;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.2s ease;
            overflow: hidden;
            object-fit: cover;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .dropdown-toggle::after {
            display: none;
        }
        
        .dropdown-menu {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid #e5e7eb;
            margin-top: 0.5rem;
        }
        
        .dropdown-item {
            padding: 0.75rem 1.25rem;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background: #f0fdfa;
            color: #14b8a6;
        }
        
        .dropdown-item i {
            width: 20px;
        }
        
        /* Notification Icon */
        .notification-icon {
            position: relative;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .notification-icon:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: scale(1.1);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            min-width: 20px;
            height: 20px;
            font-size: 0.7rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #14b8a6;
            padding: 0 4px;
            z-index: 10;
        }
        
        /* Navigation */
        .main-nav {
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(20, 184, 166, 0.1);
            padding: 0.5rem 0;
            border-bottom: 1px solid #a7f3d0;
        }
        
        .nav-link {
            color: #6b7280;
            font-weight: 500;
            padding: 0.45rem 1rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
            font-size: 0.95rem;
        }
        
        /* .nav-link:hover {
            color: #14b8a6;
            background: #f0fdfa;
        } */
        
        .nav-link.active {
            background: rgba(255,255,255,0.2) !important;
            color: white !important;
            border-radius: 8px;
        }
        
        /* Main Content */
        .main-content {
            min-height: calc(100vh - 140px);
            padding: 2rem 0;
        }
        
        /* Sidebar */
        .sidebar {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(20, 184, 166, 0.1);
            border: 1px solid #a7f3d0;
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .sidebar-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #0d9488;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #a7f3d0;
        }
        
        .sidebar-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
        }
        
        .sidebar-item:last-child {
            border-bottom: none;
        }
        
        .sidebar-icon {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            background: #f0fdfa;
            color: #14b8a6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            font-size: 0.9rem;
        }
        
        /* Cards */
        .content-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(20, 184, 166, 0.1);
            border: 1px solid #a7f3d0;
            margin-bottom: 2rem;
        }
        
        /* Buttons */
        .btn-primary {
            background: #14b8a6;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: #0d9488;
            transform: translateY(-1px);
        }
        
        .btn-outline-primary {
            border: 1px solid #14b8a6;
            color: #14b8a6;
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-outline-primary:hover {
            background: #14b8a6;
            color: white;
        }
        
        .text-teal {
            color: #14b8a6 !important;
        }
        
        /* Footer */
        .main-footer {
            background: #ffffff;
            color: #333;
            padding: 1rem 0;
            margin-top: 1.3rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer-brand {
            padding-right: 0.7rem;
        }
        
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 0.2rem;
        }
        
        .logo-fpt {
            background: linear-gradient(45deg, #0066cc, #00cc66);
            color: white;
            padding: 0.3rem 0.5rem;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.75rem;
        }
        
        .logo-education {
            color: #0066cc;
            font-weight: 600;
            font-size: 0.7rem;
        }
        
        .brand-name {
            color: #ff6600;
            font-weight: bold;
            font-size: 1.2rem;
            margin: 0.33rem 0;
            letter-spacing: 0.7px;
        }
        
        .address-info {
            color: #666;
            font-size: 0.7rem;
            line-height: 1.4;
        }
        
        .contact-info {
            padding-left: 0.7rem;
            border-left: 1px solid #e5e7eb;
        }
        
        .contact-title {
            color: #333;
            font-weight: bold;
            margin-bottom: 0.7rem;
            font-size: 0.8rem;
        }
        
        .contact-item {
            margin-bottom: 0.33rem;
            display: flex;
            align-items: flex-start;
            gap: 0.2rem;
        }
        
        .contact-item i {
            color: #14b8a6;
            margin-top: 0.07rem;
            font-size: 0.65rem;
        }
        
        .department-list {
            margin-top: 0.33rem;
        }
        
        .department-item {
            margin-bottom: 0.33rem;
            padding-left: 0.7rem;
        }
        
        .department-item strong {
            color: #333;
            display: block;
            margin-bottom: 0.2rem;
            font-size: 0.7rem;
        }
        
        .sub-department {
            margin-left: 0.53rem;
            margin-top: 0.2rem;
        }
        
        .sub-department div {
            margin-bottom: 0.13rem;
            color: #666;
            font-size: 0.65rem;
        }
        
        .email {
            font-weight: bold;
            color: #14b8a6;
            font-size: 0.65rem;
        }
        
        .feedback-info {
            margin-top: 0.7rem;
            padding-top: 0.53rem;
            border-top: 1px solid #e5e7eb;
            color: #666;
            font-size: 0.6rem;
            line-height: 1.4;
        }
        
        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                margin-top: 2rem;
                position: static;
            }
        }

        /* Header search tweak */
        .header-search .input-group {
            max-width: 260px;
            margin: 0 auto;
        }
        .header-search .form-control {
            padding: 0.5rem 0.75rem;
        }
        /* Đồng bộ chiều cao ô tìm kiếm với nút bên cạnh */
        .header-search .form-control,
        .header-search .btn {
            height: 38px;
        }
        .header-search .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Header với menu bên trong -->
    <header class="main-header">
        <div class="container">
            <div class="row align-items-center g-3 flex-nowrap" style="flex-wrap: nowrap;">
                <div class="col-auto flex-shrink-0">
                    <a href="{{ route('home') }}" class="text-white text-decoration-none d-flex align-items-center" style="font-size: 1.9rem; margin-left: -30px;">
                        <i class="fas fa-graduation-cap me-2"></i> UniClubs
                    </a>
                </div>

                <!-- Navigation moved up to same row as search -->
                <div class="col-auto flex-shrink-0">
                    <ul class="nav align-items-center" style="flex-wrap: nowrap; gap: 0.5rem;">
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}" style="color: white !important;">
                                <i class="fas fa-home me-2"></i> Trang chủ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('student.clubs*') ? 'active' : '' }}" href="{{ route('student.clubs.index') }}" style="color: white !important;">
                                <i class="fas fa-users me-2"></i> Câu lạc bộ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('student.events*') ? 'active' : '' }}" href="{{ route('student.events.index') }}" style="color: white !important;">
                                <i class="fas fa-calendar-alt me-2"></i> Sự kiện
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('student.posts*') ? 'active' : '' }}" href="{{ route('student.posts') }}" style="color: white !important;">
                                <i class="fas fa-newspaper me-2"></i> Bài viết
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('student.contact*') ? 'active' : '' }}" href="{{ route('student.contact.index') }}" style="color: white !important;">
                                <i class="fas fa-phone me-2"></i> Liên hệ
                            </a>
                        </li>
                        @php
                            $hasManagementRole = false;
                            $currentUser = isset($user) ? $user : (session('user_id') ? \App\Models\User::find(session('user_id')) : null);
                            if ($currentUser) {
                                $hasManagementRole = \App\Models\ClubMember::where('user_id', $currentUser->id)
                                    ->whereIn('status', ['approved', 'active'])
                                    ->whereIn('position', ['leader', 'vice_president', 'treasurer'])
                                    ->exists();
                            }
                        @endphp
                        @if($hasManagementRole)
                        <li class="nav-item">
                            <a class="nav-link text-white {{ request()->routeIs('student.club-management*') ? 'active' : '' }}" href="{{ route('student.club-management.index') }}" style="color: white !important;">
                                <i class="fas fa-crown me-2"></i> Quản lý CLB
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>

                <div class="col-auto flex-grow-1">
                    @php
                        $currentRoute = request()->route()->getName();
                        $searchRoute = 'student.posts';
                        $searchPlaceholder = 'Tìm kiếm...';
                        
                        if (str_contains($currentRoute, 'club-management')) {
                            // Trang quản lý CLB - tìm kiếm trong chính trang đó
                            $searchRoute = 'student.club-management.index';
                            $searchPlaceholder = 'Tìm kiếm thành viên, sự kiện, bài viết...';
                        } elseif (str_contains($currentRoute, 'clubs')) {
                            $searchRoute = 'student.clubs.index';
                            $searchPlaceholder = 'Tìm kiếm câu lạc bộ...';
                        } elseif (str_contains($currentRoute, 'events')) {
                            $searchRoute = 'student.events.index';
                            $searchPlaceholder = 'Tìm kiếm sự kiện...';
                        } elseif (str_contains($currentRoute, 'posts')) {
                            $searchRoute = 'student.posts';
                            $searchPlaceholder = 'Tìm kiếm bài viết...';
                        } elseif (str_contains($currentRoute, 'home')) {
                            $searchRoute = 'home';
                            $searchPlaceholder = 'Tìm kiếm...';
                        }
                    @endphp
                    <div class="d-flex align-items-center gap-3">
                        <form method="GET" action="{{ route($searchRoute) }}" class="d-flex header-search w-100">
                        <div class="input-group">
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="{{ $searchPlaceholder }}" 
                                   value="{{ request('search') }}"
                                   style="border-radius: 8px 0 0 8px;">
                            <button type="submit" class="btn btn-light border-start-0" style="border-radius: 0 8px 8px 0;">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                        <div class="d-flex align-items-center justify-content-end flex-shrink-0">
                        @if(isset($user) && $user)
                        <!-- Notification Bell -->
                        <a href="{{ route('student.notifications.index') }}" class="notification-icon me-3 position-relative text-white text-decoration-none">
                            <i class="fas fa-bell fa-lg"></i>
                            @php
                                $unreadAnnouncementCount = 0;
                                try {
                                    // Lấy thông báo có target là user hiện tại và chưa được đọc
                                    $unreadCount = \App\Models\Notification::whereHas('targets', function($query) use ($user) {
                                        $query->where('target_type', 'user')
                                              ->where('target_id', $user->id);
                                    })->whereDoesntHave('reads', function($query) use ($user) {
                                        $query->where('user_id', $user->id)
                                              ->where('is_read', true);
                                    })->count();
                                    $unreadAnnouncementCount = $unreadCount;
                                } catch (\Exception $e) {
                                    $unreadAnnouncementCount = 0;
                                }
                            @endphp
                            @if($unreadAnnouncementCount > 0)
                                <span class="notification-badge">{{ $unreadAnnouncementCount > 99 ? '99+' : $unreadAnnouncementCount }}</span>
                            @endif
                        </a>
                        
                        <!-- User Profile Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-link text-white text-decoration-none p-0 d-flex align-items-center" 
                                    type="button" 
                                    id="userDropdown" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false"
                                    style="border: none; background: none;">
                                @php
                                    $userAvatar = optional($user)->avatar;
                                    $defaultAvatar = '/images/avatar/avatar.png';
                                    $hasCustomAvatar = $userAvatar && $userAvatar !== $defaultAvatar && $userAvatar !== ltrim($defaultAvatar, '/') && $userAvatar !== 'images/avatar/avatar.png';
                                    $avatarUrl = $hasCustomAvatar ? (optional($user)->avatar_url ?? asset('images/avatar/avatar.png')) : null;
                                @endphp
                                @if($hasCustomAvatar && $avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="{{ $user->name ?? 'User' }}" class="user-avatar">
                                @else
                                    <div class="user-avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</div>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('student.profile.index') }}">
                                        <i class="fas fa-user-circle me-2 text-teal"></i> Hồ sơ
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                        @else
                        <div class="dropdown">
                            <button class="btn btn-link text-white text-decoration-none p-0 d-flex align-items-center" 
                                    type="button" 
                                    id="userDropdown" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false"
                                    style="border: none; background: none;">
                                <div class="user-avatar">U</div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('student.profile.index') }}">
                                        <i class="fas fa-user-circle me-2 text-teal"></i> Hồ sơ
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                        @endif
                    </div>
        </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="footer-brand">
                        <div class="brand-logo mb-3">
                            <span class="logo-fpt">FPT</span>
                            <span class="logo-education">Education</span>
                        </div>
                        <h3 class="brand-name">UNICLUBS</h3>
                        <div class="address-info">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <span>Tòa nhà FPT Polytechnic, Phố Trịnh Văn Bô, Nam Từ Liêm, Hà Nội.</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="contact-info">
                        <h5 class="contact-title">Thông tin liên hệ</h5>
                        
                        <div class="contact-item">
                            <i class="fas fa-phone me-2"></i>
                            <span>Số điện thoại liên hệ giải đáp ý kiến sinh viên:</span>
                            <strong>1900996686</strong>
                        </div>
                        
                        <div class="contact-item">
                            <i class="fas fa-envelope me-2"></i>
                            <span>Địa chỉ email các phòng ban:</span>
                        </div>
                        
                        <div class="department-list">
                            <div class="department-item">
                                <strong>Phòng dịch vụ sinh viên:</strong>
                                <span class="email">dvsvpoly.hn@poly.edu.vn</span>
                            </div>
                            <div class="department-item">
                                <strong>Phòng Tổ chức và quản lý đào tạo:</strong>
                                <div class="sub-department">
                                    <div>Đào tạo: <span class="email">daotaopoly.hn@fe.edu.vn</span></div>
                                    <div>Khảo thí: <span class="email">khaothipolyhn@fe.edu.vn</span></div>
                                </div>
                            </div>
                            <div class="department-item">
                                <strong>Phòng hành chính:</strong>
                                <span class="email">hanhchinhfplhn@fe.edu.vn</span>
                            </div>
                            <div class="department-item">
                                <strong>Phòng quan hệ doanh nghiệp:</strong>
                                <span class="email">qhdn.poly@fpt.edu.vn</span>
                            </div>
                        </div>
                        
                        <div class="feedback-info">
                            <span>Ý kiến đóng góp chung gửi về</span>
                            <span class="email">ykien.poly@fpt.edu.vn</span>
                            <span>bằng email @fpt.edu.vn</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
