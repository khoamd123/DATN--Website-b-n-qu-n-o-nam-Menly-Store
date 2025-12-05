<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UniClubs - Kết nối sinh viên & câu lạc bộ')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #1f2937;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: #0f766e !important;
        }

        .hero-section {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
            color: white;
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            top: -60px;
            right: -120px;
            width: 320px;
            height: 320px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            filter: blur(2px);
        }

        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(20, 184, 166, 0.08);
            border: none;
            transition: transform 0.2s ease;
        }

        .stats-card:hover {
            transform: translateY(-4px);
        }

        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        /* Navbar pills */
        .nav-link {
            color: #6b7280;
            font-weight: 600;
            padding: 0.75rem 1.1rem;
            border-radius: 12px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .nav-link:hover { color: #14b8a6; background: #f0fdfa; }
        .nav-link.active { background: #14b8a6; color: #fff !important; }

        .club-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(15, 118, 110, 0.05);
            transition: all 0.2s ease;
        }

        .club-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(15, 118, 110, 0.12);
        }

        .event-card, .post-card, .field-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease;
        }

        .event-card:hover, .post-card:hover, .field-card:hover {
            transform: translateY(-4px);
        }

        footer {
            background: #0f172a;
            color: #cbd5f5;
            padding: 2.5rem 0;
            margin-top: 4rem;
        }

        .footer-link {
            color: #cbd5f5;
            text-decoration: none;
        }

        .footer-link:hover {
            color: white;
        }

        .btn-teal {
            background: #0f766e;
            color: white;
            border-radius: 999px;
            padding: 0.5rem 1.25rem;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-teal:hover {
            background: #115e59;
            color: white;
        }

        .btn-outline-teal {
            border-radius: 999px;
            padding: 0.5rem 1.25rem;
            border: 1px solid #0f766e;
            color: #0f766e;
            transition: all 0.2s ease;
        }

        .btn-outline-teal:hover {
            background: #0f766e;
            color: white;
        }

        .text-teal {
            color: #0f766e !important;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #0f766e;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
    </style>

    @stack('styles')
</head>
<body>
    @php
        $isLoggedIn = session('user_id');
        $userName = session('user_name');
    @endphp
    <nav class="navbar navbar-expand-lg bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <i class="fas fa-layer-group me-2 text-teal"></i> UniClubs
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                @php
                    $loggedIn = session('user_id');
                    $user = null;
                    $hasManagementRole = false;
                    if ($loggedIn) {
                        $user = \App\Models\User::with('clubs')->find(session('user_id'));
                        if ($user && $user->clubs->count() > 0) {
                            $clubId = $user->clubs->first()->id;
                            $position = $user->getPositionInClub($clubId);
                            $hasManagementRole = in_array($position, ['leader', 'vice_president', 'officer']);
                        }
                    }
                @endphp
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('home')) active @endif" href="{{ route('home') }}">
                            <i class="fas fa-home me-2"></i> Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('student.clubs*')) active @endif" href="{{ route('student.clubs.index') }}">
                            <i class="fas fa-users me-2"></i> Câu lạc bộ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('student.events*')) active @endif" href="{{ route('student.events.index') }}">
                            <i class="fas fa-calendar-alt me-2"></i> Sự kiện
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('student.posts*')) active @endif" href="{{ route('student.posts') }}">
                            <i class="fas fa-newspaper me-2"></i> Bài viết
                        </a>
                    </li>
                    @if($loggedIn)
                        <li class="nav-item">
                            <a class="nav-link @if(request()->routeIs('student.profile*')) active @endif" href="{{ route('student.profile.index') }}">
                                <i class="fas fa-user-circle me-2"></i> Hồ sơ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if(request()->routeIs('student.notifications*')) active @endif" href="{{ route('student.notifications.index') }}">
                                <i class="fas fa-bell me-2"></i> Thông báo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if(request()->routeIs('student.contact*')) active @endif" href="{{ route('student.contact.index') }}">
                                <i class="fas fa-phone me-2"></i> Liên hệ
                            </a>
                        </li>
                        @if($hasManagementRole)
                            <li class="nav-item">
                                <a class="nav-link @if(request()->routeIs('student.club-management*')) active @endif" href="{{ route('student.club-management.index') }}">
                                    <i class="fas fa-crown me-2"></i> Quản lý CLB
                                </a>
                            </li>
                        @endif
                    @endif
                </ul>
                @if(!$isLoggedIn)
                    <div class="d-flex ms-lg-4 gap-2">
                        <a href="{{ route('login') }}" class="btn btn-outline-teal">Đăng nhập</a>
                        <a href="{{ route('register') }}" class="btn btn-teal">Đăng ký</a>
                    </div>
                @else
                    <div class="d-flex ms-lg-4 align-items-center gap-3 flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="small">
                                <div class="fw-semibold text-teal">{{ $userName }}</div>
                                <div class="text-muted">Thành viên UniClubs</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('student.profile.index') }}" class="btn btn-outline-teal">Hồ sơ</a>
                            <a href="{{ route('student.club-management.index') }}" class="btn btn-outline-teal">Quản lý CLB</a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-teal">Đăng xuất</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <main>
        @if (View::hasSection('page_title'))
        <section class="sub-hero border-bottom" style="background: #f0fdfa;">
            <div class="container py-3">
                <h2 class="h5 fw-bold text-teal mb-1">@yield('page_title')</h2>
                @hasSection('page_subtitle')
                    <p class="text-muted mb-0">@yield('page_subtitle')</p>
                @endif
            </div>
        </section>
        @endif
        @yield('content')
    </main>

    <footer>
        <div class="container">
            <div class="row gy-4">
                <div class="col-md-6">
                    <h5 class="fw-bold text-white mb-3">UniClubs</h5>
                    <p class="mb-2">Nền tảng kết nối sinh viên với các câu lạc bộ tại trường.</p>
                    <p class="small mb-0">&copy; {{ now()->year }} UniClubs. All rights reserved.</p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-white fw-semibold mb-3">Tìm hiểu thêm</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('student.clubs.index') }}" class="footer-link">Danh sách CLB</a></li>
                        <li class="mb-2"><a href="{{ route('student.events.index') }}" class="footer-link">Sự kiện</a></li>
                        <li class="mb-2"><a href="{{ route('student.posts') }}" class="footer-link">Tin tức</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="text-white fw-semibold mb-3">Liên hệ</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="mailto:contact@uniclubs.vn" class="footer-link"><i class="fas fa-envelope me-2"></i>contact@uniclubs.vn</a></li>
                        <li class="mb-2"><a href="#" class="footer-link"><i class="fas fa-phone me-2"></i>0123 456 789</a></li>
                        <li class="mb-2">
                            <div class="d-flex gap-3">
                                <a href="#" class="footer-link"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" class="footer-link"><i class="fab fa-instagram"></i></a>
                                <a href="#" class="footer-link"><i class="fab fa-youtube"></i></a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

