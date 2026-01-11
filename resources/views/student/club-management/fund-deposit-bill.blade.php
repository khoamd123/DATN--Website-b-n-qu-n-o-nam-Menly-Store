@extends('layouts.student')

@section('title', 'Bill chuyển khoản - #' . $transaction->id)
@section('page_title', 'Bill chuyển khoản nộp quỹ')

@section('content')
<div class="content-card">
    <div class="text-center mb-4">
        <h3 class="mb-2">
            <i class="fas fa-receipt text-primary me-2"></i>Bill chuyển khoản
        </h3>
        <p class="text-muted mb-0">Mã giao dịch: #{{ $transaction->id }}</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Bill Content -->
            <div class="bill-container bg-white border rounded p-4 shadow-sm">
                <!-- Header -->
                <div class="text-center border-bottom pb-3 mb-3">
                    <h4 class="mb-1">{{ $club->name ?? 'Câu lạc bộ' }}</h4>
                    <p class="text-muted mb-0">Bill chuyển khoản nộp quỹ</p>
                </div>

                <!-- Transaction Info -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Mã giao dịch:</strong>
                        <div class="text-muted">#{{ $transaction->id }}</div>
                    </div>
                    <div class="col-md-6 text-end">
                        <strong>Ngày tạo:</strong>
                        <div class="text-muted">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</div>
                    </div>
                </div>

                <hr>

                <!-- Payer Info -->
                <div class="mb-3">
                    <h6 class="mb-2"><i class="fas fa-user me-2"></i>Thông tin người nộp</h6>
                    <div class="bg-light p-3 rounded">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Họ tên:</strong> {{ $transaction->payer_name ?: $transaction->creator->name }}
                            </div>
                            @if($transaction->payer_phone)
                                <div class="col-md-6 mb-2">
                                    <strong>Số điện thoại:</strong> {{ $transaction->payer_phone }}
                                </div>
                            @endif
                            <div class="col-md-6 mb-2">
                                <strong>Email:</strong> {{ $transaction->creator->email }}
                            </div>
                            @if($transaction->creator->student_id)
                                <div class="col-md-6 mb-2">
                                    <strong>MSSV:</strong> {{ $transaction->creator->student_id }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="mb-3">
                    <h6 class="mb-2"><i class="fas fa-money-bill-wave me-2"></i>Thông tin thanh toán</h6>
                    <div class="bg-light p-3 rounded">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Số tiền:</strong>
                                <div class="h5 text-success mb-0">{{ number_format($transaction->amount, 0, ',', '.') }} VNĐ</div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Phương thức thanh toán:</strong>
                                <div>
                                    <span class="badge bg-info">{{ $transaction->payment_method ?: 'VietQR' }}</span>
                                </div>
                            </div>
                            @if($transaction->transaction_code)
                                <div class="col-md-6 mb-2">
                                    <strong>Mã giao dịch/Số bill:</strong>
                                    <div><code>{{ $transaction->transaction_code }}</code></div>
                                </div>
                            @endif
                            <div class="col-md-6 mb-2">
                                <strong>Ngày giao dịch:</strong>
                                <div>{{ $transaction->transaction_date ? $transaction->transaction_date->format('d/m/Y') : $transaction->created_at->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                @if($transaction->description)
                    <div class="mb-3">
                        <h6 class="mb-2"><i class="fas fa-comment me-2"></i>Ghi chú</h6>
                        <div class="bg-light p-3 rounded">
                            {{ $transaction->description }}
                        </div>
                    </div>
                @endif

                <!-- Status -->
                <div class="mb-3">
                    <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Trạng thái</h6>
                    <div class="bg-light p-3 rounded">
                        @if($transaction->status === 'pending')
                            <span class="badge bg-warning fs-6">
                                <i class="fas fa-clock me-1"></i>Đang chờ duyệt
                            </span>
                            <p class="mb-0 mt-2 text-muted">
                                Yêu cầu của bạn đã được gửi đến Trưởng CLB và Thủ quỹ để xác nhận. Vui lòng chờ xử lý.
                            </p>
                        @elseif($transaction->status === 'approved')
                            <span class="badge bg-success fs-6">
                                <i class="fas fa-check-circle me-1"></i>Đã được duyệt
                            </span>
                            @if($transaction->approved_at)
                                <p class="mb-0 mt-2 text-muted">
                                    Duyệt bởi: {{ $transaction->approver->name ?? 'N/A' }}<br>
                                    Ngày duyệt: {{ $transaction->approved_at->format('d/m/Y H:i:s') }}
                                </p>
                            @endif
                            <p class="mb-0 mt-2 text-success">
                                <i class="fas fa-check me-1"></i>Số tiền đã được cộng vào quỹ CLB.
                            </p>
                        @elseif($transaction->status === 'rejected')
                            <span class="badge bg-danger fs-6">
                                <i class="fas fa-times-circle me-1"></i>Đã bị từ chối
                            </span>
                            @if($transaction->rejection_reason)
                                <p class="mb-0 mt-2 text-danger">
                                    <strong>Lý do từ chối:</strong> {{ $transaction->rejection_reason }}
                                </p>
                            @endif
                            @if($transaction->approved_at)
                                <p class="mb-0 mt-2 text-muted">
                                    Từ chối bởi: {{ $transaction->approver->name ?? 'N/A' }}<br>
                                    Ngày từ chối: {{ $transaction->approved_at->format('d/m/Y H:i:s') }}
                                </p>
                            @endif
                        @endif
                    </div>
                </div>

                <hr>

                <!-- Footer -->
                <div class="text-center text-muted">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        Bill này được tạo tự động bởi hệ thống UniClubs.<br>
                        Nếu có thắc mắc, vui lòng liên hệ Trưởng CLB hoặc Thủ quỹ.
                    </small>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center mt-4">
                <button onclick="window.print()" class="btn btn-primary me-2">
                    <i class="fas fa-print me-2"></i>In bill
                </button>
                <a href="{{ route('student.club-management.fund-deposit', ['club' => $club->id]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
                @if(in_array($user->getPositionInClub($club->id), ['leader', 'treasurer']))
                    <a href="{{ route('student.club-management.fund-deposit-requests', ['club' => $club->id]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>Danh sách yêu cầu
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .bill-container, .bill-container * {
        visibility: visible;
    }
    .bill-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .btn, .content-card .d-flex {
        display: none !important;
    }
}
</style>
@endsection




