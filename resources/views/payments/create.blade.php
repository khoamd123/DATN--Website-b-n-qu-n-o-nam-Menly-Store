@extends('layouts.student')

@section('title', 'Thanh toán online')
@section('page_title', 'Thanh toán online')

@section('content')
<div class="content-card">
    <h5 class="mb-4"><i class="fas fa-credit-card me-2"></i>Thông tin thanh toán</h5>
    
    <form method="POST" action="{{ route('payments.store') }}">
        @csrf
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Số tiền (VNĐ) <span class="text-danger">*</span></label>
                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" 
                       value="{{ old('amount', $amount ?? '') }}" min="1000" step="1000" required>
                @error('amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Số tiền tối thiểu: 1,000 VNĐ</small>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Loại thanh toán <span class="text-danger">*</span></label>
                <select name="payment_type" class="form-select @error('payment_type') is-invalid @enderror" required>
                    <option value="">-- Chọn loại thanh toán --</option>
                    <option value="event_registration" {{ old('payment_type', $paymentType ?? '') == 'event_registration' ? 'selected' : '' }}>
                        Đăng ký tham gia sự kiện
                    </option>
                    <option value="club_fee" {{ old('payment_type', $paymentType ?? '') == 'club_fee' ? 'selected' : '' }}>
                        Đóng phí CLB
                    </option>
                    <option value="fund_contribution" {{ old('payment_type', $paymentType ?? '') == 'fund_contribution' ? 'selected' : '' }}>
                        Đóng góp quỹ
                    </option>
                    <option value="other" {{ old('payment_type', $paymentType ?? '') == 'other' ? 'selected' : '' }}>
                        Khác
                    </option>
                </select>
                @error('payment_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Phương thức thanh toán <span class="text-danger">*</span></label>
                <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                    <option value="vnpay" {{ old('payment_method') == 'vnpay' ? 'selected' : '' }}>VNPay</option>
                </select>
                @error('payment_method')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            @if(isset($fund) && $fund)
                <input type="hidden" name="fund_id" value="{{ $fund->id }}">
            @endif
            
            @if(isset($event) && $event)
                <input type="hidden" name="event_id" value="{{ $event->id }}">
            @endif
            
            @if(isset($club) && $club)
                <input type="hidden" name="club_id" value="{{ $club->id }}">
            @endif
        </div>
        
        <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" 
                      placeholder="Nhập mô tả thanh toán (tùy chọn)">{{ old('description') }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        @if(isset($fund) && $fund)
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Quỹ:</strong> {{ $fund->name }}<br>
                <strong>Số dư hiện tại:</strong> {{ number_format($fund->current_amount, 0, ',', '.') }} VNĐ
            </div>
        @endif
        
        @if(isset($event) && $event)
            <div class="alert alert-info">
                <i class="fas fa-calendar me-2"></i>
                <strong>Sự kiện:</strong> {{ $event->name }}
            </div>
        @endif
        
        <div class="d-flex justify-content-between">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
            <div>
                <a href="{{ route('payments.history') }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-history me-2"></i>Lịch sử
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-credit-card me-2"></i>Tiếp tục thanh toán
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

