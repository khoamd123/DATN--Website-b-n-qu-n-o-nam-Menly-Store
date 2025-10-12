<?php $__env->startSection('title', 'Quản Lý Phân Quyền Chi Tiết - CLB Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-header">
    <h1>🔐 Quản Lý Phân Quyền Chi Tiết</h1>
    <p class="text-muted">Quản lý quyền hạn chi tiết cho từng người dùng trong từng CLB</p>
</div>

<!-- Thống kê tổng quan -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo e($users->count()); ?></h4>
                        <p class="mb-0">Tổng người dùng</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo e($clubs->count()); ?></h4>
                        <p class="mb-0">Câu lạc bộ</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-building fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo e($permissions->count()); ?></h4>
                        <p class="mb-0">Loại quyền</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-key fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo e($users->where('is_admin', true)->count()); ?></h4>
                        <p class="mb-0">Admin</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-shield fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Danh sách quyền có sẵn -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📋 Danh sách quyền có sẵn</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <?php switch($permission->name):
                                    case ('manage_club'): ?>
                                        <i class="fas fa-cogs text-primary fa-lg"></i>
                                        <?php break; ?>
                                    <?php case ('manage_members'): ?>
                                        <i class="fas fa-users-cog text-success fa-lg"></i>
                                        <?php break; ?>
                                    <?php case ('create_event'): ?>
                                        <i class="fas fa-calendar-plus text-warning fa-lg"></i>
                                        <?php break; ?>
                                    <?php case ('post_announcement'): ?>
                                        <i class="fas fa-bullhorn text-info fa-lg"></i>
                                        <?php break; ?>
                                    <?php case ('evaluate_member'): ?>
                                        <i class="fas fa-star text-warning fa-lg"></i>
                                        <?php break; ?>
                                    <?php case ('manage_department'): ?>
                                        <i class="fas fa-sitemap text-secondary fa-lg"></i>
                                        <?php break; ?>
                                    <?php case ('manage_documents'): ?>
                                        <i class="fas fa-file-alt text-dark fa-lg"></i>
                                        <?php break; ?>
                                    <?php case ('view_reports'): ?>
                                        <i class="fas fa-chart-bar text-success fa-lg"></i>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <i class="fas fa-key text-primary fa-lg"></i>
                                <?php endswitch; ?>
                            </div>
                            <div>
                                <strong><?php echo e($permission->name); ?></strong>
                                <br><small class="text-muted"><?php echo e($permission->description); ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bảng quản lý permissions -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">👥 Phân Quyền Chi Tiết</h5>
        <button class="btn btn-primary" onclick="refreshPermissions()">
            <i class="fas fa-sync-alt"></i> Làm mới
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Người dùng</th>
                        <th>CLB</th>
                        <th>Vị trí</th>
                        <th>Quyền hiện tại</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $clubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $club): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $position = $user->getPositionInClub($club->id);
                                $userPermissions = $user->getClubPermissions($club->id);
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar-fixed me-2">
                                            <?php echo e(substr($user->name, 0, 1)); ?>

                                        </div>
                                        <div>
                                            <strong><?php echo e($user->name); ?></strong>
                                            <br><small class="text-muted"><?php echo e($user->email); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo e($club->name); ?></strong>
                                    <br><small class="text-muted"><?php echo e($club->description); ?></small>
                                </td>
                                <td>
                                    <?php if($user->isAdmin()): ?>
                                        <span class="badge bg-danger">Admin</span>
                                        <br><small class="text-muted">Vị trí CLB: 
                                            <?php if($position): ?>
                                                <?php switch($position):
                                                    case ('leader'): ?> Trưởng CLB <?php break; ?>
                                                    <?php case ('vice_president'): ?> Phó CLB <?php break; ?>
                                                    <?php case ('officer'): ?> Cán sự <?php break; ?>
                                                    <?php case ('member'): ?> Thành viên <?php break; ?>
                                                    <?php default: ?> <?php echo e($position); ?> <?php break; ?>
                                                <?php endswitch; ?>
                                            <?php else: ?>
                                                Không phải thành viên
                                            <?php endif; ?>
                                        </small>
                                    <?php elseif($position): ?>
                                        <?php switch($position):
                                            case ('leader'): ?>
                                                <span class="badge bg-danger">Trưởng CLB</span>
                                                <?php break; ?>
                                            <?php case ('vice_president'): ?>
                                                <span class="badge bg-warning">Phó CLB</span>
                                                <?php break; ?>
                                            <?php case ('officer'): ?>
                                                <span class="badge bg-info">Cán sự</span>
                                                <?php break; ?>
                                            <?php case ('member'): ?>
                                                <span class="badge bg-success">Thành viên</span>
                                                <?php break; ?>
                                            <?php default: ?>
                                                <span class="badge bg-secondary"><?php echo e($position); ?></span>
                                        <?php endswitch; ?>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">Không phải thành viên</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($user->isAdmin()): ?>
                                        <span class="badge bg-danger">Admin - Tất cả quyền</span>
                                    <?php elseif($userPermissions): ?>
                                        <?php $__currentLoopData = $userPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="badge bg-primary me-1"><?php echo e($perm); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Không có quyền</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!$user->isAdmin()): ?>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="editPermissions(<?php echo e($user->id); ?>, <?php echo e($club->id); ?>, '<?php echo e($user->name); ?>', '<?php echo e($club->name); ?>')">
                                            <i class="fas fa-edit"></i> Sửa quyền
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">Admin - Không thể sửa</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal chỉnh sửa quyền -->
<div class="modal fade" id="editPermissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa quyền</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Người dùng:</strong> <span id="modalUserName"></span><br>
                    <strong>CLB:</strong> <span id="modalClubName"></span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Chọn quyền:</label>
                    <div class="row" id="permissionsList">
                        <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input permission-checkbox" 
                                       type="checkbox" 
                                       value="<?php echo e($permission->id); ?>" 
                                       id="perm_<?php echo e($permission->id); ?>"
                                       name="permissions[]">
                                <label class="form-check-label" for="perm_<?php echo e($permission->id); ?>">
                                    <strong><?php echo e($permission->name); ?></strong><br>
                                    <small class="text-muted"><?php echo e($permission->description); ?></small>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="savePermissions()">Lưu quyền</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentUserId = null;
