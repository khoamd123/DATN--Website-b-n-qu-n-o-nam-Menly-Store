@extends('layouts.student')

@section('title', 'Yêu cầu cấp kinh phí - UniClubs')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">
                        <i class="fas fa-money-bill-wave text-teal"></i>
                        @if(request('settlement') === 'settled')
                            Quyết toán kinh phí - {{ $club->name }}
                        @else
                        Yêu cầu cấp kinh phí - {{ $club->name }}
                        @endif
                    </h3>
                    <small class="text-muted">
                        @if(request('settlement') === 'settled')
                            Danh sách yêu cầu đã được quyết toán
                        @else
                            Danh sách yêu cầu cấp kinh phí của CLB
                        @endif
                    </small>
                </div>
                <div class="d-flex gap-2">
                    @php
                        $position = $user->getPositionInClub($club->id);
                        $isSettlementPage = request('settlement') === 'settled';
                    @endphp
                    @if($position === 'leader' && !$isSettlementPage)
                        <a href="{{ route('student.club-management.fund-requests.create') }}" class="btn btn-primary btn-sm text-white">
                            <i class="fas fa-plus me-1"></i> Tạo yêu cầu
                        </a>
                    @endif
                    <a href="{{ route('student.club-management.index') }}" class="btn btn-secondary btn-sm text-white">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabs để chuyển đổi giữa tất cả yêu cầu và quyết toán -->
        <div class="content-card mb-3">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ request('settlement') !== 'settled' ? 'active' : '' }}" 
                       href="{{ route('student.club-management.fund-requests') }}">
                        <i class="fas fa-list me-1"></i> Tất cả yêu cầu
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ request('settlement') === 'settled' ? 'active' : '' }}" 
                       href="{{ route('student.club-management.fund-requests', ['settlement' => 'settled']) }}">
                        <i class="fas fa-calculator me-1"></i> Yêu cầu đã quyết toán
                    </a>
                </li>
            </ul>
        </div>

        <div class="content-card mb-3">
            <form method="GET" action="{{ route('student.club-management.fund-requests') }}" class="row g-2 align-items-end">
                @if(request('settlement') === 'settled')
                    <input type="hidden" name="settlement" value="settled">
                @endif
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Tìm kiếm theo tiêu đề, mô tả, sự kiện...">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="partially_approved" {{ request('status') === 'partially_approved' ? 'selected' : '' }}>Duyệt một phần</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm text-white">
                        <i class="fas fa-search me-1"></i> Tìm kiếm
                    </button>
                </div>
                <div class="col-md-auto ms-auto">
                    <a href="{{ route('student.club-management.fund-requests') }}" class="btn btn-secondary btn-sm text-white">
                        <i class="fas fa-sync-alt me-1"></i> Làm mới
                    </a>
                </div>
            </form>
        </div>

        <div class="content-card">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($requests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 25%;">Tiêu đề</th>
                                <th style="width: 15%;">Sự kiện</th>
                                <th style="width: 12%;">Số tiền yêu cầu</th>
                                <th style="width: 12%;">Số tiền duyệt</th>
                                <th style="width: 12%;">Trạng thái</th>
                                <th style="width: 10%;">Ngày tạo</th>
                                <th style="width: 9%;" class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                            <tr>
                                <td>{{ $request->id }}</td>
                                <td>
                                    <strong>{{ $request->title }}</strong>
                                </td>
                                <td>
                                    <a href="#" class="text-decoration-none">
                                        {{ $request->event->title ?? 'N/A' }}
                                        <i class="fas fa-external-link-alt ms-1" style="font-size: 0.7rem;"></i>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-primary fw-semibold">{{ number_format($request->requested_amount, 0, ',', '.') }} VNĐ</span>
                                </td>
                                <td>
                                    @if($request->approved_amount)
                                        <span class="text-success fw-semibold">{{ number_format($request->approved_amount, 0, ',', '.') }} VNĐ</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'partially_approved' => 'info',
                                            'rejected' => 'danger',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Chờ duyệt',
                                            'approved' => 'Đã duyệt',
                                            'partially_approved' => 'Duyệt một phần',
                                            'rejected' => 'Từ chối',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$request->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$request->status] ?? $request->status }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $request->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex flex-column gap-1">
                                        <a href="{{ route('student.club-management.fund-requests.show', $request->id) }}" 
                                           class="btn btn-sm btn-primary text-white w-100">
                                            <i class="fas fa-eye"></i> Xem chi tiết
                                        </a>
                                        @php
                                            $position = $user->getPositionInClub($club->id);
                                        @endphp
                                        @if($request->status === 'rejected' && $position === 'leader')
                                            <a href="{{ route('student.club-management.fund-requests.edit', $request->id) }}" 
                                               class="btn btn-sm btn-warning text-white w-100">
                                                <i class="fas fa-edit"></i> Sửa
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $requests->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    @if(request('settlement') === 'settled')
                        <i class="fas fa-calculator fa-3x text-muted mb-3 d-block opacity-50"></i>
                        <p class="text-muted mb-3">Chưa có yêu cầu nào đã được quyết toán.</p>
                        <p class="text-muted small mb-3">Các yêu cầu đã được quyết toán sẽ hiển thị tại đây.</p>
                        <a href="{{ route('student.club-management.fund-requests') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-1"></i> Xem tất cả yêu cầu
                        </a>
                    @else
                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block opacity-50"></i>
                        <p class="text-muted mb-3">Chưa có yêu cầu cấp kinh phí nào.</p>
                        @php
                            $position = $user->getPositionInClub($club->id);
                        @endphp
                        @if($position === 'leader')
                            <a href="{{ route('student.club-management.fund-requests.create') }}" class="btn btn-primary text-white">
                                <i class="fas fa-plus me-1"></i> Tạo yêu cầu đầu tiên
                            </a>
                        @endif
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

