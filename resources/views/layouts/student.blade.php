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
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(20, 184, 166, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #0d9488;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
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
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .nav-link:hover {
            color: #14b8a6;
            background: #f0fdfa;
        }
        
        .nav-link.active {
            background: #14b8a6;
            color: white;
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
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0 text-white">
                        <i class="fas fa-graduation-cap me-2"></i> UniClubs
                    </h4>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="me-3">
                            <div class="user-avatar">{{ substr($user->name, 0, 1) }}</div>
                        </div>
                        <div>
                            <div class="fw-bold">{{ $user->name }}</div>
                            <small class="opacity-75">{{ $user->student_id ?? 'Sinh viên' }}</small>
                        </div>
                        <div class="ms-3">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="main-nav">
        <div class="container">
            <ul class="nav justify-content-center">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                        <i class="fas fa-home me-2"></i> Trang chủ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.clubs*') ? 'active' : '' }}" href="{{ route('student.clubs.index') }}">
                        <i class="fas fa-users me-2"></i> Câu lạc bộ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.events*') ? 'active' : '' }}" href="{{ route('student.events.index') }}">
                        <i class="fas fa-calendar-alt me-2"></i> Sự kiện
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.posts*') ? 'active' : '' }}" href="{{ route('student.posts') }}">
                        <i class="fas fa-newspaper me-2"></i> Bài viết
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.profile*') ? 'active' : '' }}" href="{{ route('student.profile.index') }}">
                        <i class="fas fa-user-circle me-2"></i> Hồ sơ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.contact*') ? 'active' : '' }}" href="{{ route('student.contact.index') }}">
                        <i class="fas fa-phone me-2"></i> Liên hệ
                    </a>
                </li>
                @php
                    $hasManagementRole = false;
                    if ($user->clubs->count() > 0) {
                        $clubId = $user->clubs->first()->id;
                        $position = $user->getPositionInClub($clubId);
                        $hasManagementRole = in_array($position, ['leader', 'vice_president', 'officer']);
                    }
                @endphp
                @if($hasManagementRole)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.club-management*') ? 'active' : '' }}" href="{{ route('student.club-management.index') }}">
                        <i class="fas fa-crown me-2"></i> Quản lý CLB
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </nav>

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