let currentClubId = null;

function editPermissions(userId, clubId, userName, clubName) {
    currentUserId = userId;
    currentClubId = clubId;
    
    document.getElementById('modalUserName').textContent = userName;
    document.getElementById('modalClubName').textContent = clubName;
    
    // Debug: Log available permissions
    console.log('Available permissions:', document.querySelectorAll('.permission-checkbox').length);
    
    // Reset checkboxes
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    
    // Load current permissions
    fetch(`<?php echo e(url('/admin/permissions/user-permissions')); ?>?user_id=${userId}&club_id=${clubId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.permissions) {
                // Check permissions based on current position
                data.permissions.forEach(perm => {
                    const permissionId = getPermissionIdByName(perm);
                    if (permissionId) {
                        const checkbox = document.querySelector(`input[value="${permissionId}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error loading permissions:', error);
        });
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editPermissionsModal'));
    modal.show();
}

function savePermissions() {
    const selectedPermissions = Array.from(document.querySelectorAll('.permission-checkbox:checked'))
        .map(cb => cb.value);
    
    fetch('<?php echo e(url("/admin/permissions/update")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            user_id: currentUserId,
            club_id: currentClubId,
            permissions: selectedPermissions
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đã cập nhật quyền thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        alert('Lỗi: ' + error.message);
    });
}

function refreshPermissions() {
    location.reload();
}

function getPermissionIdByName(permissionName) {
    const permissionMap = {
        <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        '<?php echo e($permission->name); ?>': <?php echo e($permission->id); ?>,
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    };
    return permissionMap[permissionName] || null;
}

function getPermissionNameById(permissionId) {
    const permissionMap = {
        <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo e($permission->id); ?>: '<?php echo e($permission->name); ?>',
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    };
    return permissionMap[permissionId] || null;
}
</script>

<style>
.user-avatar-fixed {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\DATN_Uniclubs\resources\views/admin/permissions/detailed.blade.php ENDPATH**/ ?>