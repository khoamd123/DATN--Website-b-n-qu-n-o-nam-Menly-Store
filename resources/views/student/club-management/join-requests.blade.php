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
                        <form method="POST" action="{{ route('student.club-management.join-requests.reject', ['club' => $clubId, 'request' => $req->id]) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Từ chối</button>
                        </form>
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
@endsection






