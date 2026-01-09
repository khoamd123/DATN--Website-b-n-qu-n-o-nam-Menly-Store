@extends('layouts.student')

@section('title', 'Thanh toán thành công')
@section('page_title', 'Thanh toán thành công')

@section('content')
<div class="content-card text-center">
    <div class="mb-4">
        <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
    </div>
    
    <h3 class="text-success mb-3">Thanh toán thành công!</h3>
    
    <div class="card bg-light mb-4">
        <div class="card-body text-start">
            <h5 class="card-title mb-3">Thông tin giao dịch</h5>
            <table class="table table-borderless mb-0">
                <tr>
                    <td width="40%"><strong>Mã thanh toán:</strong></td>
                    <td>{{ $payment->payment_code }}</td>
                </tr>
                <tr>
                    <td><strong>Số tiền:</strong></td>
                    <td class="text-success fw-bold">{{ number_format($payment->amount, 0, ',', '.') }} VNĐ</td>
                </tr>
                <tr>
                    <td><strong>Phương thức:</strong></td>
                    <td>{{ strtoupper($payment->payment_method) }}</td>
                </tr>
                @if($payment->transaction_id)
                <tr>
                    <td><strong>Mã giao dịch:</strong></td>
                    <td>{{ $payment->transaction_id }}</td>
                </tr>
                @endif
                @if($payment->bank_code)
                <tr>
                    <td><strong>Ngân hàng:</strong></td>
                    <td>{{ $payment->bank_code }}</td>
                </tr>
                @endif
                <tr>
                    <td><strong>Thời gian:</strong></td>
                    <td>{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i:s') : $payment->created_at->format('d/m/Y H:i:s') }}</td>
                </tr>
                @if($payment->description)
                <tr>
                    <td><strong>Mô tả:</strong></td>
                    <td>{{ $payment->description }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    
    @if($payment->fundTransaction)
        <div class="alert alert-success">
            <i class="fas fa-check me-2"></i>
            Giao dịch quỹ đã được tạo tự động. Mã giao dịch: #{{ $payment->fundTransaction->id }}
        </div>
    @endif
    
    <div class="d-flex justify-content-center gap-2 flex-wrap">
        <a href="{{ route('payments.history') }}" class="btn btn-primary">
            <i class="fas fa-history me-2"></i>Xem lịch sử thanh toán
        </a>
        @if($payment->fund_id && $payment->fund)
            @php
                $clubId = $payment->fund->club_id ?? null;
            @endphp
            @if($clubId)
                <a href="{{ route('student.club-management.fund-transactions') }}?club={{ $clubId }}" class="btn btn-info text-white">
                    <i class="fas fa-wallet me-2"></i>Xem giao dịch quỹ
                </a>
            @endif
        @endif
        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-home me-2"></i>Về trang chủ
        </a>
    </div>
</div>
@endsection

