@extends('admin.layouts.app')

@section('title', 'Thành viên CLB - ' . $club->name)

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Thành viên CLB: {{ $club->name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.clubs') }}">Quản lý CLB</a></li>
                    <li class="breadcrumb-item active">Thành viên</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.clubs') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Thông tin CLB -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h5 class="card-title">{{ $club->name }}</h5>
                <p class="card-text">{{ strip_tags($club->description) }}</p>
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-user me-1"></i>
                            <strong>Chủ sở hữu:</strong> {{ $club->owner->name ?? 'Không xác định' }}
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-users me-1"></i>
                            <strong>Tổng thành viên:</strong> {{ $members->count() }}
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="club-logo-fallback rounded d-inline-flex align-items-center justify-content-center" 
                     style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: bold; font-size: 24px;">
                    {{ strtoupper(substr($club->name, 0, 2)) }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Thống kê thành viên -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-primary">{{ $members->where('position', 'leader')->count() }}</h5>
                <p class="card-text">Trưởng CLB</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-success">{{ $members->where('position', 'vice_president')->count() }}</h5>
                <p class="card-text">Phó trưởng</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-info">{{ $members->where('position', 'officer')->count() }}</h5>
                <p class="card-text">Cán sự</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-warning">{{ $members->where('position', 'member')->count() }}</h5>
                <p class="card-text">Thành viên</p>
            </div>
        </div>
    </div>
</div>

<!-- Danh sách thành viên -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Danh sách thành viên</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Thành viên</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tham gia</th>
                        <th>Thông tin liên hệ</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $index => $member)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="member-avatar me-3">
                                        <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center">
                                            {{ strtoupper(substr($member->user->name ?? 'U', 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <strong>{{ $member->user->name ?? 'Không xác định' }}</strong>
                                        <br><small class="text-muted">{{ $member->user->student_id ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $positionColors = [
                                        'leader' => 'danger',
                                        'vice_president' => 'warning',
                                        'officer' => 'info',
                                        'member' => 'secondary'
                                    ];
                                    $positionLabels = [
                                        'leader' => 'Trưởng CLB',
                                        'vice_president' => 'Phó trưởng',
                                        'officer' => 'Cán sự',
                                        'member' => 'Thành viên'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $positionColors[$member->position] ?? 'secondary' }}">
                                    {{ $positionLabels[$member->position] ?? ucfirst($member->position) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active' => 'success',
                                        'pending' => 'warning',
                                        'inactive' => 'secondary'
                                    ];
                                    $statusLabels = [
                                        'active' => 'Hoạt động',
                                        'pending' => 'Chờ duyệt',
                                        'inactive' => 'Không hoạt động'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$member->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$member->status] ?? ucfirst($member->status) }}
                                </span>
                            </td>
                            <td>
                                {{ $member->joined_at ? $member->joined_at->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td>
                                <div class="small">
                                    <div><i class="fas fa-envelope me-1"></i> {{ $member->user->email ?? 'N/A' }}</div>
                                    <div><i class="fas fa-phone me-1"></i> {{ $member->user->phone ?? 'N/A' }}</div>
                                </div>
                            </td>
                            <td>
                                <form action="{{ route('admin.clubs.members.remove', ['club' => $club->id, 'member' => $member->id]) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa thành viên {{ $member->user->name ?? 'này' }} khỏi CLB này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-3 d-block"></i>
                                CLB này chưa có thành viên nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-weight: bold;
    font-size: 16px;
}

.member-avatar .avatar-circle {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.club-logo-fallback {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}
</style>
@endsection
