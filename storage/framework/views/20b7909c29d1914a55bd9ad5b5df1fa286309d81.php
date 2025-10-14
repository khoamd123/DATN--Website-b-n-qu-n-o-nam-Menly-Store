

<?php $__env->startSection('title', 'Quản lý Bình luận - CLB Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-header">
    <h1>Quản lý Bình luận</h1>
</div>

<!-- Thống kê bình luận -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #007bff;">
                <i class="fas fa-comments"></i>
            </div>
            <p class="stats-number"><?php echo e($allComments->count()); ?></p>
            <p class="stats-label">Tổng bình luận</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #28a745;">
                <i class="fas fa-newspaper"></i>
            </div>
            <p class="stats-number"><?php echo e($allComments->where('commentable_type', 'App\Models\Post')->count()); ?></p>
            <p class="stats-label">Bình luận bài viết</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #ffc107;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <p class="stats-number"><?php echo e($allComments->where('commentable_type', 'App\Models\Event')->count()); ?></p>
            <p class="stats-label">Bình luận sự kiện</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #dc3545;">
                <i class="fas fa-clock"></i>
            </div>
            <p class="stats-number"><?php echo e($allComments->where('created_at', '>=', now()->subWeek())->count()); ?></p>
            <p class="stats-label">Tuần này</p>
        </div>
    </div>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('admin.comments')); ?>" class="row g-3">
            <div class="col-md-4">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm nội dung bình luận..."
                       value="<?php echo e(request('search')); ?>">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">Tất cả loại</option>
                    <option value="post" <?php echo e(request('type') == 'post' ? 'selected' : ''); ?>>Bài viết</option>
                    <option value="event" <?php echo e(request('type') == 'event' ? 'selected' : ''); ?>>Sự kiện</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-4 text-end">
                <a href="<?php echo e(route('admin.comments')); ?>" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách bình luận -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Người bình luận</th>
                        <th>Nội dung</th>
                        <th>Loại</th>
                        <th>Liên kết</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $allComments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $commentable = $comment->post ?? $comment->event ?? null;
                            $commentableType = $comment->post ? 'Bài viết' : 'Sự kiện';
                        ?>
                        <tr>
                            <td><?php echo e($comment->id); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo e($comment->user->avatar ?? '/images/avatar/avatar.png'); ?>" 
                                         alt="<?php echo e($comment->user->name); ?>" 
                                         class="rounded-circle me-2" 
                                         width="30" 
                                         height="30"
                                         onerror="this.src='/images/avatar/avatar.png'">
                                    <div>
                                        <strong><?php echo e($comment->user->name ?? 'Không xác định'); ?></strong>
                                        <br><small class="text-muted"><?php echo e($comment->user->email ?? ''); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="max-width: 300px;">
                                    <?php echo e(Str::limit($comment->content, 100)); ?>

                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo e($comment->post ? 'primary' : 'warning'); ?>">
                                    <?php echo e($commentableType); ?>

                                </span>
                            </td>
                            <td>
                                <?php if($commentable): ?>
                                    <strong><?php echo e($commentable->title ?? 'Không có tiêu đề'); ?></strong>
                                    <br><small class="text-muted"><?php echo e($commentable->club->name ?? 'Không xác định CLB'); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Không tìm thấy</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($comment->created_at->format('d/m/Y H:i')); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-info" onclick="viewComment(<?php echo e($comment->id); ?>)">
                                        <i class="fas fa-eye"></i> Xem
                                    </button>
                                    <form method="POST" action="<?php echo e(route('admin.comments.delete', [$comment->post ? 'post' : 'event', $comment->id])); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa bình luận này?')">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Không tìm thấy bình luận nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang (nếu cần) -->
        <?php if($allComments->count() > 20): ?>
            <div class="d-flex justify-content-center mt-4">
                <!-- Pagination sẽ được thêm sau -->
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function viewComment(id) {
    // Logic xem chi tiết bình luận
    alert('Xem bình luận ID: ' + id);
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\xampp\htdocs\DATN_Uniclubs\resources\views/admin/comments/index.blade.php ENDPATH**/ ?>