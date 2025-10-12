<?php $__env->startSection('title', 'Quản lý người dùng - CLB Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-header">
    <h1>Quản lý người dùng</h1>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('admin.users')); ?>" class="row g-3">
            <div class="col-md-4">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm theo tên, email, số điện thoại..."
                       value="<?php echo e(request('search')); ?>">
            </div>
            <div class="col-md-3">
                <select name="is_admin" class="form-select">
                    <option value="">Tất cả quyền</option>
                    <option value="1" <?php echo e(request('is_admin') == '1' ? 'selected' : ''); ?>>Admin</option>
                    <option value="0" <?php echo e(request('is_admin') == '0' ? 'selected' : ''); ?>>User thường</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-3 text-end">
                <a href="<?php echo e(route('admin.users')); ?>" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách người dùng -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh đại diện</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Mã sinh viên</th>
                        <th>Số điện thoại</th>
                        <th>Địa chỉ</th>
                        <th>Role</th>
                        <th>Vai trò CLB</th>
                        <th>Quyền Admin</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($user->id); ?></td>
                            <td>
                                <?php if($user->avatar && file_exists(public_path($user->avatar))): ?>
                                    <img src="<?php echo e(asset($user->avatar)); ?>" 
                                         alt="<?php echo e($user->name); ?>" 
                                         class="rounded-circle" 
                                         width="40" 
                                         height="40">
                                <?php else: ?>
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($user->name); ?></td>
                            <td><?php echo e($user->email); ?></td>
                            <td>
                                <?php if($user->student_id): ?>
                                    <span class="badge bg-success"><?php echo e($user->student_id); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($user->phone ?? 'N/A'); ?></td>
                            <td><?php echo e(Str::limit($user->address ?? 'N/A', 30)); ?></td>
                            <td>
                                <span class="badge bg-<?php echo e($user->role === 'admin' ? 'danger' : ($user->role === 'club_manager' ? 'warning' : 'success')); ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $user->role))); ?>

                                </span>
                            </td>
                            <td>
                                <?php
                                    $clubs = \App\Models\Club::all();
                                    $clubRoles = [];
                                    foreach($clubs as $club) {
                                        $position = $user->getPositionInClub($club->id);
                                        if($position) {
                                            $clubRoles[] = $club->name . ': ' . $position;
                                        }
                                    }
                                ?>
                                <?php if(count($clubRoles) > 0): ?>
                                    <?php $__currentLoopData = $clubRoles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="badge bg-info me-1"><?php echo e($role); ?></span><br>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark">Không có</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo e($user->is_admin ? 'danger' : 'secondary'); ?>">
                                    <?php echo e($user->is_admin ? 'Admin' : 'User'); ?>

                                </span>
                            </td>
                            <td><?php echo e($user->created_at->format('d/m/Y')); ?></td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editRoleModal<?php echo e($user->id); ?>">
                                    <i class="fas fa-edit"></i> Chỉnh sửa
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                Không tìm thấy người dùng nào
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

<!-- Edit Role Modals -->
<?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="editRoleModal<?php echo e($user->id); ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa quyền - <?php echo e($user->name); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo e(route('admin.users.status', $user->id)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="role<?php echo e($user->id); ?>" class="form-label">Vai trò hệ thống:</label>
                        <select name="role" id="role<?php echo e($user->id); ?>" class="form-select" onchange="updateAdminStatus(this, 'adminStatus<?php echo e($user->id); ?>')">
                            <option value="user" <?php echo e($user->role === 'user' ? 'selected' : ''); ?>>User thường</option>
                            <option value="club_manager" <?php echo e($user->role === 'club_manager' ? 'selected' : ''); ?>>Quản lý CLB</option>
                            <option value="admin" <?php echo e($user->role === 'admin' ? 'selected' : ''); ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adminStatus<?php echo e($user->id); ?>" class="form-label">Quyền Admin:</label>
                        <select name="is_admin" id="adminStatus<?php echo e($user->id); ?>" class="form-select">
                            <option value="0" <?php echo e(!$user->is_admin ? 'selected' : ''); ?>>Không</option>
                            <option value="1" <?php echo e($user->is_admin ? 'selected' : ''); ?>>Có</option>
                        </select>
                    </div>

                    <!-- Club Roles Section -->
                    <div class="mb-3">
                        <label class="form-label">Vai trò trong CLB:</label>
                        <div class="row">
                            <?php
                                $clubs = \App\Models\Club::all();
                            ?>
                            <?php $__currentLoopData = $clubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $club): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $position = $user->getPositionInClub($club->id);
                                ?>
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2 fw-bold"><?php echo e($club->name); ?>:</span>
                                        <?php if($position): ?>
                                            <span class="badge bg-success"><?php echo e(ucfirst($position)); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark">Không phải thành viên</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <small class="text-muted">Để thay đổi vai trò trong CLB, vào trang "Phân Quyền Chi Tiết"</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="<?php echo e(route('admin.permissions.detailed')); ?>" class="btn btn-outline-info me-auto">
                        <i class="fas fa-cogs"></i> Phân quyền chi tiết
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Đảm bảo tất cả functions được định nghĩa khi DOM ready
    window.updateAdminStatus = function(roleSelect, adminSelectId) {
        const adminSelect = document.getElementById(adminSelectId);
        
        if (adminSelect && roleSelect.value === 'admin') {
            adminSelect.value = '1';
            adminSelect.disabled = true;
        } else if (adminSelect) {
            adminSelect.disabled = false;
        }
    };
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\DATN_Uniclubs\resources\views/admin/users/index.blade.php ENDPATH**/ ?>