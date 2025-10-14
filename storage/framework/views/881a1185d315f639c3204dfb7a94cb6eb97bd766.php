<?php $__env->startSection('title', 'Tài nguyên CLB - CLB Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-1">📦 Tài nguyên CLB</h1>
        <p class="text-muted mb-0">Quản lý mẫu đơn, hình ảnh, video, tài liệu và các tài nguyên khác</p>
    </div>
    <div class="header-actions">
        <a href="<?php echo e(route('admin.club-resources.create')); ?>" class="btn btn-success btn-lg">
            <i class="fas fa-plus-circle"></i> Thêm tài nguyên mới
        </a>
    </div>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fas fa-filter"></i> Bộ lọc và tìm kiếm</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('admin.club-resources.index')); ?>" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">Tìm kiếm</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Tìm kiếm tài nguyên..."
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
                <label class="form-label small text-muted">Loại tài nguyên</label>
                <select name="resource_type" class="form-select">
                    <option value="">Tất cả loại</option>
                    <option value="form" <?php echo e(request('resource_type') == 'form' ? 'selected' : ''); ?>>📋 Mẫu đơn</option>
                    <option value="image" <?php echo e(request('resource_type') == 'image' ? 'selected' : ''); ?>>🖼️ Hình ảnh</option>
                    <option value="video" <?php echo e(request('resource_type') == 'video' ? 'selected' : ''); ?>>🎥 Video</option>
                    <option value="pdf" <?php echo e(request('resource_type') == 'pdf' ? 'selected' : ''); ?>>📄 PDF</option>
                    <option value="document" <?php echo e(request('resource_type') == 'document' ? 'selected' : ''); ?>>📝 Tài liệu</option>
                    <option value="guide" <?php echo e(request('resource_type') == 'guide' ? 'selected' : ''); ?>>📖 Hướng dẫn</option>
                    <option value="other" <?php echo e(request('resource_type') == 'other' ? 'selected' : ''); ?>>📦 Khác</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>✅ Hoạt động</option>
                    <option value="inactive" <?php echo e(request('status') == 'inactive' ? 'selected' : ''); ?>>⏸️ Tạm dừng</option>
                    <option value="archived" <?php echo e(request('status') == 'archived' ? 'selected' : ''); ?>>📦 Lưu trữ</option>
                    <option value="deleted" <?php echo e(request('status') == 'deleted' ? 'selected' : ''); ?>>🗑️ Đã xóa</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <a href="<?php echo e(route('admin.club-resources.index')); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách tài nguyên -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-list"></i> Danh sách tài nguyên</h6>
            <span class="badge bg-primary"><?php echo e($resources->total()); ?> tài nguyên</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">ID</th>
                        <th class="border-0">📦 Tài nguyên</th>
                        <th class="border-0">🏷️ Loại</th>
                        <th class="border-0">🏢 Câu lạc bộ</th>
                        <th class="border-0">👤 Người tạo</th>
                        <th class="border-0">📊 Thống kê</th>
                        <th class="border-0">📄 Trạng thái</th>
                        <th class="border-0">📅 Ngày tạo</th>
                        <th class="border-0">⚙️ Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="align-middle">
                            <td>
                                <span class="badge bg-light text-dark">#<?php echo e($resource->id); ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if($resource->thumbnail_url): ?>
                                        <img src="<?php echo e($resource->thumbnail_url); ?>" 
                                             alt="<?php echo e($resource->title); ?>" 
                                             class="rounded me-3" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-file fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong class="text-dark mb-1"><?php echo e(Str::limit($resource->title, 30)); ?></strong>
                                        <?php if($resource->file_size): ?>
                                            <br><small class="text-muted"><?php echo e($resource->formatted_file_size); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info rounded-pill">
                                    <?php echo e($resource->resource_type_label); ?>

                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-building text-muted me-2"></i>
                                    <span><?php echo e(Str::limit($resource->club->name ?? 'N/A', 20)); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user text-muted me-2"></i>
                                    <span><?php echo e(Str::limit($resource->user->name ?? 'N/A', 15)); ?></span>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="fas fa-eye"></i> <?php echo e($resource->view_count); ?><br>
                                    <i class="fas fa-download"></i> <?php echo e($resource->download_count); ?>

                                </small>
                            </td>
                            <td>
                                <?php
                                    $statusConfig = [
                                        'active' => ['color' => 'success', 'label' => '✅ Hoạt động'],
                                        'inactive' => ['color' => 'warning', 'label' => '⏸️ Tạm dừng'],
                                        'archived' => ['color' => 'secondary', 'label' => '📦 Lưu trữ']
                                    ];
                                    $config = $statusConfig[$resource->status] ?? ['color' => 'secondary', 'label' => $resource->status];
                                ?>
                                <span class="badge bg-<?php echo e($config['color']); ?> rounded-pill">
                                    <?php echo e($config['label']); ?>

                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark"><?php echo e($resource->created_at->format('d/m/Y')); ?></span>
                                    <small class="text-muted"><?php echo e($resource->created_at->format('H:i')); ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group-vertical btn-group-sm" role="group">
                                    <div class="btn-group btn-group-sm mb-1" role="group">
                                        <a href="<?php echo e(route('admin.club-resources.show', $resource->id)); ?>" class="btn btn-outline-info btn-sm" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if($resource->file_path): ?>
                                            <a href="<?php echo e(route('admin.club-resources.download', $resource->id)); ?>" class="btn btn-outline-success btn-sm" title="Tải xuống">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?php echo e(route('admin.club-resources.edit', $resource->id)); ?>" class="btn btn-outline-warning btn-sm" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="<?php echo e(route('admin.club-resources.destroy', $resource->id)); ?>" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa tài nguyên này?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Không tìm thấy tài nguyên nào</h5>
                                    <p class="text-muted mb-3">Hãy thử thay đổi bộ lọc hoặc thêm tài nguyên mới</p>
                                    <a href="<?php echo e(route('admin.club-resources.create')); ?>" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Thêm tài nguyên đầu tiên
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        <?php if($resources->hasPages()): ?>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Hiển thị <?php echo e($resources->firstItem() ?? 0); ?> - <?php echo e($resources->lastItem() ?? 0); ?> 
                        trong tổng số <?php echo e($resources->total()); ?> tài nguyên
                    </div>
                    <div>
                        <?php echo e($resources->appends(request()->query())->links()); ?>

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

.badge.rounded-pill {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\xampp\htdocs\DATN_Uniclubs\resources\views/admin/club-resources/index.blade.php ENDPATH**/ ?>