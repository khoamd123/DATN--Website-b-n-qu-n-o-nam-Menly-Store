<?php $__env->startSection('title', 'Phân Quyền - CLB Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-header">
    <h1>Phân Quyền</h1>
</div>

<!-- Thống kê quyền -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #dc3545;">
                <i class="fas fa-user-shield"></i>
            </div>
            <p class="stats-number"><?php echo e($users->where('is_admin', true)->count()); ?></p>
            <p class="stats-label">Admin</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #28a745;">
                <i class="fas fa-users"></i>
            </div>
            <p class="stats-number"><?php echo e($users->where('is_admin', false)->count()); ?></p>
            <p class="stats-label">User thường</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #007bff;">
                <i class="fas fa-users"></i>
            </div>
            <p class="stats-number"><?php echo e($clubs->count()); ?></p>
            <p class="stats-label">Câu lạc bộ</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #ffc107;">
                <i class="fas fa-key"></i>
            </div>
            <p class="stats-number"><?php echo e($permissions->count()); ?></p>
            <p class="stats-label">Loại quyền</p>
        </div>
    </div>
</div>

<!-- Danh sách người dùng và phân quyền -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Người dùng</th>
                        <th>Email</th>
                        <th>Quyền Admin</th>
                        <th>Câu lạc bộ sở hữu</th>
                        <th>Câu lạc bộ tham gia</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($user->id); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if($user->avatar && file_exists(public_path($user->avatar))): ?>
                                        <img src="<?php echo e(asset($user->avatar)); ?>" 
                                             alt="<?php echo e($user->name); ?>" 
                                             class="rounded-circle me-2" 
                                             width="30" 
                                             height="30">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-2 text-white" 
                                             style="width: 30px; height: 30px; font-size: 12px;">
                                            <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo e($user->name); ?></strong>
                                        <?php if($user->is_admin): ?>
                                            <i class="fas fa-crown text-warning ms-1" title="Admin"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo e($user->email); ?></td>
                            <td>
                                <span class="badge bg-<?php echo e($user->is_admin ? 'danger' : 'success'); ?>">
                                    <?php echo e($user->is_admin ? 'Admin' : 'User'); ?>

                                </span>
                            </td>
                            <td>
                                <?php if($user->ownedClubs->count() > 0): ?>
                                    <?php $__currentLoopData = $user->ownedClubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $club): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="badge bg-primary me-1"><?php echo e($club->name); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <span class="text-muted">Không có</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($user->clubs->count() > 0): ?>
                                    <?php echo e($user->clubs->count()); ?> CLB
                                <?php else: ?>
                                    <span class="text-muted">Không có</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($user->created_at->format('d/m/Y')); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editPermissionsModal<?php echo e($user->id); ?>">
                                        <i class="fas fa-edit"></i> Sửa quyền
                                    </button>
                                </div>
                                
                                <!-- Modal sửa quyền -->
                                <div class="modal fade" id="editPermissionsModal<?php echo e($user->id); ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Sửa quyền: <?php echo e($user->name); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="<?php echo e(route('admin.permissions.user', $user->id)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Quyền Admin</label>
                                                        <select name="is_admin" class="form-select">
                                                            <option value="0" <?php echo e(!$user->is_admin ? 'selected' : ''); ?>>User thường</option>
                                                            <option value="1" <?php echo e($user->is_admin ? 'selected' : ''); ?>>Admin</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Quyền cụ thể</label>
                                                        <div class="row">
                                                            <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <div class="col-md-6">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" 
                                                                               name="permissions[]" value="<?php echo e($permission->id); ?>" 
                                                                               id="permission<?php echo e($permission->id); ?>_<?php echo e($user->id); ?>">
                                                                        <label class="form-check-label" for="permission<?php echo e($permission->id); ?>_<?php echo e($user->id); ?>">
                                                                            <?php echo e($permission->name); ?>

                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Không có người dùng nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        <?php if($users->hasPages()): ?>
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($users->appends(request()->query())->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Danh sách quyền hệ thống -->
<div class="card mt-4">
    <div class="card-header">
        <h5>Danh sách quyền hệ thống</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo e($permission->name); ?></h6>
                            <p class="card-text text-muted"><?php echo e($permission->description ?? 'Không có mô tả'); ?></p>
                            <small class="text-muted">ID: <?php echo e($permission->id); ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\DATN_Uniclubs\resources\views/admin/permissions/index.blade.php ENDPATH**/ ?>