@extends('layouts.student')

@section('title', 'Danh sách yêu cầu nộp quỹ - ' . $club->name)
@section('page_title', 'Danh sách yêu cầu nộp quỹ')

@section('content')
<div class="content-card mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h5 class="mb-0">{{ $club->name }}</h5>
        <small class="text-muted">Quản lý yêu cầu thanh toán quỹ từ thành viên</small>
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

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="content-card text-center stat-card">
            <h6 class="text-muted mb-2 fw-semibold">Chờ duyệt</h6>
            <h3 class="text-warning mb-2 fw-bold">{{ $stats['pending'] }}</h3>
            <small class="text-muted d-block">{{ number_format($stats['total_pending_amount'], 0, ',', '.') }} VNĐ</small>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="content-card text-center stat-card">
            <h6 class="text-muted mb-2 fw-semibold">Đã duyệt</h6>
            <h3 class="text-success mb-2 fw-bold">{{ $stats['approved'] }}</h3>
            <small class="text-muted d-block">&nbsp;</small>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="content-card text-center stat-card">
            <h6 class="text-muted mb-2 fw-semibold">Đã từ chối</h6>
            <h3 class="text-danger mb-2 fw-bold">{{ $stats['rejected'] }}</h3>
            <small class="text-muted d-block">&nbsp;</small>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="content-card text-center stat-card">
            <h6 class="text-muted mb-2 fw-semibold">Số dư quỹ</h6>
            <h3 class="text-primary mb-2 fw-bold">{{ number_format($fund->current_amount, 0, ',', '.') }}</h3>
            <small class="text-muted d-block">VNĐ</small>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="content-card mb-4">
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" 
               href="{{ route('student.club-management.fund-deposit-requests', ['club' => $club->id, 'status' => 'pending']) }}">
                Chờ duyệt ({{ $stats['pending'] }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'approved' ? 'active' : '' }}" 
               href="{{ route('student.club-management.fund-deposit-requests', ['club' => $club->id, 'status' => 'approved']) }}">
                Đã duyệt ({{ $stats['approved'] }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" 
               href="{{ route('student.club-management.fund-deposit-requests', ['club' => $club->id, 'status' => 'rejected']) }}">
                Đã từ chối ({{ $stats['rejected'] }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" 
               href="{{ route('student.club-management.fund-deposit-requests', ['club' => $club->id, 'status' => 'all']) }}">
                Tất cả
            </a>
        </li>
    </ul>
</div>

<!-- Requests List -->
<div class="content-card">
    @if($requests->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Người nộp</th>
                        <th>Số tiền</th>
                        <th>Phương thức</th>
                        <th>Mã GD</th>
                        <th>Ngày nộp</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                        <tr>
                            <td>
                                <strong>{{ $request->payer_name ?: $request->creator->name }}</strong><br>
                                <small class="text-muted">{{ $request->payer_phone }}</small>
                            </td>
                            <td>
                                <strong class="text-success">{{ number_format($request->amount, 0, ',', '.') }} VNĐ</strong>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $request->payment_method ?: 'VietQR' }}</span>
                            </td>
                            <td>
                                @if($request->transaction_code)
                                    <code>{{ $request->transaction_code }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                {{ $request->transaction_date ? $request->transaction_date->format('d/m/Y H:i') : $request->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                @if($request->status === 'pending')
                                    <span class="badge bg-warning">Chờ duyệt</span>
                                @elseif($request->status === 'approved')
                                    <span class="badge bg-success">Đã duyệt</span>
                                    @if($request->approved_at)
                                        <br><small class="text-muted">{{ $request->approved_at->format('d/m/Y H:i') }}</small>
                                    @endif
                                @elseif($request->status === 'rejected')
                                    <span class="badge bg-danger">Đã từ chối</span>
                                    @if($request->rejection_reason)
                                        <br><small class="text-muted" title="{{ $request->rejection_reason }}">{{ Str::limit($request->rejection_reason, 30) }}</small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('student.club-management.fund-deposit.bill', ['transaction' => $request->id]) }}" 
                                       class="btn btn-outline-info" 
                                       title="Xem bill">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                    @if($request->status === 'pending')
                                        <form action="{{ route('student.club-management.fund-transactions.approve', ['transaction' => $request->id]) }}" 
                                              method="POST" 
                                              style="display: inline-block;"
                                              onsubmit="return confirm('Bạn có chắc chắn muốn duyệt yêu cầu nộp quỹ này? Số tiền sẽ được cộng vào quỹ CLB.');">
                                            @csrf
                                            <button type="submit" class="btn btn-success" title="Duyệt">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <button type="button" 
                                                class="btn btn-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#rejectModal{{ $request->id }}"
                                                title="Từ chối">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>

                                <!-- Reject Modal -->
                                @if($request->status === 'pending')
                                    <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Từ chối yêu cầu nộp quỹ</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('student.club-management.fund-transactions.reject', ['transaction' => $request->id]) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                                                            <textarea name="rejection_reason" 
                                                                      class="form-control" 
                                                                      rows="3" 
                                                                      required
                                                                      placeholder="Nhập lý do từ chối..."></textarea>
                                                        </div>
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            Người nộp quỹ sẽ nhận được thông báo về việc từ chối.
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                        <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @if($request->description)
                            <tr>
                                <td colspan="7" class="bg-light">
                                    <small><strong>Ghi chú:</strong> {{ $request->description }}</small>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $requests->appends(request()->query())->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">Chưa có yêu cầu nộp quỹ nào.</p>
        </div>
    @endif
</div>

@push('styles')
<style>
    .stat-card {
        min-height: 140px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 1.5rem;
    }
    
    .stat-card h6 {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.75rem;
        font-weight: 600;
    }
    
    .stat-card h3 {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        line-height: 1.2;
        font-weight: 700;
    }
    
    .stat-card small {
        font-size: 0.75rem;
        color: #9ca3af;
        min-height: 1.2rem;
        display: block;
    }
    
    .stat-card .text-warning {
        color: #f59e0b !important;
    }
    
    .stat-card .text-success {
        color: #10b981 !important;
    }
    
    .stat-card .text-danger {
        color: #ef4444 !important;
    }
    
    .stat-card .text-primary {
        color: #3b82f6 !important;
    }
    
    /* Nav tabs styling */
    .content-card .nav-tabs {
        border-bottom: 2px solid #e5e7eb;
    }
    
    .content-card .nav-tabs .nav-link {
        color: #374151;
        font-weight: 500;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 0.75rem 1.25rem;
        transition: all 0.2s ease;
        background: transparent;
    }
    
    .content-card .nav-tabs .nav-link:hover {
        color: #14b8a6;
        background-color: #f0fdfa;
        border-bottom-color: #a7f3d0;
    }
    
    .content-card .nav-tabs .nav-link.active {
        color: #14b8a6 !important;
        background-color: transparent !important;
        border-bottom-color: #14b8a6;
        font-weight: 600;
    }
</style>
@endpush
@endsection




