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
            <p class="stats-number">{{ $events->where('status', 'ongoing')->count() }}</p>
            <p class="stats-label">Đang diễn ra</p>
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
            <p class="stats-number">{{ $events->where('status', 'cancelled')->count() }}</p>
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
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Đang diễn ra</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-3 text-end">
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('admin.plans-schedule') }}" class="btn btn-secondary">
                        <i class="fas fa-refresh"></i> Làm mới
                    </a>
                    <a href="{{ route('admin.events.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Tạo lịch trình sự kiện
                    </a>
                </div>
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
                                @if($event->location)
                                    <br><strong>Địa điểm:</strong> {{ $event->location }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $modeColors = [
                                        'offline' => 'primary',
                                        'online' => 'success',
                                        'hybrid' => 'info'
                                    ];
                                    $modeLabels = [
                                        'offline' => 'Trực tiếp',
                                        'online' => 'Trực tuyến',
                                        'hybrid' => 'Kết hợp'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $modeColors[$event->mode] ?? 'secondary' }}">
                                    {{ $modeLabels[$event->mode] ?? ucfirst($event->mode) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'ongoing' => 'success',
                                        'completed' => 'primary',
                                        'cancelled' => 'danger'
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Bản nháp',
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'ongoing' => 'Đang diễn ra',
                                        'completed' => 'Hoàn thành',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$event->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$event->status] ?? ucfirst($event->status) }}
                                </span>
                            </td>
                            <td>{{ $event->creator->name ?? 'Không xác định' }}</td>
                            <td style="min-width: 140px; width: 140px;">
                                <div class="d-flex flex-column gap-1">
                                    @if($event->status === 'pending')
                                        <form method="POST" action="{{ route('admin.events.approve', $event->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Bạn có chắc muốn duyệt sự kiện này?')">
                                                <i class="fas fa-check"></i> Duyệt
                                            </button>
                                        </form>
                                        
                                        <form method="POST" action="{{ route('admin.events.cancel', $event->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('Bạn có chắc chắn muốn hủy sự kiện này?')">
                                                <i class="fas fa-times"></i> Hủy
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-sm btn-warning w-100">
                                        <i class="fas fa-edit"></i> Chỉnh sửa
                                    </a>
                                    <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-sm btn-info w-100">
                                        <i class="fas fa-eye"></i> Xem chi tiết
                                    </a>
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
