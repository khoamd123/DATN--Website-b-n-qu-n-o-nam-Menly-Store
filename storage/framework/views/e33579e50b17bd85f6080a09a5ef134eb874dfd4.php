

<?php $__env->startSection('title', 'Kế hoạch - CLB Admin'); ?>

<?php $__env->startSection('content'); ?>
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
            <p class="stats-number"><?php echo e($events->total()); ?></p>
            <p class="stats-label">Tổng sự kiện</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #28a745;">
                <i class="fas fa-play"></i>
            </div>
            <p class="stats-number"><?php echo e($events->where('status', 'active')->count()); ?></p>
            <p class="stats-label">Đang hoạt động</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #ffc107;">
                <i class="fas fa-clock"></i>
            </div>
            <p class="stats-number"><?php echo e($events->where('status', 'pending')->count()); ?></p>
            <p class="stats-label">Chờ duyệt</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #dc3545;">
                <i class="fas fa-ban"></i>
            </div>
            <p class="stats-number"><?php echo e($events->where('status', 'canceled')->count()); ?></p>
            <p class="stats-label">Đã hủy</p>
        </div>
    </div>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('admin.plans-schedule')); ?>" class="row g-3">
            <div class="col-md-3">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm sự kiện..."
                       value="<?php echo e(request('search')); ?>">
            </div>
            <div class="col-md-2">
                <select name="club_id" class="form-select">
                    <option value="">Tất cả CLB</option>
                    <?php $__currentLoopData = $clubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $club): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($club->id); ?>" <?php echo e(request('club_id') == $club->id ? 'selected' : ''); ?>>
                            <?php echo e($club->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Chờ duyệt</option>
                    <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Đã duyệt</option>
                    <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Đang hoạt động</option>
                    <option value="canceled" <?php echo e(request('status') == 'canceled' ? 'selected' : ''); ?>>Đã hủy</option>
                    <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Hoàn thành</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-3 text-end">
                <a href="<?php echo e(route('admin.plans-schedule')); ?>" class="btn btn-secondary">
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
                    <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($event->id); ?></td>
                            <td>
                                <strong><?php echo e($event->title); ?></strong>
                                <br><small class="text-muted"><?php echo e(Str::limit($event->description, 50)); ?></small>
                            </td>
                            <td><?php echo e($event->club->name ?? 'Không xác định'); ?></td>
                            <td>
                                <strong>Bắt đầu:</strong> <?php echo e(\Carbon\Carbon::parse($event->start_time)->format('d/m/Y H:i')); ?>

                                <br><strong>Kết thúc:</strong> <?php echo e(\Carbon\Carbon::parse($event->end_time)->format('d/m/Y H:i')); ?>

                            </td>
                            <td>
                                <span class="badge bg-<?php echo e($event->mode === 'public' ? 'success' : 'warning'); ?>">
                                    <?php echo e($event->mode === 'public' ? 'Công khai' : 'Riêng tư'); ?>

                                </span>
                            </td>
                            <td>
                                <?php
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
                                ?>
                                <span class="badge bg-<?php echo e($statusColors[$event->status] ?? 'secondary'); ?>">
                                    <?php echo e($statusLabels[$event->status] ?? ucfirst($event->status)); ?>

                                </span>
                            </td>
                            <td><?php echo e($event->creator->name ?? 'Không xác định'); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <?php if($event->status === 'pending'): ?>
                                        <form method="POST" action="<?php echo e(route('admin.clubs.status', $event->id)); ?>" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Duyệt
                                            </button>
                                        </form>
                                        
                                        <form method="POST" action="<?php echo e(route('admin.clubs.status', $event->id)); ?>" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <input type="hidden" name="status" value="canceled">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn hủy sự kiện này?')">
                                                <i class="fas fa-times"></i> Hủy
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if($event->status === 'approved'): ?>
                                        <form method="POST" action="<?php echo e(route('admin.clubs.status', $event->id)); ?>" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-play"></i> Kích hoạt
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if($event->status === 'active'): ?>
                                        <form method="POST" action="<?php echo e(route('admin.clubs.status', $event->id)); ?>" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Bạn có chắc chắn muốn đánh dấu sự kiện này là hoàn thành?')">
                                                <i class="fas fa-check-double"></i> Hoàn thành
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-sm btn-info" onclick="viewEvent(<?php echo e($event->id); ?>)">
                                        <i class="fas fa-eye"></i> Xem
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Không tìm thấy sự kiện nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        <?php if($events->hasPages()): ?>
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($events->appends(request()->query())->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\xampp\htdocs\DATN_Uniclubs\resources\views/admin/plans-schedule/index.blade.php ENDPATH**/ ?>