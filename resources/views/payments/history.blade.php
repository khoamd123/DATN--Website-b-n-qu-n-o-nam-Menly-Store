@extends('layouts.student')

@section('title', 'Lịch sử thanh toán')
@section('page_title', 'Lịch sử thanh toán')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Lịch sử thanh toán</h5>
        <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-2"></i>Tạo thanh toán mới
        </a>
    </div>
    
    @if($payments->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Mã thanh toán</th>
                        <th>Số tiền</th>
                        <th>Loại</th>
                        <th>Phương thức</th>
                        <th>Trạng thái</th>
                        <th>Thời gian</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>
                                <code>{{ $payment->payment_code }}</code>
                                @if($payment->transaction_id)
                                    <br><small class="text-muted">GD: {{ $payment->transaction_id }}</small>
                                @endif
                            </td>
                            <td class="fw-bold">{{ number_format($payment->amount, 0, ',', '.') }} VNĐ</td>
                            <td>
                                @php
                                    $types = [
                                        'event_registration' => 'Đăng ký sự kiện',
                                        'club_fee' => 'Phí CLB',
                                        'fund_contribution' => 'Đóng góp quỹ',
                                        'other' => 'Khác'
                                    ];
                                @endphp
                                {{ $types[$payment->payment_type] ?? $payment->payment_type }}
                            </td>
                            <td>{{ strtoupper($payment->payment_method ?? 'N/A') }}</td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'completed' => 'success',
                                        'failed' => 'danger',
                                        'cancelled' => 'secondary'
                                    ];
                                    $statusTexts = [
                                        'pending' => 'Chờ thanh toán',
                                        'processing' => 'Đang xử lý',
                                        'completed' => 'Thành công',
                                        'failed' => 'Thất bại',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusClasses[$payment->status] ?? 'secondary' }}">
                                    {{ $statusTexts[$payment->status] ?? $payment->status }}
                                </span>
                            </td>
                            <td>
                                {{ $payment->created_at->format('d/m/Y H:i') }}
                                @if($payment->paid_at)
                                    <br><small class="text-muted">Thanh toán: {{ $payment->paid_at->format('d/m/Y H:i') }}</small>
                                @endif
                            </td>
                            <td>
                                @if($payment->isCompleted())
                                    <a href="{{ route('payments.success', $payment->id) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @elseif($payment->isPending() && !$payment->isExpired())
                                    @if($payment->payment_url)
                                        <a href="{{ $payment->payment_url }}" class="btn btn-sm btn-primary" target="_blank" title="Thanh toán">
                                            <i class="fas fa-credit-card"></i> Thanh toán
                                        </a>
                                    @else
                                        <span class="text-muted small">Đang tạo URL thanh toán...</span>
                                    @endif
                                    <form method="POST" action="{{ route('payments.cancel', $payment->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hủy thanh toán" onclick="return confirm('Bạn có chắc muốn hủy thanh toán này?');">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $payments->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-inbox text-muted" style="font-size: 64px;"></i>
            <p class="text-muted mt-3">Chưa có giao dịch thanh toán nào.</p>
            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tạo thanh toán mới
            </a>
        </div>
    @endif
</div>
@endsection

