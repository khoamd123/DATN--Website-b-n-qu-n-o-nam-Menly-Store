@extends('layouts.student')

@section('title', 'Nộp quỹ CLB - ' . $club->name)
@section('page_title', 'Nộp quỹ CLB')

@section('content')
<div class="content-card mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h5 class="mb-0">{{ $club->name }}</h5>
        <small class="text-muted">Nộp quỹ CLB qua QR Code</small>
    </div>
    <a href="{{ route('student.clubs.show', $club->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Quay lại
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(!$paymentQr)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        CLB này chưa có QR code thanh toán. Vui lòng liên hệ Trưởng CLB để được hỗ trợ.
    </div>
@else
<div class="row">
    <!-- Left Column - Fund Deposit Form -->
    <div class="col-lg-6 mb-4">
        <div class="content-card">
            <div class="d-flex align-items-center mb-4">
                <i class="fas fa-wallet text-primary me-2 fs-4"></i>
                <h5 class="mb-0">Thông tin nộp quỹ</h5>
            </div>

            <form method="POST" action="{{ route('student.club-management.fund-deposit.submit') }}" id="fundDepositForm">
                @csrf
                <input type="hidden" name="club_id" value="{{ $club->id }}">

                <div class="mb-3">
                    <label class="form-label">Số tiền nộp <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                        <input type="text" 
                               class="form-control @error('amount') is-invalid @enderror" 
                               name="amount" 
                               id="amountInput"
                               value="{{ old('amount', $amount ? number_format($amount, 0, ',', '.') : '') }}" 
                               required 
                               placeholder="Nhập số tiền"
                               autocomplete="off">
                        <input type="hidden" name="amount_raw" id="amountRaw">
                        <span class="input-group-text">VNĐ</span>
                    </div>
                    <small class="form-text text-muted">Số tiền tối thiểu: 1.000 VNĐ</small>
                    @error('amount')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Phương thức thanh toán <span class="text-danger">*</span></label>
                    <select name="payment_method" 
                            class="form-select @error('payment_method') is-invalid @enderror" 
                            id="paymentMethodSelect"
                            required>
                        @foreach($paymentMethods as $key => $method)
                            <option value="{{ $key }}" {{ old('payment_method', $paymentQr->payment_method ?? 'VietQR') == $key ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_method')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Mã giao dịch / Số bill</label>
                    <input type="text" 
                           class="form-control @error('transaction_code') is-invalid @enderror" 
                           name="transaction_code" 
                           value="{{ old('transaction_code') }}" 
                           placeholder="VD: FT25010812345"
                           maxlength="255">
                    <small class="form-text text-muted">Mã tham chiếu từ ngân hàng (nếu có)</small>
                    @error('transaction_code')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Tên người nộp</label>
                    <input type="text" 
                           class="form-control @error('payer_name') is-invalid @enderror" 
                           name="payer_name" 
                           value="{{ old('payer_name', $user->name) }}" 
                           placeholder="Nhập tên người nộp"
                           maxlength="255">
                    @error('payer_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Số điện thoại liên hệ</label>
                    <input type="tel" 
                           class="form-control @error('payer_phone') is-invalid @enderror" 
                           name="payer_phone" 
                           value="{{ old('payer_phone', $user->phone ?? '') }}" 
                           placeholder="Nhập số điện thoại"
                           maxlength="20">
                    @error('payer_phone')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" 
                              class="form-control @error('note') is-invalid @enderror" 
                              rows="3" 
                              placeholder="Ghi chú thêm (nếu có)"
                              maxlength="1000">{{ old('note') }}</textarea>
                    @error('note')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Lưu ý:</strong> Sau khi thanh toán, yêu cầu của bạn sẽ được gửi đến Trưởng CLB và Thủ quỹ để xác nhận. Vui lòng chờ xác nhận.
                </div>

                <button type="submit" class="btn btn-success w-100 btn-lg">
                    <i class="fas fa-money-bill-wave me-2"></i>Thanh toán
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column - QR Code -->
    <div class="col-lg-6 mb-4">
        <div class="content-card">
            <div class="d-flex align-items-center mb-4">
                <i class="fas fa-qrcode text-success me-2 fs-4"></i>
                <h5 class="mb-0">Quét mã QR để chuyển khoản</h5>
            </div>

            @if($paymentQr && $paymentQr->qr_code_image)
                <div class="text-center p-4 bg-light rounded mb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="bg-white rounded p-4 d-inline-block">
                        <img src="{{ asset($paymentQr->qr_code_image) }}" 
                             alt="QR Code" 
                             class="img-fluid" 
                             style="max-width: 300px; height: auto;">
                        
                        <div class="mt-3">
                            @if($paymentQr->bank_code)
                                <small class="text-muted d-block mb-2">
                                    {{ $paymentQr->bank_code }}
                                </small>
                            @endif
                            <div class="mb-2">
                                <strong class="font-monospace">{{ $paymentQr->account_number }}</strong>
                            </div>
                            @if($paymentQr->account_name)
                                <div class="text-muted small">
                                    {{ $paymentQr->account_name }}
                                </div>
                            @endif
                            <div class="mt-2">
                                <strong>Số tiền: <span id="amountDisplay">{{ number_format($amount, 0, ',', '.') }}</span> VNĐ</strong>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    QR code chưa được cấu hình. Vui lòng liên hệ Trưởng CLB.
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Danh sách Bill nộp quỹ -->
<div class="row mt-4">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <i class="fas fa-receipt me-2"></i>Danh sách bill nộp quỹ
                </h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-info">Tổng: {{ $billStats['total'] }}</span>
                    @if($billStats['pending'] > 0)
                        <span class="badge bg-warning">Chờ: {{ $billStats['pending'] }}</span>
                    @endif
                    @if($billStats['approved'] > 0)
                        <span class="badge bg-success">Đã duyệt: {{ $billStats['approved'] }}</span>
                    @endif
                </div>
            </div>

            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ $billStatus === 'all' ? 'active' : '' }}" 
                       href="{{ route('student.club-management.fund-deposit', ['club' => $club->id, 'status' => 'all']) }}">
                        Tất cả ({{ $billStats['total'] }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $billStatus === 'pending' ? 'active' : '' }}" 
                       href="{{ route('student.club-management.fund-deposit', ['club' => $club->id, 'status' => 'pending']) }}">
                        Chờ duyệt ({{ $billStats['pending'] }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $billStatus === 'approved' ? 'active' : '' }}" 
                       href="{{ route('student.club-management.fund-deposit', ['club' => $club->id, 'status' => 'approved']) }}">
                        Đã duyệt ({{ $billStats['approved'] }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $billStatus === 'rejected' ? 'active' : '' }}" 
                       href="{{ route('student.club-management.fund-deposit', ['club' => $club->id, 'status' => 'rejected']) }}">
                        Đã từ chối ({{ $billStats['rejected'] }})
                    </a>
                </li>
            </ul>

            <!-- Bills List -->
            @if($bills->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã GD</th>
                                <th>Số tiền</th>
                                <th>Phương thức</th>
                                <th>Mã GD/Số bill</th>
                                <th>Ngày nộp</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bills as $bill)
                                <tr>
                                    <td>
                                        <strong>#{{ $bill->id }}</strong>
                                    </td>
                                    <td>
                                        <strong class="text-success">{{ number_format($bill->amount, 0, ',', '.') }} VNĐ</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $bill->payment_method ?: 'VietQR' }}</span>
                                    </td>
                                    <td>
                                        @if($bill->transaction_code)
                                            <code>{{ $bill->transaction_code }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $bill->transaction_date ? $bill->transaction_date->format('d/m/Y H:i') : $bill->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        @if($bill->status === 'pending')
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>Chờ duyệt
                                            </span>
                                        @elseif($bill->status === 'approved')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>Đã duyệt
                                            </span>
                                            @if($bill->approved_at)
                                                <br><small class="text-muted">{{ $bill->approved_at->format('d/m/Y H:i') }}</small>
                                            @endif
                                        @elseif($bill->status === 'rejected')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle me-1"></i>Đã từ chối
                                            </span>
                                            @if($bill->rejection_reason)
                                                <br><small class="text-muted" title="{{ $bill->rejection_reason }}">{{ Str::limit($bill->rejection_reason, 30) }}</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('student.club-management.fund-deposit.bill', ['transaction' => $bill->id]) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Xem bill">
                                            <i class="fas fa-receipt me-1"></i>Xem bill
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $bills->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">
                        @if($billStatus === 'all')
                            Bạn chưa có bill nộp quỹ nào cho CLB này.
                        @elseif($billStatus === 'pending')
                            Không có bill nào đang chờ duyệt.
                        @elseif($billStatus === 'approved')
                            Không có bill nào đã được duyệt.
                        @elseif($billStatus === 'rejected')
                            Không có bill nào bị từ chối.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amountInput');
    const amountRaw = document.getElementById('amountRaw');
    const amountDisplay = document.getElementById('amountDisplay');
    const fundDepositForm = document.getElementById('fundDepositForm');

    // Hàm format số tiền theo định dạng VN (1.000.000)
    function formatCurrency(value) {
        // Loại bỏ tất cả ký tự không phải số
        const numbers = value.toString().replace(/[^\d]/g, '');
        
        if (!numbers) return '';
        
        // Format với dấu chấm phân cách hàng nghìn
        return numbers.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Hàm lấy giá trị số thực (loại bỏ định dạng)
    function getRawValue(value) {
        return value.toString().replace(/[^\d]/g, '');
    }

    // Format số tiền khi user nhập
    if (amountInput) {
        amountInput.addEventListener('input', function(e) {
            const rawValue = getRawValue(e.target.value);
            const formattedValue = formatCurrency(rawValue);
            
            // Cập nhật giá trị hiển thị
            e.target.value = formattedValue;
            
            // Lưu giá trị số thực vào hidden input
            if (amountRaw) {
                amountRaw.value = rawValue;
            }
            
            // Cập nhật hiển thị số tiền ở QR code section (nếu có)
            if (amountDisplay) {
                if (rawValue) {
                    amountDisplay.textContent = formatCurrency(rawValue);
                } else {
                    amountDisplay.textContent = '0';
                }
            }
        });

        // Xử lý khi blur (khi user rời khỏi input)
        amountInput.addEventListener('blur', function(e) {
            const rawValue = getRawValue(e.target.value);
            const numValue = parseInt(rawValue) || 0;
            
            // Kiểm tra số tiền tối thiểu
            if (numValue > 0 && numValue < 1000) {
                e.target.value = '1.000';
                if (amountRaw) {
                    amountRaw.value = '1000';
                }
                if (amountDisplay) {
                    amountDisplay.textContent = '1.000';
                }
            }
        });

        // Xử lý khi paste
        amountInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = (e.clipboardData || window.clipboardData).getData('text');
            const rawValue = getRawValue(pastedData);
            const formattedValue = formatCurrency(rawValue);
            
            this.value = formattedValue;
            if (amountRaw) {
                amountRaw.value = rawValue;
            }
            if (amountDisplay) {
                amountDisplay.textContent = formattedValue || '0';
            }
        });
    }

    // Trước khi submit form, chuyển giá trị đã format về số thực
    if (fundDepositForm) {
        fundDepositForm.addEventListener('submit', function(e) {
            const rawValue = getRawValue(amountInput.value);
            const numValue = parseInt(rawValue) || 0;
            
            // Kiểm tra số tiền tối thiểu
            if (numValue < 1000) {
                e.preventDefault();
                alert('Số tiền tối thiểu là 1.000 VNĐ');
                amountInput.focus();
                return false;
            }
            
            // Gán giá trị số thực vào input để submit
            amountInput.value = numValue;
        });
    }
});
</script>
@endpush
@endsection

