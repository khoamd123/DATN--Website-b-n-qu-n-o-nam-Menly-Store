@extends('admin.layouts.app')

@section('title', 'Chi tiết yêu cầu cấp kinh phí')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                        <i class="fas fa-money-bill-wave"></i>
                        Chi tiết yêu cầu cấp kinh phí
                    </h3>
                        <div class="d-flex gap-2">
                        @if($fundRequest->status === 'pending')
                            <a href="{{ route('admin.fund-requests.edit', $fundRequest->id) }}" class="btn btn-warning text-white">
                                    <i class="fas fa-edit me-1"></i> Chỉnh sửa
                            </a>
                            <button type="button" class="btn btn-success text-white" data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i class="fas fa-check-circle me-1"></i> Duyệt yêu cầu
                            </button>
                            <button type="button" class="btn btn-danger text-white" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fas fa-times-circle me-1"></i> Từ chối yêu cầu
                            </button>
                        @endif
                            <a href="{{ route('admin.fund-requests') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                        </div>
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
                                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge bg-success text-white">Đã duyệt</span>
                                                        @break
                                                    @case('partially_approved')
                                                        <span class="badge bg-info text-dark">Duyệt một phần</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge bg-danger text-white">Từ chối</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary text-white">{{ ucfirst($fundRequest->status) }}</span>
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
                                                        <strong>{{ $fundRequest->event->title ?? $fundRequest->event->name ?? 'Sự kiện #' . $fundRequest->event->id }}</strong>
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
                                                    <a href="{{ route('admin.clubs.show', $fundRequest->club->id) }}" class="badge bg-info text-dark text-decoration-none">
                                                        {{ $fundRequest->club->name }}
                                                    </a>
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
                                        <div class="mt-2">{!! nl2br(e(str_replace('&nbsp;', ' ', strip_tags($fundRequest->description)))) !!}</div>
                                    </div>

                                    @if($fundRequest->approval_notes)
                                        <div class="form-group">
                                            <strong>Ghi chú duyệt:</strong>
                                            <div class="mt-2 text-info">{!! nl2br(e(str_replace('&nbsp;', ' ', strip_tags($fundRequest->approval_notes)))) !!}</div>
                                        </div>
                                    @endif

                                    @if($fundRequest->rejection_reason)
                                        <div class="form-group">
                                            <strong>Lý do từ chối:</strong>
                                            <div class="mt-2 text-danger">{!! nl2br(e(str_replace('&nbsp;', ' ', strip_tags($fundRequest->rejection_reason)))) !!}</div>
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

                            <!-- Chi tiết duyệt (Ẩn, chỉ hiện trong modal) -->
                            @if($fundRequest->status === 'pending')
                                <!-- Form duyệt -->
                                <form action="{{ route('admin.fund-requests.approve', $fundRequest->id) }}" method="POST" id="approve-form">
                                    @csrf
                                    
                                    <!-- Duyệt tổng số tiền -->
                                    <div class="form-group mb-3" style="display: none;">
                                        <label for="approved_amount">Số tiền duyệt tổng (VNĐ)</label>
                                        <input type="number" class="form-control" id="approved_amount" name="approved_amount" 
                                               value="{{ $fundRequest->requested_amount }}" min="0" max="{{ $fundRequest->requested_amount }}" step="1000">
                                    </div>
                                    
                                    <!-- Duyệt từng mục chi phí -->
                                    @if($fundRequest->expense_items && count($fundRequest->expense_items) > 0)
                                        <div class="form-group mb-3" style="display: none;">
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
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Duyệt yêu cầu -->
@if($fundRequest->status === 'pending')
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="fas fa-check-circle"></i> Duyệt yêu cầu cấp kinh phí
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.fund-requests.approve', $fundRequest->id) }}" method="POST" id="approve-form-modal">
                    @csrf
                    
                    <!-- Duyệt tổng số tiền -->
                    <div class="form-group mb-3">
                        <label for="approved_amount_modal">Số tiền duyệt tổng (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-lg" id="approved_amount_modal" name="approved_amount" 
                               value="{{ $fundRequest->requested_amount }}" min="0" max="{{ $fundRequest->requested_amount }}" step="1000" required>
                        <small class="form-text text-muted">Số tiền tối đa: {{ number_format($fundRequest->requested_amount) }} VNĐ</small>
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($fundRequest->expense_items as $index => $item)
                                            <tr class="expense-row-modal" data-index="{{ $index }}">
                                                <td class="text-center align-middle">
                                                    <input type="checkbox" class="form-check-input expense-approve-modal" 
                                                           name="expense_approvals[{{ $index }}][approved]" value="1" checked>
                                                </td>
                                                <td class="align-middle">{{ $item['item'] }}</td>
                                                <td class="text-right align-middle"><strong>{{ number_format($item['amount']) }}₫</strong></td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm expense-amount-modal" 
                                                           name="expense_approvals[{{ $index }}][amount]" 
                                                           value="{{ $item['amount'] }}" min="0" max="{{ $item['amount'] }}" step="1000"
                                                           data-original="{{ $item['amount'] }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Ghi chú duyệt -->
                    <div class="form-group mb-3">
                        <label for="approval_notes_modal">Ghi chú duyệt (tùy chọn)</label>
                        <textarea class="form-control" id="approval_notes_modal" name="approval_notes" rows="3" 
                                  placeholder="Nhập ghi chú nếu có"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Hủy
                </button>
                <button type="submit" form="approve-form-modal" class="btn btn-success btn-lg">
                    <i class="fas fa-check-circle"></i> Xác nhận duyệt
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Từ chối yêu cầu -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times-circle"></i> Từ chối yêu cầu cấp kinh phí
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.fund-requests.reject', $fundRequest->id) }}" method="POST" id="reject-form-modal">
                    @csrf
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Bạn có chắc chắn muốn từ chối yêu cầu này? Hành động này không thể hoàn tác.
                    </div>
                    <div class="form-group mb-3">
                        <label for="rejection_reason_modal">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason_modal" name="rejection_reason" rows="4" 
                                  placeholder="Nhập lý do từ chối yêu cầu" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Hủy
                </button>
                <button type="submit" form="reject-form-modal" class="btn btn-danger btn-lg">
                    <i class="fas fa-times-circle"></i> Xác nhận từ chối
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý cho modal duyệt
    const expenseApprovesModal = document.querySelectorAll('.expense-approve-modal');
    const expenseAmountsModal = document.querySelectorAll('.expense-amount-modal');
    const totalApprovedInputModal = document.getElementById('approved_amount_modal');
    
    // Xử lý khi bỏ chọn một mục trong modal
    expenseApprovesModal.forEach((checkbox, index) => {
        checkbox.addEventListener('change', function() {
            const amountInput = expenseAmountsModal[index];
            
            if (!this.checked) {
                // Bỏ chọn: Vô hiệu hóa số tiền
                amountInput.value = 0;
                amountInput.disabled = true;
            } else {
                // Chọn lại: Kích hoạt số tiền
                amountInput.disabled = false;
                amountInput.value = amountInput.dataset.original;
            }
            updateTotalApprovedModal();
        });
    });
    
    // Xử lý khi thay đổi số tiền duyệt trong modal
    expenseAmountsModal.forEach(input => {
        input.addEventListener('input', updateTotalApprovedModal);
    });
    
    // Cập nhật tổng số tiền duyệt trong modal
    function updateTotalApprovedModal() {
        if (!totalApprovedInputModal) return;
        let total = 0;
        expenseApprovesModal.forEach((checkbox, index) => {
            if (checkbox.checked) {
                const amount = parseFloat(expenseAmountsModal[index].value) || 0;
                total += amount;
            }
        });
        totalApprovedInputModal.value = total;
    }
    
    // Khởi tạo tổng số tiền khi mở modal
    const approveModal = document.getElementById('approveModal');
    if (approveModal) {
        approveModal.addEventListener('shown.bs.modal', function() {
            updateTotalApprovedModal();
        });
    }
    
    // Khởi tạo tổng số tiền
    if (totalApprovedInputModal) {
        updateTotalApprovedModal();
    }
});
</script>
@endsection
