<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn tham gia CLB đã bị từ chối</title>
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
        .rejection-reason {
            background: #fff3cd;
            border: 1px solid #ffc107;
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
        <p>Thông báo từ chối đơn tham gia CLB</p>
    </div>
    
    <div class="content">
        <p>Xin chào <strong>{{ $user->name }}</strong>,</p>
        
        <p>Chúng tôi rất tiếc phải thông báo rằng đơn tham gia CLB của bạn đã bị từ chối.</p>
        
        <div class="info-box">
            <h3 style="margin-top: 0; color: #0f766e;">Thông tin đơn đăng ký:</h3>
            <p><strong>CLB:</strong> {{ $club->name }}</p>
            <p><strong>Thời gian gửi đơn:</strong> {{ $joinRequest->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Thời gian xử lý:</strong> {{ $joinRequest->reviewed_at ? $joinRequest->reviewed_at->format('d/m/Y H:i') : 'N/A' }}</p>
        </div>
        
        @if($rejectionReason)
        <div class="rejection-reason">
            <h4 style="margin-top: 0; color: #856404;">Lý do từ chối:</h4>
            <p style="margin-bottom: 0;">{{ $rejectionReason }}</p>
        </div>
        @endif
        
        <p>Bạn có thể:</p>
        <ul>
            <li>Xem lại thông tin CLB và các yêu cầu tham gia</li>
            <li>Liên hệ với ban quản lý CLB nếu có thắc mắc</li>
            <li>Thử đăng ký tham gia các CLB khác phù hợp với bạn</li>
        </ul>
        
        <div style="text-align: center;">
            <a href="{{ url('/student/clubs') }}" class="btn">Xem danh sách CLB</a>
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






