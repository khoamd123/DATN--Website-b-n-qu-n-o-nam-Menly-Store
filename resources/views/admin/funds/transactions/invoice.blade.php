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
            color: #2c3e50;
            line-height: 1.6;
            background: white;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        
        .header {
            text-align: center;
            border-bottom: 4px solid #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 8px 8px 0 0;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        
        .header .invoice-number {
            font-size: 14px;
            opacity: 0.95;
            margin-top: 3px;
        }
        
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 15px;
        }
        
        .info-box {
            width: 48%;
            border: 2px solid #e8e8e8;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .info-box h3 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        
        .info-box p {
            margin: 0;
            padding: 8px 12px;
            font-size: 12px;
            border-bottom: 1px solid #f0f0f0;
            line-height: 1.5;
        }
        
        .info-box p:last-child {
            border-bottom: none;
        }
        
        .info-box strong {
            color: #667eea;
            font-weight: 600;
        }
        
        .transaction-details {
            margin: 20px 0;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .transaction-details h3 {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .detail-row:hover {
            background-color: #f8f9fa;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-row .label {
            font-weight: 600;
            width: 40%;
            color: #667eea;
        }
        
        .detail-row .value {
            width: 60%;
            text-align: right;
            color: #2c3e50;
        }
        
        .amount-box {
            background: linear-gradient(135deg, {{ $transaction->type === 'income' ? '#28a745 0%, #20c997 100%' : '#dc3545 0%, #fd7e14 100%' }});
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .amount-box .label {
            font-size: 14px;
            color: white;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        
        .amount-box .amount {
            font-size: 32px;
            font-weight: 800;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .receipt-section {
            margin: 20px 0;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .receipt-section h3 {
            background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
            color: white;
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        
        .receipt-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 15px;
        }
        
        .receipt-item {
            width: calc(33.33% - 10px);
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .receipt-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .receipt-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block;
        }
        
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            padding: 20px 0;
        }
        
        .signature-box {
            width: 45%;
            text-align: center;
        }
        
        .signature-box p {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 40px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .signature-line {
            border-top: 2px solid #2c3e50;
            margin: 0 auto 12px;
            width: 220px;
        }
        
        .signature-name {
            font-size: 14px;
            font-style: italic;
            color: #2c3e50;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 3px solid #667eea;
            text-align: center;
            font-size: 11px;
            color: #7f8c8d;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 18px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 2px solid #ffc107;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>HÓA ĐƠN GIAO DỊCH QUỸ</h1>
            <p class="invoice-number">Số: #{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</p>
        </div>
        
        <div class="invoice-info">
            <div class="info-box">
                <h3>THÔNG TIN QUỸ</h3>
                <p><strong>Tên quỹ:</strong> {{ $fund->name }}</p>
                <p><strong>CLB:</strong> {{ $fund->club ? $fund->club->name : 'Quỹ chung' }}</p>
                <p><strong>Trạng thái:</strong> {{ $fund->status === 'active' ? 'Đang hoạt động' : 'Ngừng hoạt động' }}</p>
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
                <span class="value" style="text-align: left;">{{ strip_tags($transaction->description) }}</span>
            </div>
            @endif
        </div>
        
        @if($transaction->items && $transaction->items->count() > 0)
        <div class="transaction-details">
            <h3>CHI TIẾT CÁC KHOẢN MỤC</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="padding: 8px 12px; text-align: left; border-bottom: 2px solid #dee2e6; font-size: 12px;">STT</th>
                        <th style="padding: 8px 12px; text-align: left; border-bottom: 2px solid #dee2e6; font-size: 12px;">Khoản mục</th>
                        <th style="padding: 8px 12px; text-align: right; border-bottom: 2px solid #dee2e6; font-size: 12px;">Số tiền</th>
                        <th style="padding: 8px 12px; text-align: center; border-bottom: 2px solid #dee2e6; font-size: 12px;">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->items as $index => $item)
                    <tr style="{{ $item->status === 'rejected' ? 'background-color: #fff5f5;' : '' }}">
                        <td style="padding: 6px 12px; border-bottom: 1px solid #f0f0f0; font-size: 11px;">{{ $index + 1 }}</td>
                        <td style="padding: 6px 12px; border-bottom: 1px solid #f0f0f0; font-size: 11px;">
                            {{ $item->item_name }}
                            @if($item->notes)
                                <br><small style="color: #6c757d; font-style: italic;">{{ $item->notes }}</small>
                            @endif
                            @if($item->status === 'rejected' && $item->rejection_reason)
                                <br><small style="color: #dc3545; font-weight: 600;">
                                    <i class="fas fa-times-circle"></i> Lý do từ chối: {{ $item->rejection_reason }}
                                </small>
                            @endif
                        </td>
                        <td style="padding: 6px 12px; border-bottom: 1px solid #f0f0f0; text-align: right; font-size: 11px; {{ $item->status === 'rejected' ? 'text-decoration: line-through; color: #6c757d;' : 'font-weight: 600;' }}">
                            {{ number_format($item->amount, 0, ',', '.') }} VNĐ
                        </td>
                        <td style="padding: 6px 12px; border-bottom: 1px solid #f0f0f0; text-align: center; font-size: 10px;">
                            @if($item->status === 'approved')
                                <span style="background-color: #d4edda; color: #155724; padding: 3px 8px; border-radius: 12px; font-weight: 600;">ĐÃ DUYỆT</span>
                            @elseif($item->status === 'rejected')
                                <span style="background-color: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 12px; font-weight: 600;">TỪ CHỐI</span>
                            @else
                                <span style="background-color: #fff3cd; color: #856404; padding: 3px 8px; border-radius: 12px; font-weight: 600;">CHỜ DUYỆT</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f8f9fa; font-weight: 700;">
                        <td colspan="2" style="padding: 10px 12px; border-top: 2px solid #dee2e6; text-align: right; font-size: 12px;">
                            TỔNG CỘNG:
                        </td>
                        <td style="padding: 10px 12px; border-top: 2px solid #dee2e6; text-align: right; font-size: 13px; color: #dc3545; font-weight: 800;">
                            {{ number_format($transaction->amount, 0, ',', '.') }} VNĐ
                        </td>
                        <td style="padding: 10px 12px; border-top: 2px solid #dee2e6;"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
        
        <div class="amount-box">
            <div class="label">SỐ TIỀN</div>
            <div class="amount">
                {{ $transaction->type === 'income' ? '+' : '-' }} {{ number_format($transaction->amount, 0, ',', '.') }} VNĐ
            </div>
        </div>
        
        @if($transaction->receipt_path || (is_array($transaction->receipt_paths) && count($transaction->receipt_paths) > 0))
        <div class="receipt-section">
            <h3>HÓA ĐƠN/CHỨNG TỪ</h3>
            <div class="receipt-grid">
                @php
                    $allReceipts = [];
                    if (!empty($transaction->receipt_path)) { $allReceipts[] = $transaction->receipt_path; }
                    if (is_array($transaction->receipt_paths)) { $allReceipts = array_merge($allReceipts, $transaction->receipt_paths); }
                    function encodeImage($path) {
                        $full = public_path($path);
                        if (!file_exists($full)) return null;
                        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
                        $mime = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg' : (in_array($ext, ['png','webp']) ? 'image/png' : null);
                        if (!$mime) return null;
                        $data = base64_encode(@file_get_contents($full));
                        return $data ? 'data:'.$mime.';base64,'.$data : null;
                    }
                @endphp
                @foreach($allReceipts as $rpath)
                    @php $src = encodeImage($rpath); @endphp
                    @if($src)
                        <div class="receipt-item">
                            <img src="{{ $src }}" alt="Hóa đơn {{ $loop->iteration }}">
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

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
                <p>NGƯỜI TẠO</p>
                <div class="signature-line"></div>
                <p class="signature-name">{{ $transaction->creator->name ?? 'N/A' }}</p>
            </div>
            
            <div class="signature-box">
                <p>NGƯỜI DUYỆT</p>
                <div class="signature-line"></div>
                <p class="signature-name">{{ $transaction->approver->name ?? 'Chưa duyệt' }}</p>
            </div>
        </div>
        
        <div class="footer">
            <p>Hệ thống quản lý câu lạc bộ - UniClubs</p>
            <p>Hóa đơn được tạo ngày {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>






