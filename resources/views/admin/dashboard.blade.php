@extends('admin.layouts.app')

@section('title', 'Dashboard - CLB Admin')

@section('content')
<div class="content-header">
    <h1>Dashboard</h1>
</div>

<div class="row">
    <!-- Thống kê tổng quan -->
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #007bff;">
                <i class="fas fa-users"></i>
            </div>
            <p class="stats-number">{{ $totalUsers }}</p>
            <p class="stats-label">Tổng người dùng</p>
            <a href="{{ route('admin.users') }}" class="stats-link">Xem tất cả</a>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #28a745;">
                <i class="fas fa-users"></i>
            </div>
            <p class="stats-number">{{ $totalClubs }}</p>
            <p class="stats-label">Tổng câu lạc bộ</p>
            <a href="{{ route('admin.clubs') }}" class="stats-link">Xem tất cả</a>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #ffc107;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <p class="stats-number">{{ $totalEvents }}</p>
            <p class="stats-label">Tổng sự kiện</p>
            <a href="#" class="stats-link">Xem tất cả</a>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #dc3545;">
                <i class="fas fa-newspaper"></i>
            </div>
            <p class="stats-number">{{ $totalPosts }}</p>
            <p class="stats-label">Tổng bài viết</p>
            <a href="#" class="stats-link">Xem tất cả</a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Người dùng mới -->
    <div class="col-md-6">
        <div class="user-list">
            <h5 class="mb-3">Người dùng mới</h5>
            
            @if($newUsers->count() > 0)
                @foreach($newUsers as $user)
                    <div class="user-item">
                        <img src="{{ $user->avatar ?? '/images/avatar/avatar.png' }}" 
                             alt="{{ $user->name }}" 
                             class="user-avatar"
                             onerror="this.src='/images/avatar/avatar.png'">
                        <div class="user-info">
                            <h6>{{ $user->name }}</h6>
                            <small>{{ $user->email }}</small>
                            @if($user->phone)
                                <br><small>{{ $user->phone }}</small>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-muted">Không có người dùng mới trong 7 ngày qua</p>
            @endif
        </div>
    </div>
    
    <!-- Câu lạc bộ mới -->
    <div class="col-md-6">
        <div class="user-list">
            <h5 class="mb-3">Câu lạc bộ mới</h5>
            
            @if($newClubs->count() > 0)
                @foreach($newClubs as $club)
                    <div class="user-item">
                        <div class="user-avatar" style="background-color: #6c757d; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-users text-white"></i>
                        </div>
                        <div class="user-info">
                            <h6>{{ $club->name }}</h6>
                            <small>{{ $club->field->name ?? 'Không xác định' }}</small>
                            <br><small>Chủ sở hữu: {{ $club->owner->name ?? 'Không xác định' }}</small>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-muted">Không có câu lạc bộ mới trong 7 ngày qua</p>
            @endif
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Sự kiện sắp diễn ra -->
    <div class="col-12">
        <div class="user-list">
            <h5 class="mb-3">Sự kiện sắp diễn ra</h5>
            
            @if($upcomingEvents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên sự kiện</th>
                                <th>Câu lạc bộ</th>
                                <th>Thời gian bắt đầu</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingEvents as $event)
                                <tr>
                                    <td>{{ $event->title }}</td>
                                    <td>{{ $event->club->name ?? 'Không xác định' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($event->start_time)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $event->status === 'active' ? 'success' : 'warning' }}">
                                            {{ ucfirst($event->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">Không có sự kiện sắp diễn ra</p>
            @endif
        </div>
    </div>
</div>
@endsection
