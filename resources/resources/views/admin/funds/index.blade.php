@extends('admin.layouts.app')

@section('title', 'Quản lý quỹ')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Quản lý quỹ</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.fund-requests') }}" class="btn btn-info">
                <i class="fas fa-money-bill-wave"></i> Yêu cầu cấp kinh phí
            </a>
            <a href="{{ route('admin.funds.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Tạo quỹ mới
            </a>
        </div>
    </div>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.funds') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm theo tên CLB..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Đã đóng</option>
                </select>
            </div>
            <div class="col-md-2">
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
            <div class="col-md-auto ms-auto">
                <a href="{{ route('admin.funds') }}" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách quỹ -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>CLB</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($funds as $index => $fund)
                        <tr>
                            <td>{{ ($funds->currentPage() - 1) * $funds->perPage() + $index + 1 }}</td>
                            <td>
                                @if($fund->club)
                                    <strong>Quỹ của {{ $fund->club->name }}</strong>
                                @else
                                    <strong>{{ $fund->name ?? 'Quỹ chung hệ thống' }}</strong>
                                @endif
                                @if($fund->club)
                                    <br><small class="text-muted">
                                        Số tiền: {{ number_format($fund->current_amount, 0, ',', '.') }} VNĐ
                                    </small>
                                @endif
                            </td>
                            <td>
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
                            </td>
                            <td>{{ $fund->created_at ? $fund->created_at->format('d/m/Y') : 'N/A' }}</td>
                            <td style="min-width: 120px; width: 120px;">
                                <div class="d-flex flex-column gap-1">
                                    <a href="{{ route('admin.funds.show', $fund->id) }}" 
                                       class="btn btn-sm btn-primary text-white w-100">
                                        <i class="fas fa-eye"></i> Xem chi tiết
                                    </a>
                                    <form method="POST" action="{{ route('admin.funds.destroy', $fund->id) }}" 
                                          class="d-inline" 
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa quỹ này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger w-100 text-white">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Không tìm thấy quỹ nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        @if($funds->hasPages())
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        @if($funds->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">« Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $funds->previousPageUrl() }}">« Previous</a>
                            </li>
                        @endif
                        
                        <li class="page-item active">
                            <span class="page-link">{{ $funds->currentPage() }} / {{ $funds->lastPage() }}</span>
                        </li>
                        
                        @if($funds->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $funds->nextPageUrl() }}">Next »</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">Next »</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
    </div>
</div>
@endsection
