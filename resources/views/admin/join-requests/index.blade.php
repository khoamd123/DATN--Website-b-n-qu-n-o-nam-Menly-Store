@extends('admin.layouts.app')

@section('title', 'Đơn tham gia CLB')

@section('content')
<div class="content-header">
    <h1>Đơn tham gia CLB</h1>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.join-requests') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" 
                       name="keyword" 
                       class="form-control" 
                       placeholder="Tìm kiếm theo tên, email..."
                       value="{{ request('keyword') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="club_id" class="form-select">
                    <option value="">Tất cả CLB</option>
                    @foreach($clubs as $c)
                        <option value="{{ $c->id }}" {{ request('club_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-auto ms-auto">
                <a href="{{ route('admin.join-requests') }}" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<div class="content-card">

    <form method="POST" action="{{ route('admin.join-requests.bulk') }}">
        @csrf
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width:28px"><input type="checkbox" onclick="document.querySelectorAll('.jr-check').forEach(c=>c.checked=this.checked)"></th>
                        <th>Người nộp</th>
                        <th>CLB</th>
                        <th>Trạng thái</th>
                        <th>Đánh giá bởi</th>
                        <th>Thời gian</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr>
                        <td>
                            @if($req->status === 'pending')
                                <input type="checkbox" class="jr-check" name="ids[]" value="{{ $req->id }}">
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ optional($req->user)->avatar_url ?? asset('images/avatar/avatar.png') }}" width="36" height="36" class="rounded-circle me-2" alt="">
                                <div>
                                    <div class="fw-semibold">{{ optional($req->user)->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ optional($req->user)->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ optional($req->club)->name ?? 'N/A' }}</td>
                        <td>
                            @php $map = ['pending'=>'warning','approved'=>'success','rejected'=>'secondary']; @endphp
                            @php $statusLabel = ['pending' => 'Đang chờ', 'approved' => 'Đã duyệt', 'rejected' => 'Đã từ chối']; @endphp
                            <span class="badge bg-{{ $map[$req->status] ?? 'light' }}">{{ $statusLabel[$req->status] ?? $req->status }}</span>
                        </td>
                        <td>{{ optional($req->reviewer)->name ?? '-' }}</td>
                        <td>
                            <small class="text-muted">{{ $req->created_at?->format('d/m/Y H:i') }}</small>
                        </td>
                        <td style="min-width: 120px; width: 120px;">
                            <div class="d-flex flex-column gap-1">
                                @if($req->status === 'pending')
                                    <form method="POST" action="{{ route('admin.join-requests.approve', $req->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success w-100 text-white">
                                            <i class="fas fa-check"></i> Duyệt
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger w-100 text-white" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal{{ $req->id }}">
                                        <i class="fas fa-times"></i> Từ chối
                                    </button>
                                @else
                                    <span class="text-muted small">Đã {{ $req->status === 'approved' ? 'duyệt' : 'từ chối' }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Không có đơn phù hợp.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <select name="action" class="form-select form-select-sm" style="width:auto">
                    <option value="approve">Duyệt hàng loạt</option>
                    <option value="reject">Từ chối hàng loạt</option>
                </select>
                <button class="btn btn-sm btn-primary" type="submit">Thực hiện</button>
            </div>
            {{ $requests->links() }}
        </div>
    </form>
</div>

<!-- Modal Từ chối đơn tham gia -->
@foreach($requests as $req)
@if($req->status === 'pending')
<div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $req->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel{{ $req->id }}">Từ chối đơn tham gia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.join-requests.reject', $req->id) }}">
                @csrf
                <div class="modal-body">
                    <p>Bạn sắp từ chối đơn tham gia của: <strong>{{ $req->user->name ?? 'N/A' }}</strong></p>
                    <p>CLB: <strong>{{ $req->club->name ?? 'N/A' }}</strong></p>
                    <div class="mb-3">
                        <label for="rejection_reason{{ $req->id }}" class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason{{ $req->id }}" name="rejection_reason" rows="4" required placeholder="Nhập lý do từ chối đơn tham gia..."></textarea>
                        <small class="text-muted">Lý do từ chối sẽ được gửi qua email cho người đăng ký.</small>
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
@endforeach
@endsection


