@extends('admin.layouts.app')

@section('title', 'Chi tiết giao dịch - CLB Admin')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-eye"></i> Chi tiết giao dịch</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.funds') }}">Quản lý quỹ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.funds.show', $fund->id) }}">{{ $fund->name }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.funds.transactions', $fund->id) }}">Giao dịch</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> Thông tin giao dịch</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Tiêu đề:</strong><br>
                                <span class="text-primary">{{ $transaction->title }}</span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Loại giao dịch:</strong><br>
                                @if($transaction->type === 'income')
                                    <span class="badge bg-success">Thu (Tiền vào)</span>
                                @else
                                    <span class="badge bg-danger">Chi (Tiền ra)</span>
                                @endif
                            </div>
                            
                            <div class="mb-3">
                                <strong>Số tiền:</strong><br>
                                <span class="{{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }} fw-bold fs-5">
                                    {{ $transaction->type === 'income' ? '+' : '-' }}{{ number_format($transaction->amount, 0, ',', '.') }} VNĐ
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Danh mục:</strong><br>
                                <span class="text-muted">{{ $transaction->category ?? 'N/A' }}</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Ngày giao dịch:</strong><br>
                                <span class="text-muted">{{ $transaction->transaction_date->format('d/m/Y') }}</span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Trạng thái:</strong><br>
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
                            </div>
                            
                            <div class="mb-3">
                                <strong>Sự kiện:</strong><br>
                                @if($transaction->event)
                                    <span class="badge bg-info">{{ $transaction->event->title }}</span>
                                @else
                                    <span class="text-muted">Không có</span>
                                @endif
                            </div>
                            
                            <div class="mb-3">
                                <strong>Người tạo:</strong><br>
                                <span class="text-muted">{{ $transaction->creator->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($transaction->description)
                        <div class="mb-3">
                            <strong>Mô tả:</strong><br>
                            <div class="border rounded p-3 bg-light">
                                {{ $transaction->description }}
                            </div>
                        </div>
                    @endif
                    
                    @if($transaction->receipt_paths && count($transaction->receipt_paths) > 0)
                        <div class="mb-3">
                            <strong>Chứng từ:</strong><br>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach($transaction->receipt_paths as $index => $path)
                                    @if(file_exists(public_path($path)))
                                        <a href="{{ asset($path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-file-pdf"></i> Chứng từ {{ $index + 1 }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin quỹ</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Tên quỹ:</strong><br>
                        <span class="text-primary">{{ $fund->name }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Số dư hiện tại:</strong><br>
                        <span class="text-success fw-bold">{{ number_format($fund->current_amount, 0, ',', '.') }} VNĐ</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>CLB:</strong><br>
                        @if($fund->club)
                            <span class="badge bg-info">{{ $fund->club->name }}</span>
                        @else
                            <span class="text-muted">Quỹ chung</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($transaction->status === 'approved' && $transaction->approver)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-check-circle"></i> Thông tin duyệt</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Người duyệt:</strong><br>
                            <span class="text-muted">{{ $transaction->approver->name }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Thời gian duyệt:</strong><br>
                            <span class="text-muted">{{ $transaction->approved_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            @if($transaction->status === 'rejected')
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-times-circle"></i> Lý do từ chối</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            {{ $transaction->rejection_reason }}
                        </div>
                    </div>
                </div>
            @endif

            @if($transaction->status === 'cancelled')
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-ban"></i> Lý do hủy</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            {{ $transaction->rejection_reason }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.funds.transactions', $fund->id) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        
                        @if($transaction->status === 'pending')
                            <a href="{{ route('admin.funds.transactions.edit', [$fund->id, $transaction->id]) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                            
                            <form method="POST" action="{{ route('admin.funds.transactions.approve', [$fund->id, $transaction->id]) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success" 
                                        onclick="return confirm('Xác nhận duyệt giao dịch này?')">
                                    <i class="fas fa-check"></i> Duyệt
                                </button>
                            </form>
                            
                            <button class="btn btn-danger" onclick="showRejectModal()">
                                <i class="fas fa-times"></i> Từ chối
                            </button>
                            
                            <form method="POST" action="{{ route('admin.funds.transactions.destroy', [$fund->id, $transaction->id]) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Xác nhận xóa giao dịch này?')">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        @endif
                        
                        @if($transaction->status === 'approved')
                            <button class="btn btn-warning" onclick="showCancelModal()">
                                <i class="fas fa-ban"></i> Hủy giao dịch
                            </button>
                        @endif
                    </div>
                </div>
            </div>
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
            <form method="POST" action="{{ route('admin.funds.transactions.reject', [$fund->id, $transaction->id]) }}">
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
            <form method="POST" action="{{ route('admin.funds.transactions.cancel', [$fund->id, $transaction->id]) }}">
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
function showRejectModal() {
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function showCancelModal() {
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}
</script>
@endsection
