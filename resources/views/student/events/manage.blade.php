@extends('layouts.student')

@section('title', 'Quản lý sự kiện - UniClubs')

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-12">
        <!-- Page Header -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-calendar-check text-teal"></i> Quản lý sự kiện
                    </h2>
                    <p class="text-muted mb-0">Quản lý sự kiện của CLB: <strong>{{ $userClub->name }}</strong></p>
                </div>
                <div>
                    <a href="{{ route('student.club-management.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                    <a href="{{ route('student.events.create', $clubId ? ['club' => $clubId] : []) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Tạo sự kiện mới
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Bộ lọc -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Bộ lọc</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('student.events.manage') }}" class="row g-3">
                        @if(request('club'))
                            <input type="hidden" name="club" value="{{ request('club') }}">
                        @endif
                        <div class="col-md-6">
                            <label for="search" class="form-label small">Tìm kiếm</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên sự kiện...">
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label small">Trạng thái</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>Tất cả</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Đang diễn ra</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                <i class="fas fa-search me-1"></i> Lọc
                            </button>
                            <a href="{{ route('student.events.manage', request('club') ? ['club' => request('club')] : []) }}" class="btn btn-outline-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danh sách sự kiện -->
            @if($events->count() === 0)
                <div class="text-center py-5">
                    <i class="far fa-calendar fa-2x text-muted mb-3"></i>
                    <p class="text-muted mb-2">CLB này chưa có sự kiện nào.</p>
                    <a href="{{ route('student.events.create', $clubId ? ['club' => $clubId] : []) }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tạo sự kiện</a>
                </div>
            @else
                <div class="row">
                    @foreach($events as $event)
                        @include('student.events._event_card', ['event' => $event])
                    @endforeach
                </div>
                
                <!-- Phân trang -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


