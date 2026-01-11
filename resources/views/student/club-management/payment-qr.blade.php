@extends('layouts.student')

@section('title', 'Quản lý QR Code thanh toán - ' . $club->name)
@section('page_title', 'Quản lý QR Code thanh toán')

@section('content')
<div class="content-card mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h5 class="mb-0">{{ $club->name }}</h5>
        <small class="text-muted">Quản lý thông tin chuyển khoản và QR code thanh toán</small>
    </div>
    <a href="{{ route('student.club-management.index') }}?club={{ $club->id }}" class="btn btn-outline-secondary btn-sm">
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

@php
    $existingQr = $paymentQrs->first();
@endphp

<div class="row">
    <!-- Left Column - Form -->
    <div class="col-lg-6 mb-4">
        <div class="content-card">
            <h5 class="mb-4">
                <i class="fas fa-qrcode me-2"></i>
                @if($existingQr)
                    Cập nhật QR Code thanh toán
                @else
                    Thêm QR Code thanh toán
                @endif
            </h5>

            @if($existingQr)
                <form action="{{ route('student.club-management.payment-qr.update', ['club' => $club->id, 'qr' => $existingQr->id]) }}" 
                      method="POST" 
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Số tài khoản <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('account_number') is-invalid @enderror" 
                               name="account_number" 
                               value="{{ old('account_number', $existingQr->account_number) }}" 
                               required
                               placeholder="Nhập số tài khoản">
                        @error('account_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mã ngân hàng</label>
                        <input type="text" 
                               class="form-control @error('bank_code') is-invalid @enderror" 
                               name="bank_code" 
                               value="{{ old('bank_code', $existingQr->bank_code) }}" 
                               placeholder="VD: TCB (Techcombank), VCB (Vietcombank), MB (MBBank)">
                        <small class="form-text text-muted">Mã ngân hàng (tùy chọn)</small>
                        @error('bank_code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tên chủ tài khoản</label>
                        <input type="text" 
                               class="form-control @error('account_name') is-invalid @enderror" 
                               name="account_name" 
                               value="{{ old('account_name', $existingQr->account_name) }}" 
                               placeholder="Nhập tên chủ tài khoản">
                        @error('account_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ảnh QR Code</label>
                        <input type="file" 
                               class="form-control @error('qr_code_image') is-invalid @enderror" 
                               name="qr_code_image" 
                               accept="image/*">
                        <small class="form-text text-muted">Để trống nếu không muốn thay đổi ảnh QR code</small>
                        @error('qr_code_image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Cập nhật thông tin
                    </button>

                    <div class="mt-3">
                        <form action="{{ route('student.club-management.payment-qr.delete', ['club' => $club->id, 'qr' => $existingQr->id]) }}" 
                              method="POST" 
                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa QR code này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash me-2"></i>Xóa QR Code
                            </button>
                        </form>
                    </div>
                </form>
            @else
                <form action="{{ route('student.club-management.payment-qr.store', ['club' => $club->id]) }}" 
                      method="POST" 
                      enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Số tài khoản <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('account_number') is-invalid @enderror" 
                               name="account_number" 
                               value="{{ old('account_number') }}" 
                               required
                               placeholder="Nhập số tài khoản">
                        @error('account_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mã ngân hàng</label>
                        <input type="text" 
                               class="form-control @error('bank_code') is-invalid @enderror" 
                               name="bank_code" 
                               value="{{ old('bank_code') }}" 
                               placeholder="VD: TCB (Techcombank), VCB (Vietcombank), MB (MBBank)">
                        <small class="form-text text-muted">Mã ngân hàng (tùy chọn)</small>
                        @error('bank_code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tên chủ tài khoản</label>
                        <input type="text" 
                               class="form-control @error('account_name') is-invalid @enderror" 
                               name="account_name" 
                               value="{{ old('account_name') }}" 
                               placeholder="Nhập tên chủ tài khoản">
                        @error('account_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ảnh QR Code <span class="text-danger">*</span></label>
                        <input type="file" 
                               class="form-control @error('qr_code_image') is-invalid @enderror" 
                               name="qr_code_image" 
                               accept="image/*"
                               required>
                        <small class="form-text text-muted">Tải lên ảnh QR code thanh toán (PNG, JPG, tối đa 2MB)</small>
                        @error('qr_code_image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Thêm QR Code
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Right Column - Preview -->
    <div class="col-lg-6 mb-4">
        <div class="content-card">
            <h5 class="mb-4">
                <i class="fas fa-eye me-2"></i>Xem trước QR Code
            </h5>

            @if($existingQr && $existingQr->qr_code_image)
                <div class="text-center p-4 bg-light rounded mb-3">
                    <img src="{{ asset($existingQr->qr_code_image) }}" 
                         alt="QR Code" 
                         class="img-fluid" 
                         style="max-width: 300px; height: auto; border: 2px solid #dee2e6; padding: 10px; background: white;">
                </div>

                <div class="info-box p-3 bg-light rounded">
                    <h6 class="mb-3">Thông tin tài khoản</h6>
                    <div class="mb-2">
                        <strong>Số tài khoản:</strong>
                        <div class="font-monospace text-primary">{{ $existingQr->account_number }}</div>
                    </div>
                    @if($existingQr->bank_code)
                        <div class="mb-2">
                            <strong>Ngân hàng:</strong> {{ $existingQr->bank_code }}
                        </div>
                    @endif
                    @if($existingQr->account_name)
                        <div class="mb-2">
                            <strong>Tên chủ tài khoản:</strong> {{ $existingQr->account_name }}
                        </div>
                    @endif
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-3x mb-3 text-muted"></i>
                    <p class="mb-0">Chưa có QR code thanh toán. Hãy thêm QR code để thành viên có thể nộp quỹ.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
