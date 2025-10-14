@extends('admin.layouts.app')

@section('title', 'Quản lý quỹ - CLB Admin')

@section('content')
<div class="content-header">
    <h1>Quản lý quỹ</h1>
</div>

<!-- Thống kê quỹ -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #28a745;">
                <i class="fas fa-coins"></i>
            </div>
            <p class="stats-number">{{ number_format($totalFunds) }}đ</p>
            <p class="stats-label">Tổng quỹ</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #007bff;">
                <i class="fas fa-chart-line"></i>
            </div>
            <p class="stats-number">{{ $funds->count() }}</p>
            <p class="stats-label">Giao dịch</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #ffc107;">
                <i class="fas fa-users"></i>
            </div>
            <p class="stats-number">{{ $clubs->count() }}</p>
            <p class="stats-label">CLB có quỹ</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #dc3545;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <p class="stats-number">{{ $funds->where('created_at', '>=', now()->subMonth())->count() }}</p>
            <p class="stats-label">Tháng này</p>
        </div>
    </div>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.fund-management') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm giao dịch..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="club_id" class="form-select">
                    <option value="">Tất cả câu lạc bộ</option>
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
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.fund-management') }}" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addFundModal">
                    <i class="fas fa-plus"></i> Thêm quỹ
                </button>
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
                        <th>ID</th>
                        <th>Mô tả</th>
                        <th>Câu lạc bộ</th>
                        <th>Số tiền</th>
                        <th>Loại</th>
                        <th>Người tạo</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($funds as $fund)
                        @php
                            preg_match('/\d+/', $fund->content, $matches);
                            $amount = isset($matches[0]) ? (int)$matches[0] : 0;
                            $isIncome = strpos(strtolower($fund->title), 'thu') !== false || strpos(strtolower($fund->title), 'nhận') !== false;
                        @endphp
                        <tr>
                            <td>{{ $fund->id }}</td>
                            <td>
                                <strong>{{ $fund->title }}</strong>
                                <br><small class="text-muted">{{ Str::limit($fund->content, 50) }}</small>
                            </td>
                            <td>{{ $fund->club->name ?? 'Không xác định' }}</td>
                            <td>
                                <span class="badge bg-{{ $isIncome ? 'success' : 'danger' }}">
                                    {{ $isIncome ? '+' : '-' }}{{ number_format($amount) }}đ
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $isIncome ? 'success' : 'warning' }}">
                                    {{ $isIncome ? 'Thu' : 'Chi' }}
                                </span>
                            </td>
                            <td>{{ $fund->user->name ?? 'Không xác định' }}</td>
                            <td>{{ $fund->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewFund({{ $fund->id }})">
                                    <i class="fas fa-eye"></i> Xem
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteFund({{ $fund->id }})">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Không có giao dịch quỹ nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        @if($funds->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $funds->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal thêm quỹ -->
<div class="modal fade" id="addFundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm giao dịch quỹ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.posts') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số tiền</label>
                        <input type="number" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Loại giao dịch</label>
                        <select class="form-select" name="type">
                            <option value="thu">Thu</option>
                            <option value="chi">Chi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Câu lạc bộ</label>
                        <select class="form-select" name="club_id" required>
                            @foreach($clubs as $club)
                                <option value="{{ $club->id }}">{{ $club->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea class="form-control" name="content" rows="3"></textarea>
                    </div>
                    <input type="hidden" name="type" value="fund">
                    <input type="hidden" name="status" value="published">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
