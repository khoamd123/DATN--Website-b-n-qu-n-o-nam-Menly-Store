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
                                        <br><small class="text-muted">{{ Str::limit($transaction->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->type === 'income' ? '+' : '-' }}{{ number_format($transaction->amount, 0, ',', '.') }} VNĐ
                                    </span>
                                </td>
                                <td>{{ $transaction->category ?? 'N/A' }}</td>
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
                                            <a href="{{ route('admin.funds.transactions.edit', [$fund->id, $transaction->id]) }}" 
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <form method="POST" action="{{ route('admin.funds.transactions.approve', [$fund->id, $transaction->id]) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" 
                                                        onclick="return confirm('Xác nhận duyệt giao dịch này?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="showRejectModal({{ $transaction->id }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            
                                            <form method="POST" action="{{ route('admin.funds.transactions.destroy', [$fund->id, $transaction->id]) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Xác nhận xóa giao dịch này?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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
            <div class="modal-header">
                <h5 class="modal-title">Từ chối giao dịch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal hủy giao dịch -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hủy giao dịch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Hủy giao dịch sẽ tạo giao dịch điều chỉnh ngược lại để khôi phục số dư quỹ.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lý do hủy <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Xác nhận hủy</button>
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
</script>
@endsection
