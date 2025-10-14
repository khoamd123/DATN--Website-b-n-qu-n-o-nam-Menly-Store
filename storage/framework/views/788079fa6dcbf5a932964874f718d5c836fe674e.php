

<?php $__env->startSection('title', 'Quản lý câu lạc bộ - CLB Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-header">
    <h1>Quản lý câu lạc bộ</h1>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('admin.clubs')); ?>" class="row g-3">
            <div class="col-md-4">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm theo tên, mô tả..."
                       value="<?php echo e(request('search')); ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Chờ duyệt</option>
                    <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Đã duyệt</option>
                    <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Từ chối</option>
                    <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Đang hoạt động</option>
                    <option value="inactive" <?php echo e(request('status') == 'inactive' ? 'selected' : ''); ?>>Không hoạt động</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-3 text-end">
                <a href="<?php echo e(route('admin.clubs')); ?>" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách câu lạc bộ -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Logo</th>
                        <th>Tên CLB</th>
                        <th>Lĩnh vực</th>
                        <th>Chủ sở hữu</th>
                        <th>Mô tả</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $clubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $club): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($club->id); ?></td>
                            <td>
                                <img src="<?php echo e($club->logo ?? '/images/logos/logo_club.png'); ?>" 
                                     alt="<?php echo e($club->name); ?>" 
                                     class="rounded" 
                                     width="40" 
                                     height="40"
                                     onerror="this.src='/images/logos/logo_club.png'">
                            </td>
                            <td>
                                <strong><?php echo e($club->name); ?></strong>
                                <br><small class="text-muted"><?php echo e($club->slug); ?></small>
                            </td>
                            <td><?php echo e($club->field->name ?? 'Không xác định'); ?></td>
                            <td><?php echo e($club->owner->name ?? 'Không xác định'); ?></td>
                            <td><?php echo e(Str::limit($club->description, 50)); ?></td>
                            <td>
                                <?php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'rejected' => 'danger',
                                        'active' => 'success',
                                        'inactive' => 'secondary'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'rejected' => 'Từ chối',
                                        'active' => 'Đang hoạt động',
                                        'inactive' => 'Không hoạt động'
                                    ];
                                ?>
                                <span class="badge bg-<?php echo e($statusColors[$club->status] ?? 'secondary'); ?>">
                                    <?php echo e($statusLabels[$club->status] ?? ucfirst($club->status)); ?>

                                </span>
                            </td>
                            <td><?php echo e($club->created_at->format('d/m/Y')); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <form method="POST" action="<?php echo e(route('admin.clubs.status', $club->id)); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <?php if($club->status === 'pending'): ?>
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Duyệt
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if($club->status === 'approved'): ?>
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-play"></i> Kích hoạt
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if(in_array($club->status, ['active', 'approved'])): ?>
                                            <input type="hidden" name="status" value="inactive">
                                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Bạn có chắc chắn muốn tạm dừng câu lạc bộ này?')">
                                                <i class="fas fa-pause"></i> Tạm dừng
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if($club->status === 'pending'): ?>
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn từ chối câu lạc bộ này?')">
                                                <i class="fas fa-times"></i> Từ chối
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if($club->status === 'inactive'): ?>
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-play"></i> Kích hoạt lại
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Không tìm thấy câu lạc bộ nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        <?php if($clubs->hasPages()): ?>
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($clubs->appends(request()->query())->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\DATN_Uniclubs\resources\views/admin/clubs/index.blade.php ENDPATH**/ ?>