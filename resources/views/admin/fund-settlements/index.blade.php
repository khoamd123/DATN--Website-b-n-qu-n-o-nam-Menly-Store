@extends('admin.layouts.app')

@section('title', 'Quyết toán kinh phí - CLB Admin')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Quyết toán kinh phí</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.fund-requests') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quản lý yêu cầu
            </a>
        </div>
    </div>
    <p class="text-muted">Danh sách yêu cầu cấp kinh phí cần quyết toán sau chi tiêu</p>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.fund-settlements') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm theo tiêu đề, mô tả, CLB..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="club_id" class="form-select">
                    <option value="">Tất cả CLB</option>
                    @foreach($clubs as $club)
                        <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                            {{ $club->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-3 text-end">
                <a href="{{ route('admin.fund-settlements') }}" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách yêu cầu cần quyết toán -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-calculator text-warning"></i> 
            Yêu cầu cần quyết toán ({{ $requests->total() }})
        </h5>
    </div>
    <div class="card-body">
        @if($requests->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Tiêu đề</th>
                            <th>CLB</th>
                            <th>Sự kiện</th>
                            <th>Số tiền duyệt</th>
                            <th>Ngày duyệt</th>
                            <th>Người duyệt</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $index => $request)
                            <tr>
                                <td>{{ ($requests->currentPage() - 1) * $requests->perPage() + $index + 1 }}</td>
                                <td>
                                    <div>
                                        <strong>{{ $request->title }}</strong>
                                        @if($request->description)
                                            <br><small class="text-muted">{{ Str::limit(strip_tags($request->description), 50) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $request->club->name }}</span>
                                </td>
                                <td>
                                    @if($request->event)
                                        <span class="badge bg-info">{{ $request->event->name }}</span>
                                    @else
                                        <span class="text-muted">Không có</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-success">{{ number_format($request->approved_amount) }} VNĐ</strong>
                                </td>
                                <td>
                                    {{ $request->approved_at ? $request->approved_at->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td>
                                    @if($request->approver)
                                        {{ $request->approver->name }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock"></i> Chờ quyết toán
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.fund-settlements.create', $request->id) }}" 
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-calculator"></i> Quyết toán
                                        </a>
                                        <a href="{{ route('admin.fund-requests.show', $request->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Xem
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            @if($requests->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $requests->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5>Không có yêu cầu nào cần quyết toán</h5>
                <p class="text-muted">Tất cả yêu cầu đã được quyết toán hoặc chưa được duyệt.</p>
                <a href="{{ route('admin.fund-requests') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Quay lại quản lý yêu cầu
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Danh sách yêu cầu đã quyết toán -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-check-circle text-success"></i> 
            Yêu cầu đã quyết toán ({{ $settledRequests->total() }})
        </h5>
    </div>
    <div class="card-body">
        @if($settledRequests->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Tiêu đề</th>
                            <th>CLB</th>
                            <th>Số tiền duyệt</th>
                            <th>Số tiền thực tế</th>
                            <th>Ngày quyết toán</th>
                            <th>Người quyết toán</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($settledRequests as $index => $request)
                            <tr>
                                <td>{{ ($settledRequests->currentPage() - 1) * $settledRequests->perPage() + $index + 1 }}</td>
                                <td>
                                    <div>
                                        <strong>{{ $request->title }}</strong>
                                        @if($request->description)
                                            <br><small class="text-muted">{{ Str::limit(strip_tags($request->description), 50) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $request->club->name }}</span>
                                </td>
                                <td>
                                    <strong class="text-success">{{ number_format($request->approved_amount) }} VNĐ</strong>
                                </td>
                                <td>
                                    <strong class="text-info">{{ number_format($request->actual_amount) }} VNĐ</strong>
                                </td>
                                <td>
                                    {{ $request->settlement_date ? $request->settlement_date->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td>
                                    @if($request->settler)
                                        {{ $request->settler->name }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Đã quyết toán
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.fund-settlements.show', $request->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Xem chi tiết
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Phân trang cho yêu cầu đã quyết toán -->
            @if($settledRequests->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $settledRequests->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                <p class="text-muted">Chưa có yêu cầu nào được quyết toán</p>
            </div>
        @endif
    </div>
</div>

<style>
.table th {
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
</style>
@endsection
