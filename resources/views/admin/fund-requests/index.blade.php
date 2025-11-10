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
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-money-bill-wave"></i>
                        Quản lý yêu cầu cấp kinh phí
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.funds') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay về quản lý quỹ
                        </a>
                        {{-- Bỏ nút tạo yêu cầu cấp kinh phí --}}
                    </div>
                </div>

                                 <div class="card-body">
                     <!-- Bộ lọc và tìm kiếm -->
                     <form method="GET" class="mb-4 p-2" style="background-color: #f8f9fa; border-radius: 8px;">
                         <div class="row g-2">
                             <div class="col-md-4">
                                 <label for="search" class="form-label fw-bold" style="font-size: 13px;">Tìm kiếm</label>
                                 <input type="text" class="form-control form-control-sm" id="search" name="search" 
                                        value="{{ request('search') }}" 
                                        placeholder="Tiêu đề, mô tả, sự kiện, CLB...">
                             </div>
                             <div class="col-md-3">
                                 <label for="status" class="form-label fw-bold" style="font-size: 13px;">Trạng thái</label>
                                 <select class="form-select form-select-sm" id="status" name="status">
                                     <option value="">Tất cả</option>
                                     <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                     <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                     <option value="partially_approved" {{ request('status') == 'partially_approved' ? 'selected' : '' }}>Duyệt một phần</option>
                                     <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                                 </select>
                             </div>
                             <div class="col-md-3">
                                 <label for="club_id" class="form-label fw-bold" style="font-size: 13px;">CLB</label>
                                 <select class="form-select form-select-sm" id="club_id" name="club_id">
                                     <option value="">Tất cả CLB</option>
                                     @foreach($clubs as $club)
                                         <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                                             {{ $club->name }}
                                         </option>
                                     @endforeach
                                 </select>
                             </div>
                             <div class="col-md-2 d-flex align-items-end">
                                 <div class="w-100">
                                     <button type="submit" class="btn btn-primary btn-sm w-100 mb-1">
                                         <i class="fas fa-search"></i> Tìm kiếm
                                     </button>
                                     <a href="{{ route('admin.fund-requests') }}" class="btn btn-secondary btn-sm w-100">
                                         <i class="fas fa-times"></i> Xóa bộ lọc
                                     </a>
                                 </div>
                             </div>
                         </div>
                     </form>

                    <!-- Bảng danh sách -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tiêu đề</th>
                                    <th>Sự kiện</th>
                                    <th>CLB</th>
                                    <th>Quỹ</th>
                                    <th>Số tiền yêu cầu</th>
                                    <th>Số tiền duyệt</th>
                                    <th>Trạng thái</th>
                                    <th>Người tạo</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $index => $request)
                                    <tr>
                                        <td>{{ $requests->firstItem() + $index }}</td>
                                        <td>
                                            <a href="{{ route('admin.fund-requests.show', $request->id) }}" class="text-dark text-decoration-none">
                                                <strong>{{ $request->title }}</strong>
                                            </a>
                                            {{-- Không hiện chữ nhạt dưới phần tiêu đề, chỉ hiện sự kiện nếu có --}}
                                            @if($request->event)
                                                <br><small class="text-info">
                                                    <i class="fas fa-calendar-alt"></i> 
                                                    <a href="{{ route('admin.events.show', $request->event->id) }}" class="text-dark text-decoration-none">
                                                        {{ $request->event->title ?? $request->event->name }}
                                                    </a>
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->event)
                                                <a href="{{ route('admin.events.show', $request->event->id) }}" class="text-dark text-decoration-none">
                                                    {{ $request->event->title ?? $request->event->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">Không có</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->club)
                                                <a href="{{ route('admin.clubs.show', $request->club->id) }}" class="badge bg-info text-dark text-decoration-none">
                                                    {{ $request->club->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">Không có</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->club)
                                                @php
                                                    $clubFund = \App\Models\Fund::where('club_id', $request->club->id)->where('status', 'active')->first();
                                                @endphp
                                                @if($clubFund)
                                                    <a href="{{ route('admin.funds.show', $clubFund->id) }}" class="badge bg-success text-white text-decoration-none" title="Xem chi tiết quỹ">
                                                        <i class="fas fa-wallet"></i> {{ number_format($clubFund->current_amount, 0, ',', '.') }} VNĐ
                                                    </a>
                                                @else
                                                    <span class="text-muted">Chưa có quỹ</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
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
                                        <td>
                                            @if($request->creator)
                                                {{ $request->creator->name }}
                                            @else
                                                <span class="text-muted">Không có</span>
                                            @endif
                                        </td>
                                        <td>{{ $request->created_at ? $request->created_at->format('d/m/Y H:i') : 'Chưa có ngày' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.fund-requests.show', $request->id) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($request->status === 'pending')
                                                    <a href="{{ route('admin.fund-requests.edit', $request->id) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.fund-requests.destroy', $request->id) }}" 
                                                          method="POST" class="d-inline" 
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa yêu cầu này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4">
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
