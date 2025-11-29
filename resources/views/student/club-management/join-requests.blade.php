@extends('layouts.student')

@section('title', 'Đơn tham gia CLB - UniClubs')

@section('content')
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            <i class="fas fa-user-plus text-teal me-2"></i> Đơn tham gia - {{ $club->name }}
        </h3>
        <a href="{{ route('student.club-management.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Quay lại quản lý CLB
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Thành viên</th>
                    <th>Email</th>
                    <th>Lời nhắn</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                    <th class="text-end">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td>{{ optional($req->user)->name ?? 'N/A' }}</td>
                    <td class="text-muted">{{ optional($req->user)->email }}</td>
                    <td class="text-muted" style="max-width: 360px">{{ $req->message }}</td>
                    <td>
                        @php $map = ['pending'=>'warning','approved'=>'success','rejected'=>'secondary']; @endphp
                        <span class="badge bg-{{ $map[$req->status] ?? 'light' }}">{{ $req->status }}</span>
                    </td>
                    <td><small class="text-muted">{{ $req->created_at?->format('d/m/Y H:i') }}</small></td>
                    <td class="text-end">
                        @if($req->status === 'pending')
                        <form method="POST" action="{{ route('student.club-management.join-requests.approve', ['club' => $clubId, 'request' => $req->id]) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">Duyệt</button>
                        </form>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#rejectModal{{ $req->id }}">
                            Từ chối
                        </button>
                        @else
                            <small class="text-muted">Đã {{ $req->status === 'approved' ? 'duyệt' : 'từ chối' }}</small>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Chưa có đơn tham gia.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $requests->links() }}
    </div>
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
            <form method="POST" action="{{ route('student.club-management.join-requests.reject', ['club' => $clubId, 'request' => $req->id]) }}">
                @csrf
                <div class="modal-body">
                    <p>Bạn sắp từ chối đơn tham gia của: <strong>{{ $req->user->name ?? 'N/A' }}</strong></p>
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











