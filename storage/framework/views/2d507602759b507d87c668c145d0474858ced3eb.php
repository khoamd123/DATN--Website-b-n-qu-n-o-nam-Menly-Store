

<?php $__env->startSection('title', 'Tài liệu học tập - CLB Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-header">
    <h1>Tài liệu học tập</h1>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('admin.learning-materials')); ?>" class="row g-3">
            <div class="col-md-3">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm tài liệu..."
                       value="<?php echo e(request('search')); ?>">
            </div>
            <div class="col-md-3">
                <select name="club_id" class="form-select">
                    <option value="">Tất cả câu lạc bộ</option>
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
                    <option value="published" <?php echo e(request('status') == 'published' ? 'selected' : ''); ?>>Đã xuất bản</option>
                    <option value="hidden" <?php echo e(request('status') == 'hidden' ? 'selected' : ''); ?>>Ẩn</option>
                    <option value="deleted" <?php echo e(request('status') == 'deleted' ? 'selected' : ''); ?>>Đã xóa</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-2 text-end">
                <a href="<?php echo e(route('admin.learning-materials')); ?>" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách tài liệu -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề</th>
                        <th>Câu lạc bộ</th>
                        <th>Người tạo</th>
                        <th>Nội dung</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($document->id); ?></td>
                            <td>
                                <strong><?php echo e($document->title); ?></strong>
                                <br><small class="text-muted"><?php echo e($document->slug); ?></small>
                            </td>
                            <td><?php echo e($document->club->name ?? 'Không xác định'); ?></td>
                            <td><?php echo e($document->user->name ?? 'Không xác định'); ?></td>
                            <td><?php echo e(Str::limit($document->content, 50)); ?></td>
                            <td>
                                <?php
                                    $statusColors = [
                                        'published' => 'success',
                                        'hidden' => 'warning',
                                        'deleted' => 'danger'
                                    ];
                                    $statusLabels = [
                                        'published' => 'Đã xuất bản',
                                        'hidden' => 'Ẩn',
                                        'deleted' => 'Đã xóa'
                                    ];
                                ?>
                                <span class="badge bg-<?php echo e($statusColors[$document->status] ?? 'secondary'); ?>">
                                    <?php echo e($statusLabels[$document->status] ?? ucfirst($document->status)); ?>

                                </span>
                            </td>
                            <td><?php echo e($document->created_at->format('d/m/Y')); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <?php if($document->status === 'published'): ?>
                                        <form method="POST" action="<?php echo e(route('admin.posts.status', $document->id)); ?>" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <input type="hidden" name="status" value="hidden">
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <i class="fas fa-eye-slash"></i> Ẩn
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if($document->status === 'hidden'): ?>
                                        <form method="POST" action="<?php echo e(route('admin.posts.status', $document->id)); ?>" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <input type="hidden" name="status" value="published">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-eye"></i> Hiện
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" action="<?php echo e(route('admin.posts.status', $document->id)); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <input type="hidden" name="status" value="deleted">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa tài liệu này?')">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Không tìm thấy tài liệu nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        <?php if($documents->hasPages()): ?>
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($documents->appends(request()->query())->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\xampp\htdocs\DATN_Uniclubs\resources\views/admin/learning-materials/index.blade.php ENDPATH**/ ?>