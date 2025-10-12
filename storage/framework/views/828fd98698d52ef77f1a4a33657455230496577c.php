

<?php $__env->startSection('title', 'Câu lạc bộ - UniClubs'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Page Header -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-users text-teal"></i> Câu lạc bộ
                    </h2>
                    <p class="text-muted mb-0">Khám phá và tham gia các câu lạc bộ thú vị</p>
                </div>
                <a href="#" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i> Tìm kiếm CLB
                </a>
            </div>
        </div>

        <!-- My Clubs -->
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-star text-warning me-2"></i> CLB của tôi
            </h4>
            
            <?php if($user->clubs->count() > 0): ?>
                <div class="row">
                    <?php $__currentLoopData = $user->clubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $club): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="club-logo me-3">
                                        <?php echo e(substr($club->name, 0, 2)); ?>

                                    </div>
                                    <div>
                                        <h5 class="card-title mb-1"><?php echo e($club->name); ?></h5>
                                        <small class="text-muted">
                                            <i class="fas fa-user-friends"></i> <?php echo e($club->members->count()); ?> thành viên
                                        </small>
                                    </div>
                                </div>
                                <p class="card-text"><?php echo e(Str::limit($club->description, 100)); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-teal">Đã tham gia</span>
                                    <a href="#" class="btn btn-outline-primary btn-sm">Xem chi tiết</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Bạn chưa tham gia câu lạc bộ nào</h5>
                    <p class="text-muted">Hãy khám phá và tham gia các câu lạc bộ thú vị!</p>
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i> Tìm kiếm CLB
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Popular Clubs -->
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-fire text-danger me-2"></i> CLB phổ biến
            </h4>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="club-logo me-3">IT</div>
                                <div>
                                    <h5 class="card-title mb-1">CLB Công nghệ thông tin</h5>
                                    <small class="text-muted">
                                        <i class="fas fa-user-friends"></i> 150 thành viên
                                    </small>
                                </div>
                            </div>
                            <p class="card-text">Khám phá thế giới công nghệ và lập trình cùng các bạn sinh viên IT.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Đang hoạt động</span>
                                <a href="#" class="btn btn-primary btn-sm">Tham gia</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="club-logo me-3">GD</div>
                                <div>
                                    <h5 class="card-title mb-1">CLB Giải trí & Du lịch</h5>
                                    <small class="text-muted">
                                        <i class="fas fa-user-friends"></i> 120 thành viên
                                    </small>
                                </div>
                            </div>
                            <p class="card-text">Tham gia các hoạt động giải trí và du lịch cùng bạn bè.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Đang hoạt động</span>
                                <a href="#" class="btn btn-primary btn-sm">Tham gia</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="club-logo me-3">TD</div>
                                <div>
                                    <h5 class="card-title mb-1">CLB Thể dục thể thao</h5>
                                    <small class="text-muted">
                                        <i class="fas fa-user-friends"></i> 200 thành viên
                                    </small>
                                </div>
                            </div>
                            <p class="card-text">Rèn luyện sức khỏe và tham gia các hoạt động thể thao.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Đang hoạt động</span>
                                <a href="#" class="btn btn-primary btn-sm">Tham gia</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="club-logo me-3">NT</div>
                                <div>
                                    <h5 class="card-title mb-1">CLB Nghệ thuật</h5>
                                    <small class="text-muted">
                                        <i class="fas fa-user-friends"></i> 80 thành viên
                                    </small>
                                </div>
                            </div>
                            <p class="card-text">Thể hiện tài năng nghệ thuật và sáng tạo của bạn.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Đang hoạt động</span>
                                <a href="#" class="btn btn-primary btn-sm">Tham gia</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="sidebar">
            <h5 class="sidebar-title">
                <i class="fas fa-chart-bar"></i> Thống kê
            </h5>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="fw-bold"><?php echo e($user->clubs->count()); ?></div>
                    <small class="text-muted">CLB đã tham gia</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div>
                    <div class="fw-bold">0</div>
                    <small class="text-muted">Sự kiện đã tham gia</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div>
                    <div class="fw-bold">0</div>
                    <small class="text-muted">Giải thưởng</small>
                </div>
            </div>
        </div>

        <div class="sidebar mt-4">
            <h5 class="sidebar-title">
                <i class="fas fa-bell"></i> Thông báo
            </h5>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <div class="fw-bold">Chào mừng!</div>
                    <small class="text-muted">Bạn đã tham gia UniClubs</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div class="fw-bold">Sự kiện mới</div>
                    <small class="text-muted">Workshop "Lập trình Web"</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .club-logo {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: #14b8a6;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
    }
    
    .text-teal {
        color: #14b8a6 !important;
    }
    
    .bg-teal {
        background-color: #14b8a6 !important;
    }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.student', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\DATN_Uniclubs\resources\views/student/clubs/index.blade.php ENDPATH**/ ?>