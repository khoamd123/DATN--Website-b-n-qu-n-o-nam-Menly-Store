{{-- Advanced User Statistics --}}
<div class="row mt-4">
    <!-- Sự kiện đã tổ chức vs tham gia -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Thống kê sự kiện</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h3 class="text-primary">{{ $userStats['events']['created'] }}</h3>
                        <small class="text-muted">Đã tổ chức</small>
                    </div>
                    <div class="col-4">
                        <h3 class="text-success">{{ $userStats['events']['participated'] }}</h3>
                        <small class="text-muted">Đã tham gia</small>
                    </div>
                    <div class="col-4">
                        <h3 class="text-info">{{ $userStats['events']['upcoming'] }}</h3>
                        <small class="text-muted">Sắp tham gia</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thời gian online gần đây -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock"></i> Trạng thái online</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        @if($userStats['last_online']->diffInMinutes(now()) < 5)
                            <span class="badge bg-success">Online</span>
                        @elseif($userStats['last_online']->diffInHours(now()) < 1)
                            <span class="badge bg-warning">Vừa offline</span>
                        @else
                            <span class="badge bg-secondary">Offline</span>
                        @endif
                    </div>
                    <div>
                        <strong>Lần cuối online:</strong><br>
                        <small class="text-muted">{{ $userStats['last_online']->format('d/m/Y H:i:s') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tần suất hoạt động -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Tần suất hoạt động</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-primary">{{ $userStats['activity']['daily'] }}</h4>
                        <small class="text-muted">Hôm nay</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-success">{{ $userStats['activity']['weekly'] }}</h4>
                        <small class="text-muted">7 ngày qua</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-info">{{ $userStats['activity']['monthly'] }}</h4>
                        <small class="text-muted">30 ngày qua</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thông báo và báo cáo -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bell"></i> Thông báo & Báo cáo</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-warning">{{ $userStats['unread_notifications'] }}</h4>
                        <small class="text-muted">Thông báo chưa đọc</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-danger">{{ $userStats['reports']['total'] }}</h4>
                        <small class="text-muted">Báo cáo vi phạm</small>
                    </div>
                </div>
                @if($userStats['reports']['total'] > 0)
                    <div class="mt-2">
                        <small class="text-muted">
                            <span class="badge bg-success">{{ $userStats['reports']['resolved'] }} đã xử lý</span>
                            <span class="badge bg-warning">{{ $userStats['reports']['pending'] }} chờ xử lý</span>
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
