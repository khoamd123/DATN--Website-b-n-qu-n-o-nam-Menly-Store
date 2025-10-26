@extends('admin.layouts.app')

@section('title', 'Chi tiết yêu cầu cấp kinh phí')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-money-bill-wave"></i>
                        Chi tiết yêu cầu cấp kinh phí
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.fund-requests') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        @if($fundRequest->status === 'pending')
                            <a href="{{ route('admin.fund-requests.edit', $fundRequest->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <!-- Thông tin cơ bản -->
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title text-dark">Thông tin yêu cầu</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Tiêu đề:</strong>
                                            <p>{{ $fundRequest->title }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Trạng thái:</strong>
                                            <p>
                                                @switch($fundRequest->status)
                                                    @case('pending')
                                                        <span class="badge badge-warning">Chờ duyệt</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge badge-success">Đã duyệt</span>
                                                        @break
                                                    @case('partially_approved')
                                                        <span class="badge badge-info">Duyệt một phần</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge badge-danger">Từ chối</span>
                                                        @break
                                                @endswitch
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Số tiền yêu cầu:</strong>
                                            <p class="text-primary font-weight-bold">{{ number_format($fundRequest->requested_amount) }} VNĐ</p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Số tiền duyệt:</strong>
                                            <p class="text-success font-weight-bold">
                                                {{ $fundRequest->approved_amount ? number_format($fundRequest->approved_amount) . ' VNĐ' : 'Chưa duyệt' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Sự kiện:</strong>
                                            <p>
                                                @if($fundRequest->event)
                                                    <a href="{{ route('admin.events.show', $fundRequest->event->id) }}" class="text-primary">
                                                        {{ $fundRequest->event->name }}
                                                    </a>
                                                    <br><small class="text-muted">{{ $fundRequest->event->start_time ? $fundRequest->event->start_time->format('d/m/Y H:i') : 'Chưa có ngày' }}</small>
                                                @else
                                                    <span class="text-muted">Không có</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>CLB:</strong>
                                            <p>
                                                @if($fundRequest->club)
                                                    <span class="badge badge-info text-dark">{{ $fundRequest->club->name }}</span>
                                                @else
                                                    <span class="text-muted">Không có</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Người tạo:</strong>
                                            <p>{{ $fundRequest->creator ? $fundRequest->creator->name : 'Không có' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Ngày tạo:</strong>
                                            <p>{{ $fundRequest->created_at ? $fundRequest->created_at->format('d/m/Y H:i') : 'Chưa có ngày' }}</p>
                                        </div>
                                    </div>

                                    @if($fundRequest->approver)
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Người duyệt:</strong>
                                                <p>{{ $fundRequest->approver->name }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Ngày duyệt:</strong>
                                                <p>{{ $fundRequest->approved_at ? $fundRequest->approved_at->format('d/m/Y H:i') : 'Chưa duyệt' }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <strong>Mô tả chi tiết:</strong>
                                        <p class="mt-2">{{ $fundRequest->description }}</p>
                                    </div>

                                    @if($fundRequest->approval_notes)
                                        <div class="form-group">
                                            <strong>Ghi chú duyệt:</strong>
                                            <p class="mt-2 text-info">{{ $fundRequest->approval_notes }}</p>
                                        </div>
                                    @endif

                                    @if($fundRequest->rejection_reason)
                                        <div class="form-group">
                                            <strong>Lý do từ chối:</strong>
                                            <p class="mt-2 text-danger">{{ $fundRequest->rejection_reason }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Chi tiết chi phí -->
                            @if($fundRequest->expense_items && count($fundRequest->expense_items) > 0)
                                <div class="card mt-3">
                                    <div class="card-header bg-white">
                                        <h5 class="card-title text-dark">Chi tiết chi phí</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Khoản mục</th>
                                                        <th class="text-right">Số tiền (VNĐ)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($fundRequest->expense_items as $item)
                                                        <tr>
                                                            <td>{{ $item['item'] }}</td>
                                                            <td class="text-right">{{ number_format($item['amount']) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="font-weight-bold">
                                                        <td>Tổng cộng</td>
                                                        <td class="text-right">{{ number_format($fundRequest->requested_amount) }}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Thao tác duyệt -->
                            @if($fundRequest->status === 'pending')
                                <div class="card mt-3">
                                    <div class="card-header bg-white">
                                        <h5 class="card-title text-dark">Thao tác duyệt</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- Form duyệt -->
                                        <form action="{{ route('admin.fund-requests.approve', $fundRequest->id) }}" method="POST" id="approve-form">
                                            @csrf
                                            
                                            <!-- Duyệt tổng số tiền -->
                                            <div class="form-group mb-3">
                                                <label for="approved_amount">Số tiền duyệt tổng (VNĐ)</label>
                                                <input type="number" class="form-control" id="approved_amount" name="approved_amount" 
                                                       value="{{ $fundRequest->requested_amount }}" min="0" max="{{ $fundRequest->requested_amount }}" step="1000">
                                            </div>
                                            
                                            <!-- Duyệt từng mục chi phí -->
                                            @if($fundRequest->expense_items && count($fundRequest->expense_items) > 0)
                                                <div class="form-group mb-3">
                                                    <label>Duyệt từng mục chi phí</label>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width: 50px;">Duyệt</th>
                                                                    <th>Khoản mục</th>
                                                                    <th style="width: 150px;">Số tiền yêu cầu</th>
                                                                    <th style="width: 150px;">Số tiền duyệt</th>
                                                                    <th>Lý do từ chối</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($fundRequest->expense_items as $index => $item)
                                                                    <tr class="expense-row" data-index="{{ $index }}">
                                                                        <td class="text-center align-middle">
                                                                            <input type="checkbox" class="form-check-input expense-approve" 
                                                                                   name="expense_approvals[{{ $index }}][approved]" value="1" checked>
                                                                        </td>
                                                                        <td class="align-middle">{{ $item['item'] }}</td>
                                                                        <td class="text-right align-middle"><small>{{ number_format($item['amount']) }}₫</small></td>
                                                                        <td>
                                                                            <input type="number" class="form-control form-control-sm expense-amount" 
                                                                                   name="expense_approvals[{{ $index }}][amount]" 
                                                                                   value="{{ $item['amount'] }}" min="0" max="{{ $item['amount'] }}" step="1000"
                                                                                   data-original="{{ $item['amount'] }}">
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" class="form-control form-control-sm expense-reject-reason" 
                                                                                   name="expense_approvals[{{ $index }}][reject_reason]" 
                                                                                   placeholder="Nhập lý do nếu từ chối">
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endif
                                        </form>

                                                                                 <!-- Form từ chối toàn bộ -->
                                         <form action="{{ route('admin.fund-requests.reject', $fundRequest->id) }}" method="POST" id="reject-form" style="display: none;">
                                             @csrf
                                             <div class="form-group mb-3">
                                                 <label for="rejection_reason">Lý do từ chối</label>
                                                 <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" 
                                                           placeholder="Nhập lý do từ chối yêu cầu" required></textarea>
                                             </div>
                                         </form>

                                        <!-- Tài liệu hỗ trợ -->
                                        @if($fundRequest->supporting_documents && count($fundRequest->supporting_documents) > 0)
                                            <div class="card bg-light">
                                                <div class="card-header bg-white">
                                                    <h6 class="card-title mb-0 text-dark">
                                                        <i class="fas fa-file-alt"></i> Tài liệu hỗ trợ ({{ count($fundRequest->supporting_documents) }})
                                                    </h6>
                                                </div>
                                                <div class="card-body p-2">
                                                    <div class="row">
                                                                                                                @foreach($fundRequest->supporting_documents as $index => $document)
                                                            @php
                                                                // Xử lý đúng cách nếu là array hoặc string
                                                                if (is_array($document)) {
                                                                    // Nếu là array có key 'path'
                                                                    if (isset($document['path'])) {
                                                                        $docPath = $document['path'];
                                                                    }
                                                                    // Nếu là array có key 0
                                                                    elseif (isset($document[0])) {
                                                                        $docPath = $document[0];
                                                                    }
                                                                    // Nếu không có key nào, lấy giá trị đầu tiên
                                                                    else {
                                                                        $docPath = reset($document);
                                                                    }
                                                                    $docName = $document['name'] ?? ('Tài liệu ' . ($index + 1));
                                                                } else {
                                                                    // Nếu là string
                                                                    $docPath = $document;
                                                                    $docName = basename($document);
                                                                }
                                                            @endphp
                                                            <div class="col-md-3 mb-2">
                                                                <a href="{{ asset('storage/' . $docPath) }}" target="_blank" 
                                                                    class="btn btn-outline-primary btn-sm btn-block text-truncate"
                                                                    title="{{ $docName }}">
                                                                    <i class="fas fa-file-pdf"></i> Tài liệu {{ $index + 1 }}
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="card bg-light">
                                                <div class="card-header bg-white">
                                                    <h6 class="card-title mb-0 text-dark">
                                                        <i class="fas fa-file-alt"></i> Tài liệu hỗ trợ
                                                    </h6>
                                                </div>
                                                <div class="card-body p-2 text-center text-muted">
                                                    <small>Không có tài liệu</small>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Buttons -->
                                        <div class="row mt-3">
                                            <div class="col-md-6 mb-2">
                                                <button type="submit" form="approve-form" class="btn btn-success btn-block btn-sm">
                                                    <i class="fas fa-check"></i> Duyệt yêu cầu
                                                </button>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <button type="button" id="show-reject-form" class="btn btn-danger btn-block btn-sm">
                                                    <i class="fas fa-times"></i> Từ chối yêu cầu
                                                </button>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" form="reject-form" id="confirm-reject-btn" class="btn btn-danger btn-block btn-sm" style="display: none;"
                                                        onclick="return confirm('Bạn có chắc chắn muốn từ chối yêu cầu này?')">
                                                    <i class="fas fa-check"></i> Xác nhận từ chối
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                                                                                   @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const expenseApproves = document.querySelectorAll('.expense-approve');
    const expenseAmounts = document.querySelectorAll('.expense-amount');
    const totalApprovedInput = document.getElementById('approved_amount');
    
    // Xử lý khi bỏ chọn một mục
    expenseApproves.forEach((checkbox, index) => {
        checkbox.addEventListener('change', function() {
            const amountInput = expenseAmounts[index];
            const row = this.closest('tr');
            const reasonInput = row.querySelector('.expense-reject-reason');
            
            if (!this.checked) {
                // Bỏ chọn: Vô hiệu hóa số tiền, hiện textarea lý do
                amountInput.value = 0;
                amountInput.disabled = true;
                if (reasonInput) {
                    reasonInput.style.display = 'block';
                    reasonInput.required = true;
                }
            } else {
                // Chọn lại: Kích hoạt số tiền, ẩn lý do
                amountInput.disabled = false;
                amountInput.value = amountInput.dataset.original;
                if (reasonInput) {
                    reasonInput.style.display = 'none';
                    reasonInput.required = false;
                    reasonInput.value = '';
                }
            }
            updateTotalApproved();
        });
        
        // Ẩn ô lý do ban đầu nếu đã được duyệt
        if (checkbox.checked) {
            const row = checkbox.closest('tr');
            const reasonInput = row.querySelector('.expense-reject-reason');
            if (reasonInput) {
                reasonInput.style.display = 'none';
            }
        }
    });
    
    // Xử lý khi thay đổi số tiền duyệt
    expenseAmounts.forEach(input => {
        input.addEventListener('input', updateTotalApproved);
    });
    
    // Cập nhật tổng số tiền duyệt
    function updateTotalApproved() {
        let total = 0;
        expenseApproves.forEach((checkbox, index) => {
            if (checkbox.checked) {
                const amount = parseFloat(expenseAmounts[index].value) || 0;
                total += amount;
            }
        });
        totalApprovedInput.value = total;
    }
    
    // Xử lý nút "Từ chối yêu cầu"
    const showRejectBtn = document.getElementById('show-reject-form');
    const confirmRejectBtn = document.getElementById('confirm-reject-btn');
    const rejectForm = document.getElementById('reject-form');
    
    if (showRejectBtn) {
        showRejectBtn.addEventListener('click', function() {
            // Ẩn nút "Từ chối yêu cầu"
            this.style.display = 'none';
            // Hiện form từ chối
            rejectForm.style.display = 'block';
            // Hiện nút "Xác nhận từ chối"
            confirmRejectBtn.style.display = 'block';
        });
    }
    
    // Khởi tạo tổng số tiền
    updateTotalApproved();
});
</script>
@endsection
