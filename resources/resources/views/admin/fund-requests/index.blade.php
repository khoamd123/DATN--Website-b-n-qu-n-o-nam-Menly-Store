@extends('admin.layouts.app')

@section('title', 'Quản lý yêu cầu cấp kinh phí')

@section('styles')
<style>
/* Đảm bảo tất cả link trong bảng có màu đen và không gạch chân */
.table a {
    color: #212529 !important;
    text-decoration: none !important;
}
.table a:hover {
    color: #0d6efd !important;
    text-decoration: underline !important;
}
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Quản lý yêu cầu cấp kinh phí</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.funds') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay về quản lý quỹ
            </a>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">

                                 <div class="card-body">
                     <!-- Bộ lọc và tìm kiếm -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.fund-requests') }}" class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" 
                                           name="search" 
                                           class="form-control" 
                                           placeholder="Tìm kiếm theo sự kiện, mô tả, CLB..."
                                           value="{{ request('search') }}">
                             </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select">
                                        <option value="">Tất cả trạng thái</option>
                                     <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                     <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                     <option value="partially_approved" {{ request('status') == 'partially_approved' ? 'selected' : '' }}>Duyệt một phần</option>
                                     <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
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
                                    <a href="{{ route('admin.fund-requests') }}" class="btn btn-secondary">
                                        <i class="fas fa-refresh"></i> Làm mới
                                     </a>
                                 </div>
                            </form>
                             </div>
                         </div>

                    <!-- Bảng danh sách -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Sự kiện</th>
                                    <th>CLB</th>
                                    <th>Số tiền yêu cầu</th>
                                    <th>Số tiền duyệt</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $index => $request)
                                    <tr>
                                        <td>{{ $requests->firstItem() + $index }}</td>
                                        <td>
                                            @if($request->event)
                                                <a href="{{ route('admin.events.show', $request->event->id) }}" class="text-dark text-decoration-none">
                                                    <strong>{{ $request->event->title ?? $request->event->name }}</strong>
                                                </a>
                                            @else
                                                <span class="text-muted">Không có</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->club)
                                                <a href="{{ route('admin.clubs.show', $request->club->id) }}" 
                                                   class="text-dark text-decoration-none"
                                                   title="Xem chi tiết câu lạc bộ">
                                                    {{ $request->club->name }}
                                                    <i class="fas fa-external-link-alt fa-xs ms-1 text-muted"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">Không xác định</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <strong class="text-primary">{{ number_format($request->requested_amount) }} VNĐ</strong>
                                        </td>
                                        <td class="text-right">
                                            @if($request->approved_amount)
                                                <strong class="text-success">{{ number_format($request->approved_amount) }} VNĐ</strong>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($request->status)
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
                                            @endswitch
                                        </td>
                                        <td>{{ $request->created_at ? $request->created_at->format('d/m/Y H:i') : 'Chưa có ngày' }}</td>
                                        <td style="min-width: 120px; width: 120px;">
                                            <div class="d-flex flex-column gap-1">
                                                <a href="{{ route('admin.fund-requests.show', $request->id) }}" 
                                                   class="btn btn-sm btn-primary text-white w-100">
                                                    <i class="fas fa-eye"></i> Xem chi tiết
                                                </a>
                                                @if($request->status === 'pending')
                                                    <form action="{{ route('admin.fund-requests.destroy', $request->id) }}" 
                                                          method="POST" class="d-inline" 
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa yêu cầu này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger w-100 text-white">
                                                            <i class="fas fa-trash"></i> Xóa
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Không tìm thấy yêu cầu cấp kinh phí nào</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            Hiển thị {{ $requests->firstItem() }} đến {{ $requests->lastItem() }} 
                            trong tổng số {{ $requests->total() }} yêu cầu
                        </div>
                        <div>
                            {{ $requests->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
