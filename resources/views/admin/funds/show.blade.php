@extends('admin.layouts.app')

@section('title', 'Chi tiết quỹ - CLB Admin')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-wallet"></i> {{ $fund->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.funds') }}">Quản lý quỹ</a></li>
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
        <!-- Thông tin cơ bản -->
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
                    
                    @if($fund->description)
                        <div class="mb-3">
                            <strong>Mô tả:</strong><br>
                            <span class="text-muted">{{ $fund->description }}</span>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <strong>CLB:</strong><br>
                        @if($fund->club)
                            <span class="badge bg-info">{{ $fund->club->name }}</span>
                        @else
                            <span class="text-muted">Quỹ chung</span>
                        @endif
                    </div>
                    
                    @if($fund->source && $fund->initial_amount > 0)
                        <div class="mb-3">
                            <strong>Nguồn tiền:</strong><br>
                            <span class="text-muted">{{ $fund->source }}</span>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <strong>Trạng thái:</strong><br>
                        @php
                            $statusColors = [
                                'active' => 'success',
                                'inactive' => 'warning', 
                                'closed' => 'danger'
                            ];
                            $statusLabels = [
                                'active' => 'Đang hoạt động',
                                'inactive' => 'Tạm dừng',
                                'closed' => 'Đã đóng'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$fund->status] }}">
                            {{ $statusLabels[$fund->status] }}
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Người tạo:</strong><br>
                        <span class="text-muted">{{ $fund->creator->name ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Ngày tạo:</strong><br>
                        <span class="text-muted">{{ $fund->created_at ? $fund->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thống kê tài chính -->
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success">{{ number_format($fund->initial_amount, 0, ',', '.') }}</h3>
                            <small class="text-muted">Số tiền ban đầu (VNĐ)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary">{{ number_format($fund->current_amount, 0, ',', '.') }}</h3>
                            <small class="text-muted">Số tiền hiện tại (VNĐ)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-info">{{ number_format($stats['total_income'], 0, ',', '.') }}</h3>
                            <small class="text-muted">Tổng thu (VNĐ)</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-danger">{{ number_format($stats['total_expense'], 0, ',', '.') }}</h3>
                            <small class="text-muted">Tổng chi (VNĐ)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning">{{ $stats['pending_transactions'] }}</h3>
                            <small class="text-muted">Giao dịch chờ duyệt</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-secondary">{{ $fund->transactions()->count() }}</h3>
                            <small class="text-muted">Tổng giao dịch</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Giao dịch gần đây -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> Giao dịch gần đây</h5>
                    <div>
                        <a href="{{ route('admin.funds.transactions.create', $fund->id) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Thêm giao dịch
                        </a>
                        <a href="{{ route('admin.funds.transactions', $fund->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-list"></i> Xem tất cả
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($stats['recent_transactions']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Loại</th>
                                        <th>Tiêu đề</th>
                                        <th>Số tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Người tạo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['recent_transactions'] as $transaction)
                                        <tr>
                                            <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                                            <td>
                                                @if($transaction->type === 'income')
                                                    <span class="badge bg-success">Thu</span>
                                                @else
                                                    <span class="badge bg-danger">Chi</span>
                                                @endif
                                            </td>
                                            <td>{{ $transaction->title }}</td>
                                            <td>
                                                <span class="{{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                                    {{ $transaction->type === 'income' ? '+' : '-' }}{{ number_format($transaction->amount, 0, ',', '.') }} VNĐ
                                                </span>
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
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-exchange-alt fa-3x mb-3"></i>
                            <p>Chưa có giao dịch nào</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.funds') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <a href="{{ route('admin.funds.edit', $fund->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                        <a href="{{ route('admin.funds.transactions.create', $fund->id) }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Thêm giao dịch
                        </a>
                        <a href="{{ route('admin.fund-requests.create') }}?event_id={{ $fund->event_id ?? '' }}&club_id={{ $fund->club_id ?? '' }}" class="btn btn-info">
                            <i class="fas fa-money-bill-wave"></i> Xin cấp kinh phí
                        </a>
                        <a href="{{ route('admin.funds.show', $fund->id) }}?refresh=true" class="btn btn-primary" title="Cập nhật lại số tiền hiện tại">
                            <i class="fas fa-sync-alt"></i> Cập nhật số tiền
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
