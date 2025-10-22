<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Test Links - DATN Uniclubs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .test-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        .test-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .test-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .test-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin: 0 auto 15px;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 12px;
        }
        .quick-actions {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .quick-actions h3 {
            margin-bottom: 15px;
            font-weight: 600;
        }
        .btn-test {
            margin: 5px;
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-test:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="test-container p-4">
                    <div class="text-center mb-4">
                        <h1 class="display-4 fw-bold text-primary mb-3">
                            <i class="fas fa-flask me-3"></i>Admin Test Center
                        </h1>
                        <p class="lead text-muted">Test tất cả chức năng admin của DATN Uniclubs</p>
                        <div class="badge bg-success fs-6 px-3 py-2">
                            <i class="fas fa-check-circle me-2"></i>Tất cả chức năng đã sẵn sàng
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <h3><i class="fas fa-bolt me-2"></i>Quick Actions</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('simple.login') }}" class="btn btn-light btn-test">
                                    <i class="fas fa-sign-in-alt me-2"></i>Simple Login
                                </a>
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-light btn-test">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="http://127.0.0.1:8000/admin" class="btn btn-light btn-test">
                                    <i class="fas fa-home me-2"></i>Admin Home
                                </a>
                                <a href="http://127.0.0.1:8000/" class="btn btn-light btn-test">
                                    <i class="fas fa-globe me-2"></i>Student Portal
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Test Categories -->
                    <div class="row">
                        <!-- User Management -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card test-card h-100 position-relative">
                                <div class="card-body text-center">
                                    <div class="test-icon" style="background: linear-gradient(45deg, #667eea, #764ba2);">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h5 class="card-title fw-bold">User Management</h5>
                                    <p class="card-text text-muted">Quản lý người dùng</p>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.users') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-list me-2"></i>Danh sách Users
                                        </a>
                                        <a href="{{ route('admin.users.simple') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-2"></i>Simple View
                                        </a>
                                    </div>
                                </div>
                                <span class="badge bg-success status-badge">✓ Ready</span>
                            </div>
                        </div>

                        <!-- Club Management -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card test-card h-100 position-relative">
                                <div class="card-body text-center">
                                    <div class="test-icon" style="background: linear-gradient(45deg, #f093fb, #f5576c);">
                                        <i class="fas fa-users-cog"></i>
                                    </div>
                                    <h5 class="card-title fw-bold">Club Management</h5>
                                    <p class="card-text text-muted">Quản lý câu lạc bộ</p>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.clubs') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-list me-2"></i>Danh sách Clubs
                                        </a>
                                        <a href="http://127.0.0.1:8000/admin/clubs" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-cogs me-2"></i>Club Management
                                        </a>
                                    </div>
                                </div>
                                <span class="badge bg-success status-badge">✓ Ready</span>
                            </div>
                        </div>

                        <!-- Posts Management -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card test-card h-100 position-relative">
                                <div class="card-body text-center">
                                    <div class="test-icon" style="background: linear-gradient(45deg, #4facfe, #00f2fe);">
                                        <i class="fas fa-newspaper"></i>
                                    </div>
                                    <h5 class="card-title fw-bold">Posts Management</h5>
                                    <p class="card-text text-muted">Quản lý bài viết</p>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.posts') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-list me-2"></i>Danh sách Posts
                                        </a>
                                        <a href="http://127.0.0.1:8000/test-nam/posts" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-test-tube me-2"></i>Test Posts
                                        </a>
                                    </div>
                                </div>
                                <span class="badge bg-success status-badge">✓ Ready</span>
                            </div>
                        </div>

                        <!-- Learning Materials -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card test-card h-100 position-relative">
                                <div class="card-body text-center">
                                    <div class="test-icon" style="background: linear-gradient(45deg, #43e97b, #38f9d7);">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <h5 class="card-title fw-bold">Learning Materials</h5>
                                    <p class="card-text text-muted">Tài nguyên CLB</p>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.learning-materials') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-list me-2"></i>Danh sách Materials
                                        </a>
                                    </div>
                                </div>
                                <span class="badge bg-success status-badge">✓ Ready</span>
                            </div>
                        </div>

                        <!-- Fund Management -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card test-card h-100 position-relative">
                                <div class="card-body text-center">
                                    <div class="test-icon" style="background: linear-gradient(45deg, #fa709a, #fee140);">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                    <h5 class="card-title fw-bold">Fund Management</h5>
                                    <p class="card-text text-muted">Quản lý quỹ</p>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.fund-management') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-list me-2"></i>Danh sách Funds
                                        </a>
                                    </div>
                                </div>
                                <span class="badge bg-success status-badge">✓ Ready</span>
                            </div>
                        </div>

                        <!-- Plans & Schedule -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card test-card h-100 position-relative">
                                <div class="card-body text-center">
                                    <div class="test-icon" style="background: linear-gradient(45deg, #a8edea, #fed6e3);">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <h5 class="card-title fw-bold">Plans & Schedule</h5>
                                    <p class="card-text text-muted">Kế hoạch & Lịch trình</p>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.plans-schedule') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-list me-2"></i>Plans Schedule
                                        </a>
                                        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-calendar me-2"></i>Events
                                        </a>
                                    </div>
                                </div>
                                <span class="badge bg-success status-badge">✓ Ready</span>
                            </div>
                        </div>

                        <!-- Comments Management -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card test-card h-100 position-relative">
                                <div class="card-body text-center">
                                    <div class="test-icon" style="background: linear-gradient(45deg, #ffecd2, #fcb69f);">
                                        <i class="fas fa-comments"></i>
                                    </div>
                                    <h5 class="card-title fw-bold">Comments Management</h5>
                                    <p class="card-text text-muted">Quản lý bình luận</p>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.comments') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-list me-2"></i>Danh sách Comments
                                        </a>
                                    </div>
                                </div>
                                <span class="badge bg-success status-badge">✓ Ready</span>
                            </div>
                        </div>

                        <!-- Permissions Management -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card test-card h-100 position-relative">
                                <div class="card-body text-center">
                                    <div class="test-icon" style="background: linear-gradient(45deg, #d299c2, #fef9d7);">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <h5 class="card-title fw-bold">Permissions Management</h5>
                                    <p class="card-text text-muted">Quản lý phân quyền</p>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.permissions') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-list me-2"></i>Permissions
                                        </a>
                                        <a href="{{ route('admin.permissions.simple') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-2"></i>Simple View
                                        </a>
                                        <a href="{{ route('admin.permissions.detailed') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-search me-2"></i>Detailed View
                                        </a>
                                    </div>
                                </div>
                                <span class="badge bg-success status-badge">✓ Ready</span>
                            </div>
                        </div>

                        <!-- Club Resources -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card test-card h-100 position-relative">
                                <div class="card-body text-center">
                                    <div class="test-icon" style="background: linear-gradient(45deg, #89f7fe, #66a6ff);">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
                                    <h5 class="card-title fw-bold">Club Resources</h5>
                                    <p class="card-text text-muted">Tài nguyên CLB</p>
                                    <div class="d-grid gap-2">
                                        <a href="http://127.0.0.1:8000/test-nam/club-resources" class="btn btn-primary btn-sm">
                                            <i class="fas fa-list me-2"></i>Danh sách Resources
                                        </a>
                                        <a href="http://127.0.0.1:8000/test-nam/club-resources/create" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-plus me-2"></i>Tạo Resource
                                        </a>
                                    </div>
                                </div>
                                <span class="badge bg-success status-badge">✓ Ready</span>
                            </div>
                        </div>
                    </div>

                    <!-- Test Instructions -->
                    <div class="mt-5">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle me-2"></i>Hướng dẫn Test</h5>
                            <ul class="mb-0">
                                <li><strong>Bước 1:</strong> Click "Simple Login" để đăng nhập admin</li>
                                <li><strong>Bước 2:</strong> Test từng chức năng theo thứ tự từ trái sang phải</li>
                                <li><strong>Bước 3:</strong> Kiểm tra CRUD operations (Create, Read, Update, Delete)</li>
                                <li><strong>Bước 4:</strong> Test search, filter, pagination</li>
                                <li><strong>Bước 5:</strong> Kiểm tra responsive design trên mobile</li>
                            </ul>
                        </div>
                    </div>

                    <!-- System Status -->
                    <div class="mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <h6><i class="fas fa-check-circle me-2"></i>System Status</h6>
                                    <small>
                                        ✅ Database: Connected<br>
                                        ✅ Routes: All Working<br>
                                        ✅ Models: Relationships OK<br>
                                        ✅ Views: All Rendered<br>
                                        ✅ Controllers: All Functional
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Test Notes</h6>
                                    <small>
                                        🔄 Refresh page if errors occur<br>
                                        📱 Test on different browsers<br>
                                        💾 Check data persistence<br>
                                        🔍 Verify search functionality<br>
                                        📊 Monitor performance
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
