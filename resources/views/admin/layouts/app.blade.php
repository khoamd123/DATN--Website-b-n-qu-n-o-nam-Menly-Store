<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CLB Admin')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            padding: 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
        }
        
        .sidebar-header {
            background-color: #2c3e50;
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid #495057;
        }
        
        .sidebar-header h4 {
            color: white;
            margin: 0;
            font-weight: 600;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0;
        }
        
        .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            background: none;
            width: 100%;
        }
        
        .nav-link:hover {
            color: white;
            background-color: #495057;
        }
        
        .nav-link.active {
            color: white;
            background-color: #007bff;
        }
        
        .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
        }
        
        .content-header {
            background-color: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .content-header h1 {
            margin: 0;
            color: #343a40;
            font-size: 1.75rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #343a40;
            margin: 0;
        }
        
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }
        
        .stats-link {
            color: #007bff;
            text-decoration: none;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: inline-block;
        }
        
        .stats-link:hover {
            text-decoration: underline;
        }
        
        .user-list {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .user-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .user-item:last-child {
            border-bottom: none;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 1rem;
            object-fit: cover;
        }
        
        .user-info h6 {
            margin: 0;
            color: #343a40;
            font-size: 0.9rem;
        }
        
        .user-info small {
            color: #6c757d;
            font-size: 0.8rem;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h4>CLB Admin</h4>
        </div>
        
        <div class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                        <i class="fas fa-users"></i>
                        Quản lý người dùng
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.clubs*') ? 'active' : '' }}" href="{{ route('admin.clubs') }}">
                        <i class="fas fa-users"></i>
                        Thành viên CLB
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.learning-materials*') ? 'active' : '' }}" href="{{ route('admin.learning-materials') }}">
                        <i class="fas fa-file-alt"></i>
                        Tài liệu học tập
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.fund-management*') ? 'active' : '' }}" href="{{ route('admin.fund-management') }}">
                        <i class="fas fa-coins"></i>
                        Quản lý quỹ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.plans-schedule*') ? 'active' : '' }}" href="{{ route('admin.plans-schedule') }}">
                        <i class="fas fa-calendar-alt"></i>
                        Kế hoạch
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.posts*') ? 'active' : '' }}" href="{{ route('admin.posts') }}">
                        <i class="fas fa-newspaper"></i>
                        Bài viết
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.comments*') ? 'active' : '' }}" href="{{ route('admin.comments') }}">
                        <i class="fas fa-comments"></i>
                        Bình luận
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.permissions*') ? 'active' : '' }}" href="{{ route('admin.permissions') }}">
                        <i class="fas fa-balance-scale"></i>
                        Phân Quyền
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="nav-link text-danger" style="background: none; border: none; width: 100%; text-align: left;">
                            <i class="fas fa-sign-out-alt"></i>
                            Đăng xuất
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('scripts')
</body>
</html>
