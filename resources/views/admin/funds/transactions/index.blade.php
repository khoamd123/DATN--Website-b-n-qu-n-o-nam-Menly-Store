@extends('admin.layouts.app')

@section('title', 'Giao dịch quỹ - CLB Admin')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-exchange-alt"></i> Giao dịch quỹ: {{ $fund->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.funds') }}">Quản lý quỹ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.funds.show', $fund->id) }}">{{ $fund->name }}</a></li>
                <li class="breadcrumb-item active">Giao dịch</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Thống kê nhanh -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ number_format($stats['total_income'], 0, ',', '.') }}</h3>
                    <small class="text-muted">Tổng thu (VNĐ)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger">{{ number_format($stats['total_expense'], 0, ',', '.') }}</h3>
                    <small class="text-muted">Tổng chi (VNĐ)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning">{{ $stats['pending_count'] }}</h3>
                    <small class="text-muted">Chờ duyệt</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">{{ $stats['approved_count'] }}</h3>
                    <small class="text-muted">Đã duyệt</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.funds.transactions', $fund->id) }}" class="row g-3">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">Tất cả loại</option>
                        <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Thu</option>
                        <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Chi</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="Từ ngày">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="Đến ngày">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách giao dịch -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Danh sách giao dịch</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.funds.transactions.create', $fund->id) }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Thêm giao dịch
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Ngày</th>
                            <th>Loại</th>
                            <th>Tiêu đề</th>
                            <th>Số tiền</th>
                            <th>Danh mục</th>
                            <th>Trạng thái</th>
                            <th>Người tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $index => $transaction)
                            <tr>
                                <td>{{ ($transactions->currentPage() - 1) * $transactions->perPage() + $index + 1 }}</td>
                                <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                                <td>
                                    @if($transaction->type === 'income')
                                        <span class="badge bg-success">Thu</span>
                                    @else
                                        <span class="badge bg-danger">Chi</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $transaction->title }}</strong>
                                    @if($transaction->description)
                                        <br><small class="text-muted">{{ Str::limit(strip_tags($transaction->description), 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->type === 'income' ? '+' : '-' }}{{ number_format($transaction->amount, 0, ',', '.') }} VNĐ
                                    </span>
                                </td>
                                <td>
                                    @if($transaction->items && $transaction->items->count() > 0)
                                        <button class="btn btn-sm btn-outline-info" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#items-{{ $transaction->id }}">
                                            <i class="fas fa-list"></i> {{ $transaction->items->count() }} khoản
                                        </button>
                                        <div class="collapse mt-2" id="items-{{ $transaction->id }}">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        @if($transaction->status === 'pending')
                                                            <th width="40" class="text-center">
                                                                <input type="checkbox" class="form-check-input select-all-{{ $transaction->id }}" checked>
                                                            </th>
                                                        @endif
                                                        <th>Khoản mục</th>
                                                        <th width="150">Số tiền</th>
                                                        @if($transaction->status === 'pending')
                                                            <th width="250">Lý do từ chối</th>
                                                        @elseif($transaction->items->contains('status', 'rejected'))
                                                            <th width="200">Lý do từ chối</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($transaction->items as $item)
                                                    <tr class="item-row-{{ $transaction->id }}" data-item-id="{{ $item->id }}" data-amount="{{ $item->amount }}">
                                                        @if($transaction->status === 'pending')
                                                            <td class="text-center">
                                                                <input type="checkbox" 
                                                                       class="form-check-input item-checkbox-{{ $transaction->id }}" 
                                                                       data-item-id="{{ $item->id }}"
                                                                       checked>
                                                            </td>
                                                        @endif
                                                        <td>
                                                            {{ $item->item_name }}
                                                            @if($transaction->status !== 'pending' && $item->status)
                                                                @if($item->status === 'approved')
                                                                    <span class="badge bg-success ms-2"><i class="fas fa-check"></i> Đã duyệt</span>
                                                                @elseif($item->status === 'rejected')
                                                                    <span class="badge bg-danger ms-2"><i class="fas fa-times"></i> Từ chối</span>
                                                                @endif
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            @if($item->status === 'rejected')
                                                                <del class="text-muted">{{ number_format($item->amount, 0, ',', '.') }} VNĐ</del>
                                                            @else
                                                                {{ number_format($item->amount, 0, ',', '.') }} VNĐ
                                                            @endif
                                                        </td>
                                                        @if($transaction->status === 'pending')
                                                            <td>
                                                                <input type="text" 
                                                                       class="form-control form-control-sm reject-reason-{{ $transaction->id }}" 
                                                                       data-item-id="{{ $item->id }}"
                                                                       placeholder="Nhập lý do từ chối..."
                                                                       style="display: none;">
                                                            </td>
                                                        @elseif($item->status === 'rejected' && $item->rejection_reason)
                                                            <td>
                                                                <small class="text-danger">
                                                                    <i class="fas fa-exclamation-circle"></i> 
                                                                    {{ $item->rejection_reason }}
                                                                </small>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                    @endforeach
                                                    <tr class="table-info fw-bold">
                                                        <td @if($transaction->status === 'pending') colspan="1" @endif>
                                                            @if($transaction->status === 'pending')
                                                                
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($transaction->status === 'pending')
                                                                Tổng duyệt
                                                            @else
                                                                Tổng thực tế
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            @if($transaction->status === 'pending')
                                                                <span class="total-approved-{{ $transaction->id }}">{{ number_format($transaction->items->sum('amount'), 0, ',', '.') }}</span> VNĐ
                                                            @else
                                                                <span class="total-approved-{{ $transaction->id }}">
                                                                    {{ number_format($transaction->items->where('status', '!=', 'rejected')->sum('amount'), 0, ',', '.') }}
                                                                </span> VNĐ
                                                                @if($transaction->items->contains('status', 'rejected'))
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        (Gốc: {{ number_format($transaction->items->sum('amount'), 0, ',', '.') }} VNĐ)
                                                                    </small>
                                                                @endif
                                                            @endif
                                                        </td>
                                                        @if($transaction->status === 'pending')
                                                            <td></td>
                                                        @elseif($transaction->items->contains('status', 'rejected'))
                                                            <td></td>
                                                        @endif
                                                    </tr>
                                                </tbody>
                                            </table>
                                            @if($transaction->status === 'pending')
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-success" onclick="approvePartialTransaction({{ $transaction->id }})">
                                                        <i class="fas fa-check"></i> Duyệt các khoản đã chọn
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($transaction->expenseCategory)
                                        <span class="badge" style="background-color: {{ $transaction->expenseCategory->color }};">
                                            <i class="fas fa-{{ $transaction->expenseCategory->icon }}"></i> 
                                            {{ $transaction->expenseCategory->name }}
                                        </span>
                                    @else
                                        {{ $transaction->category ?? 'N/A' }}
                                    @endif
                                </td>
                                <td>
                                    @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'cancelled' => 'secondary'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'rejected' => 'Từ chối',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$transaction->status] }}">
                                        {{ $statusLabels[$transaction->status] }}
                                    </span>
                                </td>
                                <td>{{ $transaction->creator->name ?? 'N/A' }}</td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <a href="{{ route('admin.funds.transactions.show', [$fund->id, $transaction->id]) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <a href="{{ route('admin.funds.transactions.invoice', [$fund->id, $transaction->id]) }}" 
                                           class="btn btn-sm btn-outline-danger" title="Xuất hóa đơn PDF" target="_blank">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        
                                        @if($transaction->status === 'pending')
                                            <!-- Nút Duyệt -->
                                            <form method="POST" action="{{ route('admin.funds.transactions.approve', [$fund->id, $transaction->id]) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" 
                                                        title="Duyệt giao dịch"
                                                        onclick="return confirm('Xác nhận duyệt giao dịch này?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            
                                            <!-- Nút Từ chối (cần lý do) -->
                                            <button class="btn btn-sm btn-danger" 
                                                    title="Từ chối giao dịch (cần nhập lý do)"
                                                    onclick="showRejectModal({{ $transaction->id }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            
                                            <!-- Nút Chỉnh sửa -->
                                            <a href="{{ route('admin.funds.transactions.edit', [$fund->id, $transaction->id]) }}" 
                                               class="btn btn-sm btn-warning"
                                               title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        
                                        @if($transaction->status === 'approved')
                                            <button class="btn btn-sm btn-outline-warning" 
                                                    onclick="showCancelModal({{ $transaction->id }})">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    Không có giao dịch nào
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($transactions->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal từ chối -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle"></i> Từ chối giao dịch
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Bạn cần nhập lý do từ chối giao dịch này
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Lý do từ chối <span class="text-danger">*</span>
                        </label>
                        <textarea name="rejection_reason" 
                                  class="form-control" 
                                  rows="4" 
                                  required
                                  placeholder="Nhập lý do từ chối giao dịch..."></textarea>
                        <small class="text-muted">
                            Lý do này sẽ được gửi đến người tạo giao dịch
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Xác nhận từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal hủy giao dịch -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-ban"></i> Hủy giao dịch
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Lưu ý:</strong> Hủy giao dịch sẽ tạo giao dịch điều chỉnh ngược lại để khôi phục số dư quỹ.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Lý do hủy <span class="text-danger">*</span>
                        </label>
                        <textarea name="cancellation_reason" 
                                  class="form-control" 
                                  rows="4" 
                                  required
                                  placeholder="Nhập lý do hủy giao dịch..."></textarea>
                        <small class="text-muted">
                            Lý do này sẽ được lưu lại trong hệ thống
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left"></i> Đóng
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-ban"></i> Xác nhận hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRejectModal(transactionId) {
    document.getElementById('rejectForm').action = '{{ route("admin.funds.transactions.reject", [$fund->id, ":transactionId"]) }}'.replace(':transactionId', transactionId);
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function showCancelModal(transactionId) {
    document.getElementById('cancelForm').action = '{{ route("admin.funds.transactions.cancel", [$fund->id, ":transactionId"]) }}'.replace(':transactionId', transactionId);
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

// Xử lý checkbox cho từng khoản mục - Sử dụng event delegation
document.addEventListener('change', function(e) {
    const target = e.target;
    
    // Check if it's a select-all checkbox
    const selectAllMatch = target.className.match(/select-all-(\d+)/);
    if (selectAllMatch && target.type === 'checkbox') {
        const transactionId = selectAllMatch[1];
        console.log('Select all changed for transaction:', transactionId, 'Checked:', target.checked);
        const checkboxes = document.querySelectorAll(`.item-checkbox-${transactionId}`);
        checkboxes.forEach(cb => {
            cb.checked = target.checked;
            toggleReasonInput(cb, transactionId);
        });
        updateTotal(transactionId);
        return;
    }
    
    // Check if it's an item checkbox
    const itemCheckboxMatch = target.className.match(/item-checkbox-(\d+)/);
    if (itemCheckboxMatch && target.type === 'checkbox') {
        const transactionId = itemCheckboxMatch[1];
        console.log('Item checkbox changed:', target.dataset.itemId, 'Checked:', target.checked);
        toggleReasonInput(target, transactionId);
        updateTotal(transactionId);
        updateSelectAll(transactionId);
        return;
    }
});

function toggleReasonInput(checkbox, transactionId) {
    const itemId = checkbox.dataset.itemId;
    const reasonInput = document.querySelector(`.reject-reason-${transactionId}[data-item-id="${itemId}"]`);
    
    console.log('Toggle reason for item:', itemId, 'Checked:', checkbox.checked, 'Input found:', !!reasonInput);
    
    if (reasonInput) {
        if (!checkbox.checked) {
            // Bỏ check = từ chối → hiện ô nhập lý do
            reasonInput.style.setProperty('display', 'block', 'important');
            reasonInput.style.visibility = 'visible';
            reasonInput.required = true;
            reasonInput.disabled = false;
            setTimeout(() => {
                reasonInput.focus();
                console.log('Reason input shown for item:', itemId);
            }, 100);
        } else {
            // Check = duyệt → ẩn ô lý do
            reasonInput.style.setProperty('display', 'none', 'important');
            reasonInput.style.visibility = 'hidden';
            reasonInput.required = false;
            reasonInput.value = '';
            reasonInput.classList.remove('is-invalid');
            reasonInput.style.borderColor = '';
        }
    } else {
        console.error('Reason input not found for item:', itemId);
    }
}

function updateTotal(transactionId) {
    const rows = document.querySelectorAll(`.item-row-${transactionId}`);
    let total = 0;
    
    rows.forEach(row => {
        const itemId = row.dataset.itemId;
        const amount = parseFloat(row.dataset.amount);
        const checkbox = document.querySelector(`.item-checkbox-${transactionId}[data-item-id="${itemId}"]`);
        
        if (checkbox && checkbox.checked) {
            total += amount;
        }
    });
    
    const totalSpan = document.querySelector(`.total-approved-${transactionId}`);
    if (totalSpan) {
        totalSpan.textContent = new Intl.NumberFormat('vi-VN').format(total);
    }
}

function updateSelectAll(transactionId) {
    const selectAll = document.querySelector(`.select-all-${transactionId}`);
    const checkboxes = document.querySelectorAll(`.item-checkbox-${transactionId}`);
    const checkedBoxes = document.querySelectorAll(`.item-checkbox-${transactionId}:checked`);
    
    if (selectAll && checkboxes.length > 0) {
        selectAll.checked = checkboxes.length === checkedBoxes.length;
    }
}

function approvePartialTransaction(transactionId) {
    const rows = document.querySelectorAll(`.item-row-${transactionId}`);
    const approvedItems = [];
    const rejectedItems = [];
    let hasError = false;
    
    rows.forEach(row => {
        const itemId = row.dataset.itemId;
        const amount = row.dataset.amount;
        const checkbox = document.querySelector(`.item-checkbox-${transactionId}[data-item-id="${itemId}"]`);
        
        if (!checkbox) return;
        
        if (checkbox.checked) {
            // Checked = Duyệt
            approvedItems.push({
                id: itemId,
                amount: amount
            });
        } else {
            // Unchecked = Từ chối
            const reasonInput = document.querySelector(`.reject-reason-${transactionId}[data-item-id="${itemId}"]`);
            const reason = reasonInput ? reasonInput.value.trim() : '';
            
            if (!reason) {
                alert('Vui lòng nhập lý do từ chối cho tất cả các khoản mục bỏ dấu tích!');
                if (reasonInput) {
                    reasonInput.focus();
                    reasonInput.classList.add('is-invalid');
                    reasonInput.style.borderColor = 'red';
                }
                hasError = true;
                return;
            }
            
            rejectedItems.push({
                id: itemId,
                reason: reason
            });
        }
    });
    
    // Nếu có lỗi validation, dừng lại không submit
    if (hasError) {
        return false;
    }
    
    if (approvedItems.length === 0) {
        alert('Vui lòng chọn ít nhất một khoản mục để duyệt!');
        return;
    }
    
    // Tạo form và submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.funds.transactions.approve-partial", [$fund->id, ":transactionId"]) }}'.replace(':transactionId', transactionId);
    
    // CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    // Approved items
    const approvedInput = document.createElement('input');
    approvedInput.type = 'hidden';
    approvedInput.name = 'approved_items';
    approvedInput.value = JSON.stringify(approvedItems);
    form.appendChild(approvedInput);
    
    // Rejected items
    const rejectedInput = document.createElement('input');
    rejectedInput.type = 'hidden';
    rejectedInput.name = 'rejected_items';
    rejectedInput.value = JSON.stringify(rejectedItems);
    form.appendChild(rejectedInput);
    
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
