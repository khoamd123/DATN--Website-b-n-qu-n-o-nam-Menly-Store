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
    
    @yield('styles')
    
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
            width: 280px;
            z-index: 1000;
        }
        
        .sidebar-header {
            background-color: #2c3e50;
            padding: 0.5rem;
            text-align: center;
            border-bottom: 1px solid #495057;
            display: none;
        }
        
        .sidebar-header h4 {
            color: white;
            margin: 0;
            font-weight: 600;
        }
        
        .sidebar-nav {
            padding: 1.5rem 0;
        }
        
        .nav-item {
            margin: 0;
        }
        
        .nav-link {
            color: #adb5bd;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            background: none;
            width: 100%;
            font-size: 0.95rem;
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
        
        .top-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 2rem;
            position: fixed;
            top: 0;
            left: 280px;
            right: 0;
            z-index: 999;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: calc(100vw - 280px);
            min-width: calc(100vw - 280px);
            max-width: calc(100vw - 280px);
        }
        
        .top-header .d-flex {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        .top-header .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
            text-decoration: none;
        }
        
        .top-header .logo img {
            height: 40px;
            width: auto;
        }
        
        .search-bar {
            max-width: 500px;
            position: relative;
            margin-right: 1.5rem;
        }
        
        .search-bar input {
            border-radius: 25px;
            border: 1px solid #dee2e6;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
        }
        
        .search-bar .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-shrink: 0;
            min-width: 200px;
            justify-content: flex-end;
        }
        
        .notification-btn, .message-btn {
            position: relative;
            background: none;
            border: none;
            color: #6c757d;
            font-size: 1.2rem;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .notification-btn:hover, .message-btn:hover {
            background-color: #f8f9fa;
            color: #495057;
        }
        
        .notification-badge, .message-badge {
            position: absolute;
            top: 0;
            right: 0;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
            margin-top: 80px;
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
        
        .user-avatar-fallback {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
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
            <!-- Top Header -->
            <header class="top-header">
                <div class="d-flex justify-content-between align-items-center w-100">
            <!-- Search Bar (chiếm toàn bộ không gian) -->
            <div class="search-bar flex-grow-1">
                <form method="GET" action="{{ route('admin.search') }}" class="position-relative">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           name="q" 
                           class="form-control w-100" 
                           placeholder="Tìm kiếm người dùng, câu lạc bộ, bài viết..."
                           value="{{ request('q') }}">
                </form>
            </div>
            
            <!-- Header Actions -->
            <div class="header-actions">
                <!-- Notifications -->
                <div class="dropdown">
                    <button class="notification-btn dropdown-toggle" 
                            type="button" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        @php
                            try {
                                $notificationCount = \App\Models\Notification::where('read_at', null)->count();
                            } catch (Exception $e) {
                                $notificationCount = 0;
                            }
                        @endphp
                        @if($notificationCount > 0)
                            <span class="notification-badge">{{ $notificationCount > 99 ? '99+' : $notificationCount }}</span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                        <li><h6 class="dropdown-header">🔔 Thông báo</h6></li>
                        @if($notificationCount > 0)
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-plus text-success"></i> Có {{ $notificationCount }} thông báo mới</a></li>
                        @else
                            <li><a class="dropdown-item text-muted" href="#"><i class="fas fa-check-circle text-success"></i> Không có thông báo mới</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="{{ route('admin.notifications') }}">Xem tất cả thông báo</a></li>
                    </ul>
                </div>
                
                <!-- Messages -->
                <div class="dropdown">
                    <button class="message-btn dropdown-toggle" 
                            type="button" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false">
                        <i class="fas fa-envelope"></i>
                        @php
                            try {
                                $messageCount = \App\Models\Notification::where('type', 'message')->where('read_at', null)->count();
                            } catch (Exception $e) {
                                $messageCount = 0;
                            }
                        @endphp
                        @if($messageCount > 0)
                            <span class="message-badge">{{ $messageCount > 99 ? '99+' : $messageCount }}</span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                        <li><h6 class="dropdown-header">💬 Tin nhắn</h6></li>
                        @if($messageCount > 0)
                            <li><a class="dropdown-item" href="#"><i class="fas fa-comment text-primary"></i> Có {{ $messageCount }} tin nhắn mới</a></li>
                        @else
                            <li><a class="dropdown-item text-muted" href="#"><i class="fas fa-inbox text-secondary"></i> Hộp thư trống</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="{{ route('admin.messages') }}">Xem tất cả tin nhắn</a></li>
                    </ul>
                </div>
                
                <!-- User Profile -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" 
                            type="button" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false">
                        <div class="user-avatar-fixed me-2" style="width: 30px; height: 30px; font-size: 12px;">
                            {{ substr(session('user_name', 'A'), 0, 1) }}
                        </div>
                        <span>{{ session('user_name', 'Admin') }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">👤 {{ session('user_name', 'Admin') }}</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="fas fa-user me-2"></i>Hồ sơ</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.settings') }}"><i class="fas fa-cog me-2"></i>Cài đặt</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <nav class="sidebar">
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
                        Quản lý CLB
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
     <li class="nav-item">
         <a class="nav-link {{ request()->routeIs('admin.permissions.detailed') ? 'active' : '' }}" href="{{ route('admin.permissions.detailed') }}">
             <i class="fas fa-cogs"></i>
             Phân Quyền Chi Tiết
         </a>
     </li>
     <li class="nav-item">
         <a class="nav-link {{ request()->routeIs('admin.trash*') ? 'active' : '' }}" href="{{ route('admin.trash') }}">
             <i class="fas fa-trash"></i>
             Thùng rác
             @php
                 try {
                     $trashCount = \App\Models\User::onlyTrashed()->count() + 
                                  \App\Models\Club::onlyTrashed()->count() + 
                                  \App\Models\Post::onlyTrashed()->count() + 
                                  \App\Models\ClubMember::onlyTrashed()->count() + 
                                  \App\Models\PostComment::onlyTrashed()->count();
                 } catch (Exception $e) {
                     $trashCount = 0;
                 }
             @endphp
             @if($trashCount > 0)
                 <span class="badge bg-danger ms-1">{{ $trashCount }}</span>
             @endif
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
