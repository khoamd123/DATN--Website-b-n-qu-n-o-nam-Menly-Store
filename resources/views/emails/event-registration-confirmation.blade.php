<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đăng ký sự kiện</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border: 1px solid #e0e0e0;
            border-top: none;
        }
        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #0f766e;
        }
        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #0f766e;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>UniClubs</h1>
        <p>Xác nhận đăng ký tham gia sự kiện</p>
    </div>
    
    <div class="content">
        <p>Xin chào <strong>{{ $user->name }}</strong>,</p>
        
        <div class="success-box">
            <h4 style="margin-top: 0; color: #155724;">✓ Đăng ký thành công!</h4>
            <p style="margin-bottom: 0;">Bạn đã đăng ký tham gia sự kiện thành công.</p>
        </div>
        
        <div class="info-box">
            <h3 style="margin-top: 0; color: #0f766e;">Thông tin sự kiện:</h3>
            <p><strong>Tên sự kiện:</strong> {{ $event->title }}</p>
            <p><strong>CLB tổ chức:</strong> {{ $event->club->name ?? 'N/A' }}</p>
            @if($event->start_time)
            <p><strong>Thời gian bắt đầu:</strong> {{ $event->start_time->format('d/m/Y H:i') }}</p>
            @endif
            @if($event->end_time)
            <p><strong>Thời gian kết thúc:</strong> {{ $event->end_time->format('d/m/Y H:i') }}</p>
            @endif
            @if($event->location)
            <p><strong>Địa điểm:</strong> {{ $event->location }}</p>
            @endif
        </div>
        
        <p>Vui lòng lưu ý:</p>
        <ul>
            <li>Đến đúng giờ và địa điểm đã thông báo</li>
            <li>Mang theo thẻ sinh viên hoặc giấy tờ tùy thân</li>
            <li>Nếu có thay đổi, vui lòng liên hệ với ban tổ chức</li>
        </ul>
        
        <div style="text-align: center;">
            <a href="{{ url('/student/events/' . $event->id) }}" class="btn">Xem chi tiết sự kiện</a>
        </div>
        
        <p>Trân trọng,<br>
        <strong>Đội ngũ UniClubs</strong></p>
    </div>
    
    <div class="footer">
        <p>Email này được gửi tự động từ hệ thống UniClubs.</p>
        <p>Vui lòng không trả lời email này.</p>
    </div>
</body>
</html>





