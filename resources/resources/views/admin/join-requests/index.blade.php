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
                        <th>Lời nhắn</th>
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
                        <td class="text-muted" style="max-width:320px">{{ $req->message }}</td>
                        <td>
                            @php $map = ['pending'=>'warning','approved'=>'success','rejected'=>'secondary']; @endphp
                            <span class="badge bg-{{ $map[$req->status] ?? 'light' }}">{{ $req->status }}</span>
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
                                    <form method="POST" action="{{ route('admin.join-requests.reject', $req->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger w-100 text-white">
                                            <i class="fas fa-times"></i> Từ chối
                                        </button>
                                    </form>
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
@endsection


