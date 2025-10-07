@extends('admin.layouts.app')

@section('title', 'Kế hoạch - CLB Admin')

@section('content')
<div class="content-header">
    <h1>Kế hoạch & Lịch trình</h1>
</div>

<!-- Thống kê sự kiện -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #007bff;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <p class="stats-number">{{ $events->total() }}</p>
            <p class="stats-label">Tổng sự kiện</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #28a745;">
                <i class="fas fa-play"></i>
            </div>
            <p class="stats-number">{{ $events->where('status', 'active')->count() }}</p>
            <p class="stats-label">Đang hoạt động</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #ffc107;">
                <i class="fas fa-clock"></i>
            </div>
            <p class="stats-number">{{ $events->where('status', 'pending')->count() }}</p>
            <p class="stats-label">Chờ duyệt</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #dc3545;">
                <i class="fas fa-ban"></i>
            </div>
            <p class="stats-number">{{ $events->where('status', 'canceled')->count() }}</p>
            <p class="stats-label">Đã hủy</p>
        </div>
    </div>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.plans-schedule') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm sự kiện..."
                       value="{{ request('search') }}">
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
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Đã hủy</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-3 text-end">
                <a href="{{ route('admin.plans-schedule') }}" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách sự kiện -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên sự kiện</th>
                        <th>Câu lạc bộ</th>
                        <th>Thời gian</th>
                        <th>Chế độ</th>
                        <th>Trạng thái</th>
                        <th>Người tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>{{ $event->id }}</td>
                            <td>
                                <strong>{{ $event->title }}</strong>
                                <br><small class="text-muted">{{ Str::limit($event->description, 50) }}</small>
                            </td>
                            <td>{{ $event->club->name ?? 'Không xác định' }}</td>
                            <td>
                                <strong>Bắt đầu:</strong> {{ \Carbon\Carbon::parse($event->start_time)->format('d/m/Y H:i') }}
                                <br><strong>Kết thúc:</strong> {{ \Carbon\Carbon::parse($event->end_time)->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $event->mode === 'public' ? 'success' : 'warning' }}">
                                    {{ $event->mode === 'public' ? 'Công khai' : 'Riêng tư' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'active' => 'success',
                                        'canceled' => 'danger',
                                        'completed' => 'secondary'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'active' => 'Đang hoạt động',
                                        'canceled' => 'Đã hủy',
                                        'completed' => 'Hoàn thành'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$event->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$event->status] ?? ucfirst($event->status) }}
                                </span>
                            </td>
                            <td>{{ $event->creator->name ?? 'Không xác định' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($event->status === 'pending')
                                        <form method="POST" action="{{ route('admin.clubs.status', $event->id) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Duyệt
                                            </button>
                                        </form>
                                        
                                        <form method="POST" action="{{ route('admin.clubs.status', $event->id) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="canceled">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn hủy sự kiện này?')">
                                                <i class="fas fa-times"></i> Hủy
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($event->status === 'approved')
                                        <form method="POST" action="{{ route('admin.clubs.status', $event->id) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-play"></i> Kích hoạt
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($event->status === 'active')
                                        <form method="POST" action="{{ route('admin.clubs.status', $event->id) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Bạn có chắc chắn muốn đánh dấu sự kiện này là hoàn thành?')">
                                                <i class="fas fa-check-double"></i> Hoàn thành
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <button class="btn btn-sm btn-info" onclick="viewEvent({{ $event->id }})">
                                        <i class="fas fa-eye"></i> Xem
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Không tìm thấy sự kiện nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        @if($events->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $events->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
