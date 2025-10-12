@extends('layouts.student')

@section('title', 'Sự kiện - UniClubs')

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Page Header -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-calendar-alt text-teal"></i> Sự kiện
                    </h2>
                    <p class="text-muted mb-0">Khám phá các sự kiện thú vị và đăng ký tham gia</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active">Tất cả</button>
                    <button type="button" class="btn btn-outline-primary">Sắp tới</button>
                    <button type="button" class="btn btn-outline-primary">Đã tham gia</button>
                </div>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-clock text-warning me-2"></i> Sự kiện sắp tới
            </h4>
            
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <div class="event-date text-center">
                                        <div class="date-day">15</div>
                                        <div class="date-month">TH1</div>
                                        <div class="date-year">2024</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="card-title mb-2">Workshop "Lập trình Web hiện đại"</h5>
                                    <p class="card-text text-muted mb-2">
                                        <i class="fas fa-map-marker-alt me-2"></i> Phòng Lab A201
                                    </p>
                                    <p class="card-text mb-0">
                                        Tìm hiểu về các công nghệ mới nhất trong phát triển web như React, Node.js và các framework hiện đại.
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="mb-2">
                                        <span class="badge bg-success">Còn 5 chỗ</span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-users me-1"></i> 45/50 người đăng ký
                                        </small>
                                    </div>
                                    <button class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i> Đăng ký
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <div class="event-date text-center">
                                        <div class="date-day">20</div>
                                        <div class="date-month">TH1</div>
                                        <div class="date-year">2024</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="card-title mb-2">Game Jam 2024</h5>
                                    <p class="card-text text-muted mb-2">
                                        <i class="fas fa-map-marker-alt me-2"></i> Sân vận động
                                    </p>
                                    <p class="card-text mb-0">
                                        Cơ hội để các bạn thể hiện tài năng làm game của mình trong 48 giờ liên tục.
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="mb-2">
                                        <span class="badge bg-warning">Sắp hết hạn</span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-users me-1"></i> 18/20 nhóm đăng ký
                                        </small>
                                    </div>
                                    <button class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i> Đăng ký
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <div class="event-date text-center">
                                        <div class="date-day">25</div>
                                        <div class="date-month">TH1</div>
                                        <div class="date-year">2024</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="card-title mb-2">Hội thảo "Khởi nghiệp trong thời đại số"</h5>
                                    <p class="card-text text-muted mb-2">
                                        <i class="fas fa-map-marker-alt me-2"></i> Hội trường A
                                    </p>
                                    <p class="card-text mb-0">
                                        Lắng nghe chia sẻ từ các doanh nhân thành đạt về con đường khởi nghiệp.
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="mb-2">
                                        <span class="badge bg-success">Còn nhiều chỗ</span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-users me-1"></i> 120/200 người đăng ký
                                        </small>
                                    </div>
                                    <button class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i> Đăng ký
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Events -->
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-check-circle text-success me-2"></i> Sự kiện đã đăng ký
            </h4>
            
            <div class="text-center py-5">
                <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Bạn chưa đăng ký sự kiện nào</h5>
                <p class="text-muted">Hãy khám phá và đăng ký các sự kiện thú vị!</p>
                <a href="#" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i> Xem sự kiện
                </a>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="sidebar">
            <h5 class="sidebar-title">
                <i class="fas fa-calendar-week"></i> Lịch sự kiện
            </h5>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div>
                    <div class="fw-bold">Hôm nay</div>
                    <small class="text-muted">Không có sự kiện</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <div class="fw-bold">Tuần này</div>
                    <small class="text-muted">2 sự kiện</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div>
                    <div class="fw-bold">Tháng này</div>
                    <small class="text-muted">8 sự kiện</small>
                </div>
            </div>
        </div>

        <div class="sidebar mt-4">
            <h5 class="sidebar-title">
                <i class="fas fa-fire"></i> Sự kiện hot
            </h5>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <div class="fw-bold">Game Jam 2024</div>
                    <small class="text-muted">90% đã đăng ký</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div>
                    <div class="fw-bold">Workshop Web</div>
                    <small class="text-muted">85% đã đăng ký</small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .event-date {
        background: #f0fdfa;
        border: 2px solid #a7f3d0;
        border-radius: 12px;
        padding: 1rem;
        color: #14b8a6;
    }
    
    .date-day {
        font-size: 2rem;
        font-weight: bold;
        line-height: 1;
    }
    
    .date-month {
        font-size: 0.9rem;
        font-weight: 500;
        margin-top: 0.25rem;
    }
    
    .date-year {
        font-size: 0.8rem;
        opacity: 0.7;
    }
    
    .text-teal {
        color: #14b8a6 !important;
    }
</style>
@endpush
@endsection
