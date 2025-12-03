@extends('admin.layouts.app')

@section('title', 'Thông báo - CLB Admin')

@section('content')
<div class="content-header">
    <h1><i class="fas fa-bell text-primary"></i> Thông báo</h1>
    <p class="text-muted">Quản lý và xem tất cả thông báo hệ thống</p>
</div>

<!-- Bộ lọc -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.notifications') }}" class="row g-3">
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">Tất cả loại</option>
                    <option value="event_registration" {{ request('type') == 'event_registration' ? 'selected' : '' }}>Đăng ký sự kiện</option>
                    <option value="event_created" {{ request('type') == 'event_created' ? 'selected' : '' }}>Sự kiện mới cần duyệt</option>
                    <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>Thông báo chung</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Chưa đọc</option>
                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Đã đọc</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Lọc
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.notifications') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
            <div class="col-md-2">
                <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check-double"></i> Đánh dấu tất cả đã đọc
                    </button>
                </form>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách thông báo -->
<div class="card">
    <div class="card-body">
        @if($notifications->count() > 0)
            <div class="list-group">
                @foreach($notifications as $notification)
                    <a href="{{ route('admin.notifications.show', $notification->id) }}" 
                       class="list-group-item list-group-item-action {{ $notification->read_at ? '' : 'list-group-item-primary' }}"
                       style="border-left: {{ $notification->read_at ? 'none' : '4px solid #007bff' }};">
                        <div class="d-flex w-100 justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    @if($notification->type === 'event_registration')
                                        <i class="fas fa-calendar-check text-primary me-2"></i>
                                    @elseif($notification->type === 'event_created')
                                        <i class="fas fa-calendar-plus text-warning me-2"></i>
                                    @else
                                        <i class="fas fa-bell text-info me-2"></i>
                                    @endif
                                    <h6 class="mb-0 {{ $notification->read_at ? '' : 'fw-bold' }}">
                                        {{ $notification->title }}
                                    </h6>
                                    @if(!$notification->read_at)
                                        <span class="badge bg-danger ms-2">Mới</span>
                                    @endif
                                </div>
                                <p class="mb-1">{{ $notification->message }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    {{ $notification->sender->name ?? 'Hệ thống' }}
                                    <span class="ms-2">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                    @if($notification->read_at)
                                        <span class="ms-2 text-success">
                                            <i class="fas fa-check me-1"></i>
                                            Đã đọc: {{ $notification->read_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </small>
                            </div>
                            <div class="ms-3">
                                @if($notification->related_type === 'App\Models\Event' && $notification->related_id)
                                    <a href="{{ route('admin.events.show', $notification->related_id) }}" 
                                       class="btn btn-sm btn-outline-primary"
                                       onclick="event.stopPropagation();">
                                        <i class="fas fa-eye"></i> Xem sự kiện
                                    </a>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <p class="text-muted">Không có thông báo nào</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('styles')
<style>
    .list-group-item {
        transition: all 0.2s ease;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }
    
    .list-group-item-primary {
        background-color: #e7f3ff;
    }
</style>
@endsection

