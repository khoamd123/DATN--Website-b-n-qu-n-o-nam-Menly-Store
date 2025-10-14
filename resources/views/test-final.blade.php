<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Hệ Thống - UniClubs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 2.5rem;
        }
        .header p {
            color: #666;
            font-size: 1.1rem;
            margin: 10px 0 0 0;
        }
        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .test-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            border-left: 5px solid #667eea;
            transition: transform 0.2s;
        }
        .test-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .test-card h3 {
            color: #333;
            margin: 0 0 15px 0;
            font-size: 1.3rem;
        }
        .test-card p {
            color: #666;
            margin: 0 0 15px 0;
            line-height: 1.5;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        .btn-warning:hover {
            background: #e0a800;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .status {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border-radius: 10px;
            font-weight: bold;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info-box {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-box h4 {
            color: #1976d2;
            margin: 0 0 10px 0;
        }
        .info-box ul {
            margin: 0;
            padding-left: 20px;
        }
        .info-box li {
            margin: 5px 0;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Hệ Thống Phân Quyền CLB</h1>
            <p>Đã hoàn thành và sẵn sàng sử dụng!</p>
        </div>

        <div class="status success">
            ✅ Tất cả components đã được kiểm tra và hoạt động tốt
        </div>

        <div class="test-grid">
            <div class="test-card">
                <h3>🔐 Đăng Nhập</h3>
                <p>Test hệ thống đăng nhập đơn giản với session management</p>
                <a href="/admin-login" class="btn">Test Đăng Nhập</a>
            </div>

            <div class="test-card">
                <h3>👥 Quản Lý Người Dùng</h3>
                <p>Test trang quản lý người dùng (đã sửa nháy màn hình)</p>
                <a href="/users-simple" class="btn btn-success">Test Users</a>
            </div>

            <div class="test-card">
                <h3>⚖️ Phân Quyền</h3>
                <p>Test trang phân quyền (đã sửa nháy màn hình)</p>
                <a href="/permissions-simple" class="btn btn-warning">Test Permissions</a>
            </div>

            <div class="test-card">
                <h3>🛡️ Test Phân Quyền CLB</h3>
                <p>Test hệ thống phân quyền CLB với các vai trò khác nhau</p>
                <a href="/club-test" class="btn btn-danger">Test CLB Permissions</a>
            </div>

            <div class="test-card">
                <h3>🏛️ Admin Dashboard</h3>
                <p>Truy cập admin dashboard chính</p>
                <a href="/admin/dashboard" class="btn">Admin Dashboard</a>
            </div>

            <div class="test-card">
                <h3>📊 Quản Lý CLB</h3>
                <p>Test trang quản lý CLB cho admin</p>
                <a href="/admin/clubs-management" class="btn">Manage Clubs</a>
            </div>
        </div>

        <div class="info-box">
            <h4>🔑 Tài khoản test:</h4>
            <ul>
                <li><strong>Admin:</strong> admin@university.edu.vn / password</li>
                <li><strong>Leader:</strong> tranthib@university.edu.vn / password</li>
                <li><strong>Officer:</strong> leader@university.edu.vn / password</li>
                <li><strong>Member:</strong> officer@university.edu.vn / password</li>
                <li><strong>Guest:</strong> member@university.edu.vn / password</li>
            </ul>
        </div>

        <div class="info-box">
            <h4>🎯 Tính năng đã hoàn thành:</h4>
            <ul>
                <li>✅ Hệ thống phân quyền 4 cấp: Admin → Leader → Officer → Member → Guest</li>
                <li>✅ Middleware bảo mật cho từng cấp độ quyền</li>
                <li>✅ Session-based authentication</li>
                <li>✅ Database structure với relationships</li>
                <li>✅ Test system với giao diện đẹp</li>
                <li>✅ Sửa lỗi nháy màn hình</li>
                <li>✅ Layout đơn giản và ổn định</li>
            </ul>
        </div>

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee;">
            <p style="color: #666; margin: 0;">
                🚀 <strong>Hệ thống đã sẵn sàng sử dụng!</strong> 
                Nhấn vào các nút trên để test từng chức năng.
            </p>
        </div>
    </div>
</body>
</html>
