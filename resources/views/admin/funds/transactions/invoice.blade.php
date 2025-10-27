<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hóa đơn giao dịch quỹ #{{ $transaction->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #007bff;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .info-box {
            width: 48%;
        }
        
        .info-box h3 {
            background-color: #007bff;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .info-box p {
            margin: 5px 0;
            font-size: 12px;
        }
        
        .transaction-details {
            margin: 30px 0;
        }
        
        .transaction-details h3 {
            background-color: #28a745;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-row .label {
            font-weight: bold;
            width: 40%;
        }
        
        .detail-row .value {
            width: 60%;
            text-align: right;
        }
        
        .amount-box {
            background-color: #f8f9fa;
            padding: 20px;
            border: 2px solid #28a745;
            margin: 30px 0;
            text-align: center;
        }
        
        .amount-box .label {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .amount-box .amount {
            font-size: 32px;
            font-weight: bold;
            color: {{ $transaction->type === 'income' ? '#28a745' : '#dc3545' }};
        }
        
        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 45%;
            text-align: center;
        }
        
        .signature-line {
            border-top: 2px solid #333;
            margin: 40px auto 10px;
            width: 200px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>HÓA ĐƠN GIAO DỊCH QUỸ</h1>
            <p style="margin-top: 10px;">Số: #{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</p>
        </div>
        
        <div class="invoice-info">
            <div class="info-box">
                <h3>THÔNG TIN QUỸ</h3>
                <p><strong>Tên quỹ:</strong> {{ $fund->name }}</p>
                <p><strong>CLB:</strong> {{ $fund->club ? $fund->club->name : 'Quỹ chung' }}</p>
                <p><strong>Trạng thái quỹ:</strong> 
                    <span class="status-badge status-approved">
                        {{ $fund->status === 'active' ? 'Hoạt động' : 'Không hoạt động' }}
                    </span>
                </p>
            </div>
            
            <div class="info-box">
                <h3>THÔNG TIN GIAO DỊCH</h3>
                <p><strong>Ngày giao dịch:</strong> {{ $transaction->transaction_date->format('d/m/Y H:i') }}</p>
                <p><strong>Trạng thái:</strong> 
                    <span class="status-badge status-{{ $transaction->status }}">
                        @if($transaction->status === 'approved')
                            Đã duyệt
                        @elseif($transaction->status === 'pending')
                            Chờ duyệt
                        @else
                            Từ chối
                        @endif
                    </span>
                </p>
                <p><strong>Người tạo:</strong> {{ $transaction->creator->name ?? 'N/A' }}</p>
            </div>
        </div>
        
        <div class="transaction-details">
            <h3>CHI TIẾT GIAO DỊCH</h3>
            
            <div class="detail-row">
                <span class="label">Loại giao dịch:</span>
                <span class="value">
                    <strong>{{ $transaction->type === 'income' ? 'THU TIỀN' : 'CHI TIỀN' }}</strong>
                </span>
            </div>
            
            <div class="detail-row">
                <span class="label">Tiêu đề:</span>
                <span class="value">{{ $transaction->title }}</span>
            </div>
            
            @if($transaction->category)
            <div class="detail-row">
                <span class="label">Danh mục:</span>
                <span class="value">{{ $transaction->category }}</span>
            </div>
            @endif
            
            @if($transaction->event)
            <div class="detail-row">
                <span class="label">Sự kiện liên quan:</span>
                <span class="value">{{ $transaction->event->name }}</span>
            </div>
            @endif
            
            @if($transaction->description)
            <div class="detail-row">
                <span class="label">Mô tả:</span>
                <span class="value" style="text-align: left;">{{ $transaction->description }}</span>
            </div>
            @endif
        </div>
        
        <div class="amount-box">
            <div class="label">SỐ TIỀN</div>
            <div class="amount">
                {{ $transaction->type === 'income' ? '+' : '-' }} {{ number_format($transaction->amount, 0, ',', '.') }} VNĐ
            </div>
        </div>
        
        @if($transaction->source)
        <div class="transaction-details">
            <h3>NGUỒN TIỀN</h3>
            <div class="detail-row">
                <span class="label">Nguồn:</span>
                <span class="value">{{ $transaction->source }}</span>
            </div>
        </div>
        @endif
        
        @if($transaction->status === 'approved')
        <div class="transaction-details">
            <h3>THÔNG TIN DUYỆT</h3>
            <div class="detail-row">
                <span class="label">Người duyệt:</span>
                <span class="value">{{ $transaction->approver->name ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Ngày duyệt:</span>
                <span class="value">{{ $transaction->approved_at ? $transaction->approved_at->format('d/m/Y H:i') : 'N/A' }}</span>
            </div>
        </div>
        @endif
        
        <div class="signature-section">
            <div class="signature-box">
                <p style="margin-bottom: 50px;"><strong>NGƯỜI TẠO</strong></p>
                <div class="signature-line"></div>
                <p style="margin-top: 10px;">{{ $transaction->creator->name ?? 'N/A' }}</p>
            </div>
            
            <div class="signature-box">
                <p style="margin-bottom: 50px;"><strong>NGƯỜI DUYỆT</strong></p>
                <div class="signature-line"></div>
                <p style="margin-top: 10px;">{{ $transaction->approver->name ?? 'Chưa duyệt' }}</p>
            </div>
        </div>
        
        <div class="footer">
            <p>Hệ thống quản lý câu lạc bộ - UniClubs</p>
            <p>Hóa đơn được tạo ngày {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>





