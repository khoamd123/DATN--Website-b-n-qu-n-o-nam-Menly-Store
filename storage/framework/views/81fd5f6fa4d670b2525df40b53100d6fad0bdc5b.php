

<?php $__env->startSection('title', 'Thông báo - UniClubs'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Page Header -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-bell text-teal"></i> Thông báo
                    </h2>
                    <p class="text-muted mb-0">Cập nhật mới nhất từ UniClubs và câu lạc bộ</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active">Tất cả</button>
                    <button type="button" class="btn btn-outline-primary">Chưa đọc</button>
                    <button type="button" class="btn btn-outline-primary">Đã đọc</button>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="content-card">
            <div class="notification-item unread">
                <div class="notification-icon bg-primary">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h6 class="mb-1">Chào mừng bạn đến với UniClubs!</h6>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> <?php echo e(now()->format('d/m/Y H:i')); ?>

                        </small>
                    </div>
                    <p class="notification-text mb-2">
                        Cảm ơn bạn đã tham gia UniClubs. Hãy khám phá các câu lạc bộ thú vị và đăng ký tham gia các sự kiện.
                    </p>
                    <div class="notification-actions">
                        <button class="btn btn-sm btn-outline-primary">Xem chi tiết</button>
                        <button class="btn btn-sm btn-link text-muted">Đánh dấu đã đọc</button>
                    </div>
                </div>
            </div>

            <div class="notification-item">
                <div class="notification-icon bg-success">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h6 class="mb-1">Sự kiện mới: Workshop "Lập trình Web hiện đại"</h6>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> <?php echo e(now()->subHours(2)->format('d/m/Y H:i')); ?>

                        </small>
                    </div>
                    <p class="notification-text mb-2">
                        CLB Công nghệ thông tin tổ chức workshop về lập trình web hiện đại. Đăng ký ngay để không bỏ lỡ cơ hội học hỏi.
                    </p>
                    <div class="notification-actions">
                        <button class="btn btn-sm btn-primary">Đăng ký tham gia</button>
                        <button class="btn btn-sm btn-link text-muted">Đánh dấu đã đọc</button>
                    </div>
                </div>
            </div>

            <div class="notification-item">
                <div class="notification-icon bg-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h6 class="mb-1">Nhắc nhở: Game Jam 2024 sắp hết hạn đăng ký</h6>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> <?php echo e(now()->subHours(5)->format('d/m/Y H:i')); ?>

                        </small>
                    </div>
                    <p class="notification-text mb-2">
                        Chỉ còn 2 ngày để đăng ký tham gia Game Jam 2024. Đừng bỏ lỡ cơ hội thể hiện tài năng lập trình game của bạn.
                    </p>
                    <div class="notification-actions">
                        <button class="btn btn-sm btn-warning">Đăng ký ngay</button>
                        <button class="btn btn-sm btn-link text-muted">Đánh dấu đã đọc</button>
                    </div>
                </div>
            </div>

            <div class="notification-item">
                <div class="notification-icon bg-info">
                    <i class="fas fa-users"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h6 class="mb-1">Thông báo từ CLB Công nghệ thông tin</h6>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> <?php echo e(now()->subDays(1)->format('d/m/Y H:i')); ?>

                        </small>
                    </div>
                    <p class="notification-text mb-2">
                        CLB sẽ tổ chức buổi họp mặt định kỳ vào cuối tuần này. Tất cả thành viên vui lòng tham gia đầy đủ.
                    </p>
                    <div class="notification-actions">
                        <button class="btn btn-sm btn-info">Xem chi tiết</button>
                        <button class="btn btn-sm btn-link text-muted">Đánh dấu đã đọc</button>
                    </div>
                </div>
            </div>

            <div class="notification-item">
                <div class="notification-icon bg-secondary">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h6 class="mb-1">Cuộc thi Hackathon 2024 đã kết thúc</h6>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> <?php echo e(now()->subDays(3)->format('d/m/Y H:i')); ?>

                        </small>
                    </div>
                    <p class="notification-text mb-2">
                        Cuộc thi Hackathon 2024 đã kết thúc thành công. Kết quả sẽ được công bố trong tuần tới.
                    </p>
                    <div class="notification-actions">
                        <button class="btn btn-sm btn-secondary">Xem kết quả</button>
                        <button class="btn btn-sm btn-link text-muted">Đánh dấu đã đọc</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Load More -->
        <div class="text-center">
            <button class="btn btn-outline-primary">
                <i class="fas fa-chevron-down me-2"></i> Xem thêm thông báo
            </button>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="sidebar">
            <h5 class="sidebar-title">
                <i class="fas fa-filter"></i> Lọc thông báo
            </h5>
            <div class="list-group list-group-flush">
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-info-circle me-2 text-primary"></i> Thông báo hệ thống
                    <span class="badge bg-primary rounded-pill ms-auto">1</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-calendar me-2 text-success"></i> Sự kiện
                    <span class="badge bg-success rounded-pill ms-auto">2</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-users me-2 text-info"></i> Câu lạc bộ
                    <span class="badge bg-info rounded-pill ms-auto">1</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-trophy me-2 text-warning"></i> Giải thưởng
                    <span class="badge bg-warning rounded-pill ms-auto">1</span>
                </a>
            </div>
        </div>

        <div class="sidebar mt-4">
            <h5 class="sidebar-title">
                <i class="fas fa-cog"></i> Cài đặt thông báo
            </h5>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                <label class="form-check-label" for="emailNotifications">
                    Thông báo qua email
                </label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="pushNotifications" checked>
                <label class="form-check-label" for="pushNotifications">
                    Thông báo đẩy
                </label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="eventNotifications" checked>
                <label class="form-check-label" for="eventNotifications">
                    Thông báo sự kiện
                </label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="clubNotifications" checked>
                <label class="form-check-label" for="clubNotifications">
                    Thông báo từ CLB
                </label>
            </div>
            <button class="btn btn-primary btn-sm w-100">
                <i class="fas fa-save me-2"></i> Lưu cài đặt
            </button>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .notification-item {
        display: flex;
        padding: 1.5rem;
        border-bottom: 1px solid #f3f4f6;
        transition: all 0.2s ease;
    }
    
    .notification-item:last-child {
        border-bottom: none;
    }
    
    .notification-item:hover {
        background-color: #f9fafb;
    }
    
    .notification-item.unread {
        background-color: #f0fdfa;
        border-left: 4px solid #14b8a6;
    }
    
    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .notification-content {
        flex-grow: 1;
    }
    
    .notification-header {
        display: flex;
        justify-content: between;
        align-items: flex-start;
        margin-bottom: 0.5rem;
    }
    
    .notification-text {
        color: #6b7280;
        line-height: 1.5;
    }
    
    .notification-actions {
        margin-top: 0.75rem;
    }
    
    .notification-actions .btn {
        margin-right: 0.5rem;
    }
    
    .text-teal {
        color: #14b8a6 !important;
    }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.student', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\DATN_Uniclubs\resources\views/student/notifications/index.blade.php ENDPATH**/ ?>