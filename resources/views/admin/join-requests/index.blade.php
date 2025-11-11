@extends('admin.layouts.app')

@section('title', 'Đơn tham gia CLB')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Đơn tham gia CLB</h3>
        <form method="GET" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="">Tất cả</option>
                <option value="pending" {{ ($status ?? '')==='pending' ? 'selected' : '' }}>Chờ duyệt</option>
                <option value="approved" {{ ($status ?? '')==='approved' ? 'selected' : '' }}>Đã duyệt</option>
                <option value="rejected" {{ ($status ?? '')==='rejected' ? 'selected' : '' }}>Đã từ chối</option>
            </select>
            <select name="club_id" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="">Tất cả CLB</option>
                @foreach($clubs as $c)
                    <option value="{{ $c->id }}" @selected(request('club_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm" placeholder="Tên/Email">
            <button class="btn btn-sm btn-outline-primary">Lọc</button>
        </form>
    </div>

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
                        <td class="text-end">
                            @if($req->status === 'pending')
                            <form method="POST" action="{{ route('admin.join-requests.approve', $req->id) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success">Duyệt</button>
                            </form>
                            <form method="POST" action="{{ route('admin.join-requests.reject', $req->id) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">Từ chối</button>
                            </form>
                            @else
                                <small class="text-muted">Đã {{ $req->status === 'approved' ? 'duyệt' : 'từ chối' }}</small>
                            @endif
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


