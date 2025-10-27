@extends('admin.layouts.app')

@section('title', 'Duyệt hàng loạt yêu cầu cấp kinh phí')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-check-double"></i>
                        Duyệt hàng loạt yêu cầu cấp kinh phí
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.fund-requests') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($pendingRequests->count() > 0)
                        <!-- Thông tin tổng quan -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Tổng quan</h5>
                                        <p class="mb-1"><strong>{{ $pendingRequests->count() }}</strong> yêu cầu chờ duyệt</p>
                                        <p class="mb-0"><strong>{{ number_format($totalRequestedAmount) }} VNĐ</strong> tổng số tiền yêu cầu</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Hướng dẫn</h5>
                                        <p class="mb-1">• Chọn yêu cầu cần duyệt</p>
                                        <p class="mb-0">• Phân bổ số tiền cho từng yêu cầu</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('admin.fund-requests.batch-approval.process') }}" method="POST" id="batchApprovalForm">
                            @csrf
                            
                            <!-- Tổng số tiền duyệt -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">Thông tin duyệt</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="total_approved_amount">Tổng số tiền duyệt (VNĐ) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control @error('total_approved_amount') is-invalid @enderror" 
                                                       id="total_approved_amount" name="total_approved_amount" 
                                                       value="{{ old('total_approved_amount') }}" min="0" step="1000"
                                                       placeholder="Nhập tổng số tiền có thể duyệt">
                                                @error('total_approved_amount')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="approval_notes">Ghi chú duyệt</label>
                                                <textarea class="form-control" id="approval_notes" name="approval_notes" rows="3" 
                                                          placeholder="Ghi chú về việc duyệt (tùy chọn)">{{ old('approval_notes') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Danh sách yêu cầu -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Danh sách yêu cầu chờ duyệt</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th width="50">
                                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                                    </th>
                                                    <th>Yêu cầu</th>
                                                    <th>Sự kiện</th>
                                                    <th>CLB</th>
                                                    <th>Số tiền yêu cầu</th>
                                                    <th>Số tiền duyệt</th>
                                                    <th>Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pendingRequests as $index => $request)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" class="form-check-input request-checkbox" 
                                                                   name="requests[{{ $index }}][selected]" value="1"
                                                                   data-request-id="{{ $request->id }}">
                                                            <input type="hidden" name="requests[{{ $index }}][id]" value="{{ $request->id }}">
                                                        </td>
                                                        <td>
                                                            <strong>{{ $request->title }}</strong>
                                                            <br><small class="text-muted">{{ Str::limit($request->description, 50) }}</small>
                                                        </td>
                                                        <td>
                                                            @if($request->event)
                                                                <span class="badge badge-primary">{{ $request->event->title }}</span>
                                                            @else
                                                                <span class="text-muted">Không có</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($request->club)
                                                                <span class="badge badge-info">{{ $request->club->name }}</span>
                                                            @else
                                                                <span class="text-muted">Không có</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-right">
                                                            <strong class="text-primary">{{ number_format($request->requested_amount) }} VNĐ</strong>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control approved-amount" 
                                                                   name="requests[{{ $index }}][approved_amount]" 
                                                                   value="0" min="0" max="{{ $request->requested_amount }}" step="1000"
                                                                   disabled>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-warning">Chờ duyệt</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Tóm tắt phân bổ -->
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="alert alert-info">
                                                <strong>Tổng số tiền yêu cầu:</strong> {{ number_format($totalRequestedAmount) }} VNĐ
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="alert alert-warning">
                                                <strong>Tổng số tiền phân bổ:</strong> <span id="totalAllocated">0</span> VNĐ
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Nút thao tác -->
                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                                            <i class="fas fa-check-double"></i> Duyệt hàng loạt
                                        </button>
                                        <a href="{{ route('admin.fund-requests') }}" class="btn btn-secondary btn-lg">
                                            <i class="fas fa-times"></i> Hủy
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Không có yêu cầu nào chờ duyệt</h4>
                            <p class="text-muted">Tất cả yêu cầu đã được xử lý hoặc chưa có yêu cầu nào được tạo.</p>
                            <a href="{{ route('admin.fund-requests') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Quay lại danh sách
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const requestCheckboxes = document.querySelectorAll('.request-checkbox');
    const approvedAmountInputs = document.querySelectorAll('.approved-amount');
    const totalAllocatedSpan = document.getElementById('totalAllocated');
    const submitBtn = document.getElementById('submitBtn');
    const totalApprovedInput = document.getElementById('total_approved_amount');

    // Chọn tất cả
    selectAllCheckbox.addEventListener('change', function() {
        requestCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            const row = checkbox.closest('tr');
            const amountInput = row.querySelector('.approved-amount');
            if (this.checked) {
                amountInput.disabled = false;
                amountInput.value = amountInput.max; // Đặt giá trị mặc định là số tiền yêu cầu
            } else {
                amountInput.disabled = true;
                amountInput.value = 0;
            }
        });
        updateTotalAllocated();
        updateSubmitButton();
    });

    // Chọn từng yêu cầu
    requestCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            const amountInput = row.querySelector('.approved-amount');
            if (this.checked) {
                amountInput.disabled = false;
                amountInput.value = amountInput.max; // Đặt giá trị mặc định
            } else {
                amountInput.disabled = true;
                amountInput.value = 0;
            }
            updateTotalAllocated();
            updateSubmitButton();
        });
    });

    // Cập nhật số tiền duyệt
    approvedAmountInputs.forEach(input => {
        input.addEventListener('input', function() {
            updateTotalAllocated();
            updateSubmitButton();
        });
    });

    // Cập nhật tổng số tiền phân bổ
    function updateTotalAllocated() {
        let total = 0;
        approvedAmountInputs.forEach(input => {
            if (!input.disabled && input.value) {
                total += parseFloat(input.value) || 0;
            }
        });
        totalAllocatedSpan.textContent = total.toLocaleString();
    }

    // Cập nhật trạng thái nút submit
    function updateSubmitButton() {
        const hasSelected = Array.from(requestCheckboxes).some(cb => cb.checked);
        const totalApproved = parseFloat(totalApprovedInput.value) || 0;
        const totalAllocated = parseFloat(totalAllocatedSpan.textContent.replace(/,/g, '')) || 0;
        
        submitBtn.disabled = !hasSelected || totalAllocated > totalApproved;
        
        if (totalAllocated > totalApproved) {
            submitBtn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Vượt quá số tiền duyệt';
            submitBtn.className = 'btn btn-danger btn-lg';
        } else {
            submitBtn.innerHTML = '<i class="fas fa-check-double"></i> Duyệt hàng loạt';
            submitBtn.className = 'btn btn-success btn-lg';
        }
    }

    // Theo dõi thay đổi tổng số tiền duyệt
    totalApprovedInput.addEventListener('input', updateSubmitButton);
});
</script>
@endsection





