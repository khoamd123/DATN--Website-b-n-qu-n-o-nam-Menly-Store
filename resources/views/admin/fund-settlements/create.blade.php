@extends('admin.layouts.app')

@section('title', 'Quyết toán kinh phí - CLB Admin')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Quyết toán kinh phí</h1>
        <a href="{{ route('admin.fund-settlements') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
    <p class="text-muted">Nhập thông tin quyết toán sau khi chi tiêu</p>
</div>

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">
    <!-- Thông tin yêu cầu -->
    <div class="col-lg-4 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle text-info"></i> Thông tin yêu cầu
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Tiêu đề:</strong></td>
                        <td>{{ $fundRequest->title }}</td>
                    </tr>
                    <tr>
                        <td><strong>CLB:</strong></td>
                        <td>
                            <span class="badge bg-primary">{{ $fundRequest->club->name }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Sự kiện:</strong></td>
                        <td>
                            @if($fundRequest->event)
                                <span class="badge bg-info">{{ $fundRequest->event->name }}</span>
                            @else
                                <span class="text-muted">Không có</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Số tiền yêu cầu:</strong></td>
                        <td>
                            <span class="text-primary">{{ number_format($fundRequest->requested_amount) }} VNĐ</span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Số tiền duyệt:</strong></td>
                        <td>
                            <span class="text-success fw-bold">{{ number_format($fundRequest->approved_amount) }} VNĐ</span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Ngày duyệt:</strong></td>
                        <td>{{ $fundRequest->approved_at ? $fundRequest->approved_at->format('d/m/Y H:i') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Người duyệt:</strong></td>
                        <td>{{ $fundRequest->approver->name ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Hướng dẫn -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-lightbulb text-warning"></i> Hướng dẫn
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> 
                        Nhập số tiền thực tế đã chi tiêu
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> 
                        Upload hóa đơn/chứng từ (bắt buộc nếu ≥ 1 triệu VNĐ)
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> 
                        Ghi chú chi tiết về việc chi tiêu
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Form quyết toán -->
    <div class="col-lg-8 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calculator text-success"></i> Thông tin quyết toán
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.fund-settlements.store', $fundRequest->id) }}" 
                      method="POST" 
                      enctype="multipart/form-data">
                    @csrf

                    <!-- Số tiền thực tế -->
                    <div class="mb-3">
                        <label for="actual_amount" class="form-label">
                            Số tiền thực tế đã chi tiêu <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control @error('actual_amount') is-invalid @enderror" 
                                   id="actual_amount" 
                                   name="actual_amount" 
                                   value="{{ old('actual_amount') }}"
                                   min="0" 
                                   max="{{ $fundRequest->approved_amount }}"
                                   step="1000"
                                   required
                                   oninput="calculateDifference()">
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <div class="form-text">
                            Tối đa: {{ number_format($fundRequest->approved_amount) }} VNĐ
                        </div>
                        
                        <!-- Hiển thị tiền thừa/thiếu -->
                        <div id="difference-alert" style="display: none;" class="mt-2"></div>
                        
                        @error('actual_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ghi chú quyết toán -->
                    <div class="mb-3">
                        <label for="settlement_notes" class="form-label">Ghi chú quyết toán</label>
                        <textarea class="form-control @error('settlement_notes') is-invalid @enderror" 
                                  id="settlement_notes" 
                                  name="settlement_notes" 
                                  rows="4" 
                                  placeholder="Mô tả chi tiết về việc chi tiêu, các khoản đã sử dụng...">{{ old('settlement_notes') }}</textarea>
                        @error('settlement_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Upload hóa đơn/chứng từ -->
                    <div class="mb-3">
                        <label for="settlement_documents" class="form-label">
                            Hóa đơn/Chứng từ <span class="text-danger">*</span>
                            <small class="text-muted">(Bắt buộc nếu số tiền ≥ 1 triệu VNĐ)</small>
                        </label>
                        <input type="file" 
                               class="form-control @error('settlement_documents') is-invalid @enderror" 
                               id="settlement_documents" 
                               name="settlement_documents[]" 
                               multiple 
                               accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">
                            Chấp nhận: PDF, JPG, PNG (tối đa 5MB/file, 10 files)
                        </div>
                        @error('settlement_documents')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Preview files -->
                    <div id="file-preview" class="mb-3" style="display: none;">
                        <label class="form-label">Files đã chọn:</label>
                        <div id="file-list" class="list-group"></div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Hoàn tất quyết toán
                        </button>
                        <a href="{{ route('admin.fund-settlements') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('settlement_documents');
    const filePreview = document.getElementById('file-preview');
    const fileList = document.getElementById('file-list');

    fileInput.addEventListener('change', function() {
        const files = Array.from(this.files);
        
        if (files.length > 0) {
            filePreview.style.display = 'block';
            fileList.innerHTML = '';
            
            files.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                fileItem.innerHTML = `
                    <div>
                        <i class="fas fa-file text-primary me-2"></i>
                        <span>${file.name}</span>
                        <small class="text-muted ms-2">(${(file.size / 1024 / 1024).toFixed(2)} MB)</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                fileList.appendChild(fileItem);
            });
        } else {
            filePreview.style.display = 'none';
        }
    });
});

function removeFile(index) {
    const fileInput = document.getElementById('settlement_documents');
    const dt = new DataTransfer();
    const files = Array.from(fileInput.files);
    
    files.forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    fileInput.files = dt.files;
    fileInput.dispatchEvent(new Event('change'));
}

function calculateDifference() {
    const approvedAmount = {{ $fundRequest->approved_amount }};
    const actualAmount = parseFloat(document.getElementById('actual_amount').value) || 0;
    const difference = approvedAmount - actualAmount;
    const alertDiv = document.getElementById('difference-alert');
    
    if (actualAmount > 0) {
        if (difference > 0) {
            alertDiv.innerHTML = `
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i>
                    <strong>Có tiền thừa:</strong> ${difference.toLocaleString('vi-VN')} VNĐ
                    <br><small>Hệ thống sẽ tự động hoàn tiền thừa vào quỹ CLB.</small>
                </div>
            `;
        } else if (difference < 0) {
            alertDiv.innerHTML = `
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Cảnh báo:</strong> Số tiền thực tế lớn hơn số tiền duyệt!
                    <br><small>Chênh lệch: ${Math.abs(difference).toLocaleString('vi-VN')} VNĐ</small>
                </div>
            `;
        } else {
            alertDiv.innerHTML = `
                <div class="alert alert-success mb-0">
                    <i class="fas fa-check-circle"></i>
                    <strong>Khớp số tiền!</strong> Không có chênh lệch.
                </div>
            `;
        }
        alertDiv.style.display = 'block';
    } else {
        alertDiv.style.display = 'none';
    }
}
</script>

<style>
.row {
    margin-left: 0;
    margin-right: 0;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    max-width: 100%;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.table {
    width: 100%;
    max-width: 100%;
    table-layout: fixed;
}

.table td {
    padding: 0.5rem;
    border-top: 1px solid #dee2e6;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.badge {
    font-size: 0.75rem;
}

.list-group-item {
    border: 1px solid #dee2e6;
    margin-bottom: 0.25rem;
}

@media (max-width: 991.98px) {
    .col-lg-4, .col-lg-8 {
        margin-bottom: 1rem;
    }
}
</style>
@endsection
