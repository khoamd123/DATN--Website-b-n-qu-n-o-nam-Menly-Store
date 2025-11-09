<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng ký - UniClubs</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: #f0fdfa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(20, 184, 166, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            border: 1px solid #a7f3d0;
        }
        
        .register-header {
            background: #14b8a6;
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .register-header h3 {
            margin: 0;
            font-weight: 600;
        }
        
        .register-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        
        .register-body {
            padding: 2rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-register {
            background: #14b8a6;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            color: white;
            font-weight: 500;
            transition: all 0.2s ease;
            width: 100%;
        }
        
        .btn-register:hover {
            background: #0d9488;
            transform: translateY(-1px);
            color: white;
        }
        
        .input-group {
            margin-bottom: 1rem;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        
        .form-control.input-with-icon {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .university-info {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        
        .university-info h6 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .example-email {
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 5px;
            font-family: monospace;
            font-size: 0.9rem;
            color: #667eea;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h3><i class="fas fa-user-plus"></i> Đăng ký UniClubs</h3>
            <p>Chỉ dành cho sinh viên</p>
        </div>
        
        <div class="register-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" 
                           class="form-control input-with-icon @error('email') is-invalid @enderror" 
                           name="email" 
                           placeholder="Email trường (.edu.vn)" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus>
                </div>
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    Ví dụ: khoamdph31863@fpt.edu.vn
                </small>
                
                <div class="input-group mt-3">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" 
                           class="form-control input-with-icon @error('name') is-invalid @enderror" 
                           name="name" 
                           placeholder="Họ và tên" 
                           value="{{ old('name') }}" 
                           required>
                </div>
                <small class="text-muted">
                    <i class="fas fa-magic"></i>
                    Tên sẽ được tự động điền từ email nếu có thể
                </small>
                
                <div class="input-group mt-3">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" 
                           class="form-control input-with-icon @error('password') is-invalid @enderror" 
                           name="password" 
                           placeholder="Mật khẩu (tối thiểu 6 ký tự)" 
                           required>
                </div>
                
                <div class="input-group mt-3">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" 
                           class="form-control input-with-icon @error('password_confirmation') is-invalid @enderror" 
                           name="password_confirmation" 
                           placeholder="Xác nhận mật khẩu" 
                           required>
                </div>
                
                <div class="input-group mt-3">
                    <span class="input-group-text">
                        <i class="fas fa-phone"></i>
                    </span>
                    <input type="tel" 
                           class="form-control input-with-icon @error('phone') is-invalid @enderror" 
                           name="phone" 
                           placeholder="Số điện thoại (tùy chọn)" 
                           value="{{ old('phone') }}">
                </div>
                
                <button type="submit" class="btn btn-register mt-4">
                    <i class="fas fa-user-plus"></i> Đăng ký tài khoản
                </button>
            </form>
            
            <!-- Thông tin về email trường -->
            <div class="university-info">
                <h6><i class="fas fa-graduation-cap"></i> Thông tin đăng ký:</h6>
                <ul class="mb-2">
                    <li>Chỉ chấp nhận email trường (.edu.vn)</li>
                    <li>Mã sinh viên sẽ được tạo tự động từ email</li>
                    <li>Mỗi sinh viên chỉ được đăng ký 1 tài khoản</li>
                    <li>Ví dụ: <code>khoamdph31863@fpt.edu.vn</code></li>
                </ul>
                <div class="example-email">
                    <strong>Email:</strong> khoamdph31863@fpt.edu.vn<br>
                    <strong>→ Tên:</strong> Khoa Mạc Đăng<br>
                    <strong>→ Mã SV:</strong> PH31863
                </div>
            </div>
            
            <div class="text-center mt-3">
                <p class="mb-0">
                    Đã có tài khoản? 
                    <a href="{{ route('login') }}" class="text-decoration-none">
                        <strong>Đăng nhập ngay</strong>
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
