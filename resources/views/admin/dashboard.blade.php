@extends('admin.layouts.app')

@section('title', 'Dashboard - CLB Admin')

@section('styles')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    #monthlyChart, #fieldChart {
        max-height: 400px !important;
        width: 100% !important;
    }
    
    .chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <h1>Dashboard</h1>
    <p class="text-muted">Tổng quan hệ thống quản lý câu lạc bộ</p>
</div>

{{-- Date Filter --}}
@include('admin.partials.dashboard-date-filter')

{{-- Filter Status Indicator --}}
@if($startDate && $endDate)
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-filter me-2"></i>
            <strong>Đang hiển thị thống kê từ:</strong> 
            {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} 
            <strong>đến:</strong> 
            {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary ms-2">
                <i class="fas fa-times me-1"></i>Xóa bộ lọc
            </a>
        </div>
    </div>
</div>
@endif

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Quick Actions</h5>
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('admin.users') }}" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-user-plus"></i> Quản lý Users
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.clubs') }}" class="btn btn-success btn-sm w-100">
                            <i class="fas fa-users"></i> Quản lý CLB
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.posts') }}" class="btn btn-info btn-sm w-100">
                            <i class="fas fa-newspaper"></i> Quản lý Bài viết
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.permissions') }}" class="btn btn-warning btn-sm w-100">
                            <i class="fas fa-key"></i> Phân quyền
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Thống kê tổng quan -->
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #007bff;">
                <i class="fas fa-users"></i>
            </div>
            <p class="stats-number">{{ $usersInPeriod }}</p>
            <p class="stats-label">Người dùng trong khoảng thời gian</p>
            <small class="text-success">+{{ $usersLastMonth }} tháng này</small>
            <a href="{{ route('admin.users') }}" class="stats-link">Xem tất cả</a>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #28a745;">
                <i class="fas fa-users"></i>
            </div>
            <p class="stats-number">{{ $clubsInPeriod }}</p>
            <p class="stats-label">Câu lạc bộ trong khoảng thời gian</p>
            <small class="text-info">{{ $activeClubs }} hoạt động, {{ $pendingClubs }} chờ duyệt</small>
            <a href="{{ route('admin.clubs') }}" class="stats-link">Xem tất cả</a>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #ffc107;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <p class="stats-number">{{ $eventsInPeriod }}</p>
            <p class="stats-label">Sự kiện trong khoảng thời gian</p>
            <small class="text-warning">{{ $activeEvents }} đang hoạt động</small>
            <a href="{{ route('admin.plans-schedule') }}" class="stats-link">Xem tất cả</a>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #dc3545;">
                <i class="fas fa-newspaper"></i>
            </div>
            <p class="stats-number">{{ $postsInPeriod }}</p>
            <p class="stats-label">Bài viết trong khoảng thời gian</p>
            <small class="text-success">+{{ $postsLastMonth }} tháng này</small>
            <a href="{{ route('admin.posts') }}" class="stats-link">Xem tất cả</a>
        </div>
    </div>
</div>

<!-- Biểu đồ thống kê -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Thống kê tăng trưởng (12 tháng gần nhất)</h5>
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">CLB theo lĩnh vực</h5>
                <div class="chart-container">
                    <canvas id="fieldChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top CLB hoạt động mạnh -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Top 5 CLB hoạt động mạnh nhất</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên CLB</th>
                                <th>Lĩnh vực</th>
                                <th>Số bài viết</th>
                                <th>Số sự kiện</th>
                                <th>Tổng hoạt động</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topClubs as $index => $club)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $club->name }}</td>
                                    <td>{{ $club->field->name ?? 'Không xác định' }}</td>
                                    <td><span class="badge bg-info">{{ $club->posts_count }}</span></td>
                                    <td><span class="badge bg-success">{{ $club->events_count }}</span></td>
                                    <td><span class="badge bg-primary">{{ $club->posts_count + $club->events_count }}</span></td>
                                    <td>
                                        <span class="badge bg-{{ $club->status === 'active' ? 'success' : ($club->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($club->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
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
                        @php
                            $avatarPath = $user->avatar ? public_path($user->avatar) : null;
                            $hasAvatar = $avatarPath && file_exists($avatarPath);
                        @endphp
                        
                        @if($hasAvatar)
                            <img src="{{ asset($user->avatar) }}" 
                                 alt="{{ $user->name }}" 
                                 class="user-avatar">
                        @else
                            <div class="user-avatar user-avatar-fallback">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        
                        <div class="user-info">
                            <h6>{{ $user->name }}</h6>
                            <small>{{ $user->email }}</small>
                            @if($user->phone)
                                <br><small>{{ $user->phone }}</small>
                            @endif
                            <br><small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
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
                            <br><small class="text-muted">{{ $club->created_at->diffForHumans() }}</small>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dữ liệu từ Laravel
    const monthlyStats = @json($monthlyStats ?? []);
    const clubsByField = @json($clubsByField ?? []);
    
    // Debug: Kiểm tra dữ liệu
    console.log('Monthly Stats:', monthlyStats);
    console.log('Clubs by Field:', clubsByField);
    
    // Kiểm tra và tạo biểu đồ thống kê theo tháng
    if (monthlyStats && monthlyStats.length > 0) {
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyStats.map(stat => stat.month),
            datasets: [
                {
                    label: 'Người dùng',
                    data: monthlyStats.map(stat => stat.users),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Câu lạc bộ',
                    data: monthlyStats.map(stat => stat.clubs),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Sự kiện',
                    data: monthlyStats.map(stat => stat.events),
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Bài viết',
                    data: monthlyStats.map(stat => stat.posts),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        }
        });
    } else {
        document.getElementById('monthlyChart').parentElement.innerHTML = '<p class="text-muted text-center">Không có dữ liệu để hiển thị</p>';
    }
    
    // Kiểm tra và tạo biểu đồ CLB theo lĩnh vực
    if (clubsByField && clubsByField.length > 0) {
        const fieldCtx = document.getElementById('fieldChart').getContext('2d');
        new Chart(fieldCtx, {
        type: 'doughnut',
        data: {
            labels: clubsByField.map(field => field.name),
            datasets: [{
                data: clubsByField.map(field => field.clubs_count),
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d',
                    '#17a2b8',
                    '#6f42c1',
                    '#e83e8c'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} CLB (${percentage}%)`;
                        }
                    }
                }
            }
        }
        });
    } else {
        document.getElementById('fieldChart').parentElement.innerHTML = '<p class="text-muted text-center">Không có dữ liệu để hiển thị</p>';
    }
});
</script>
@endsection
