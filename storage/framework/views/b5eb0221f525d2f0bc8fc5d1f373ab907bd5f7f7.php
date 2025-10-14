

<?php $__env->startSection('title', 'Quản lý Bài viết - CLB Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-1">📝 Quản lý Bài viết</h1>
        <p class="text-muted mb-0">Quản lý và theo dõi tất cả bài viết trong hệ thống</p>
    </div>
    <div class="header-actions">
        <a href="<?php echo e(route('admin.posts.create')); ?>" class="btn btn-success btn-lg">
            <i class="fas fa-plus-circle"></i> Tạo bài viết mới
        </a>
    </div>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fas fa-filter"></i> Bộ lọc và tìm kiếm</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('admin.posts')); ?>" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">Tìm kiếm</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Tìm kiếm bài viết..."
                           value="<?php echo e(request('search')); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Câu lạc bộ</label>
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
                <label class="form-label small text-muted">Loại bài viết</label>
                <select name="type" class="form-select">
                    <option value="">Tất cả loại</option>
                    <option value="post" <?php echo e(request('type') == 'post' ? 'selected' : ''); ?>>📄 Bài viết</option>
                    <option value="announcement" <?php echo e(request('type') == 'announcement' ? 'selected' : ''); ?>>📢 Thông báo</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="published" <?php echo e(request('status') == 'published' ? 'selected' : ''); ?>>✅ Đã xuất bản</option>
                    <option value="hidden" <?php echo e(request('status') == 'hidden' ? 'selected' : ''); ?>>👁️ Ẩn</option>
                    <option value="deleted" <?php echo e(request('status') == 'deleted' ? 'selected' : ''); ?>>🗑️ Đã xóa</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <a href="<?php echo e(route('admin.posts')); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách bài viết -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-list"></i> Danh sách bài viết</h6>
            <span class="badge bg-primary"><?php echo e($posts->total()); ?> bài viết</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">ID</th>
                        <th class="border-0">📝 Tiêu đề</th>
                        <th class="border-0">🏷️ Loại</th>
                        <th class="border-0">🏢 Câu lạc bộ</th>
                        <th class="border-0">👤 Tác giả</th>
                        <th class="border-0">📄 Nội dung</th>
                        <th class="border-0">📊 Trạng thái</th>
                        <th class="border-0">📅 Ngày tạo</th>
                        <th class="border-0">⚙️ Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="align-middle">
                            <td>
                                <span class="badge bg-light text-dark">#<?php echo e($post->id); ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if($post->featured_image_url): ?>
                                        <img src="<?php echo e($post->featured_image_url); ?>" 
                                             alt="<?php echo e($post->title); ?>" 
                                             class="rounded me-3" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong class="text-dark mb-1"><?php echo e(Str::limit($post->title, 30)); ?></strong>
                                        <br><small class="text-muted"><?php echo e($post->slug); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo e($post->type === 'announcement' ? 'danger' : 'primary'); ?> rounded-pill">
                                    <?php echo e($post->type === 'announcement' ? '📢 Thông báo' : '📄 Bài viết'); ?>

                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-building text-muted me-2"></i>
                                    <span><?php echo e(Str::limit($post->club->name ?? 'Không xác định', 20)); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user text-muted me-2"></i>
                                    <span><?php echo e(Str::limit($post->user->name ?? 'Không xác định', 15)); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted"><?php echo e(Str::limit(strip_tags($post->content), 60)); ?></span>
                            </td>
                            <td>
                                <?php
                                    $statusConfig = [
                                        'published' => ['color' => 'success', 'icon' => '✅', 'label' => 'Đã xuất bản'],
                                        'hidden' => ['color' => 'warning', 'icon' => '👁️', 'label' => 'Ẩn'],
                                        'deleted' => ['color' => 'danger', 'icon' => '🗑️', 'label' => 'Đã xóa']
                                    ];
                                    $config = $statusConfig[$post->status] ?? ['color' => 'secondary', 'icon' => '❓', 'label' => ucfirst($post->status)];
                                ?>
                                <span class="badge bg-<?php echo e($config['color']); ?> rounded-pill">
                                    <?php echo e($config['icon']); ?> <?php echo e($config['label']); ?>

                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark"><?php echo e($post->created_at->format('d/m/Y')); ?></span>
                                    <small class="text-muted"><?php echo e($post->created_at->format('H:i')); ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group-vertical btn-group-sm" role="group">
                                    <div class="btn-group btn-group-sm mb-1" role="group">
                                        <a href="<?php echo e(route('admin.posts.show', $post->id)); ?>" class="btn btn-outline-info btn-sm" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('admin.posts.edit', $post->id)); ?>" class="btn btn-outline-warning btn-sm" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                    
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if($post->status === 'published'): ?>
                                            <form method="POST" action="<?php echo e(route('admin.posts.status', $post->id)); ?>" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <input type="hidden" name="status" value="hidden">
                                                <button type="submit" class="btn btn-outline-warning btn-sm" title="Ẩn bài viết">
                                                    <i class="fas fa-eye-slash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if($post->status === 'hidden'): ?>
                                            <form method="POST" action="<?php echo e(route('admin.posts.status', $post->id)); ?>" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <input type="hidden" name="status" value="published">
                                                <button type="submit" class="btn btn-outline-success btn-sm" title="Hiện bài viết">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if($post->status !== 'deleted'): ?>
                                            <form method="POST" action="<?php echo e(route('admin.posts.destroy', $post->id)); ?>" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa bài viết" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Không tìm thấy bài viết nào</h5>
                                    <p class="text-muted mb-3">Hãy thử thay đổi bộ lọc hoặc tạo bài viết mới</p>
                                    <a href="<?php echo e(route('admin.posts.create')); ?>" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Tạo bài viết đầu tiên
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        <?php if($posts->hasPages()): ?>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Hiển thị <?php echo e($posts->firstItem() ?? 0); ?> - <?php echo e($posts->lastItem() ?? 0); ?> 
                        trong tổng số <?php echo e($posts->total()); ?> bài viết
                    </div>
                    <div>
                        <?php echo e($posts->appends(request()->query())->links()); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.empty-state {
    padding: 2rem;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table td {
    vertical-align: middle;
    border-top: 1px solid #f1f3f4;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.btn-group-vertical .btn {
    border-radius: 0.375rem !important;
}

.btn-group-vertical .btn:first-child {
    border-top-left-radius: 0.375rem !important;
    border-top-right-radius: 0.375rem !important;
}

.btn-group-vertical .btn:last-child {
    border-bottom-left-radius: 0.375rem !important;
    border-bottom-right-radius: 0.375rem !important;
}

.badge.rounded-pill {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\xampp\htdocs\DATN_Uniclubs\resources\views/admin/posts/index.blade.php ENDPATH**/ ?>