

<?php $__env->startSection('title', 'Thêm Tài nguyên - CLB Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-1">Thêm Tài nguyên Mới</h1>
        <p class="text-muted mb-0">Tạo mẫu đơn, tải lên hình ảnh, video, tài liệu cho CLB</p>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo e(route('admin.club-resources.index')); ?>">Tài nguyên CLB</a></li>
            <li class="breadcrumb-item active">Thêm mới</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-edit"></i> Thông tin tài nguyên</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('admin.club-resources.store')); ?>" enctype="multipart/form-data" id="resourceForm">
                    <?php echo csrf_field(); ?>
                    
                    <!-- Tiêu đề -->
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold">
                            Tiêu đề tài nguyên 
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               id="title" 
                               name="title" 
                               value="<?php echo e(old('title')); ?>" 
                               placeholder="Ví dụ: Mẫu đơn xin gia nhập CLB"
                               minlength="5"
                               maxlength="255"
                               required>
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <div class="form-text">Tiêu đề ngắn gọn, mô tả rõ ràng tài nguyên</div>
                    </div>

                    <!-- Mô tả -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-bold">
                            Mô tả
                        </label>
                        <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                  id="description" 
                                  name="description" 
                                  rows="4" 
                                  placeholder="Mô tả chi tiết về tài nguyên này..."
                                  maxlength="1000"><?php echo e(old('description')); ?></textarea>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <div class="form-text">Mô tả giúp người dùng hiểu rõ hơn về tài nguyên</div>
                    </div>

                    <!-- Upload file hoặc link -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            Tải lên file hoặc nhập link
                        </label>
                        
                        <ul class="nav nav-tabs mb-3" id="uploadTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="file-tab" data-bs-toggle="tab" data-bs-target="#file-upload" type="button" role="tab">
                                    <i class="fas fa-file-upload"></i> Tải file lên
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="link-tab" data-bs-toggle="tab" data-bs-target="#link-input" type="button" role="tab">
                                    <i class="fas fa-link"></i> Nhập link
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="uploadTabContent">
                            <!-- File Upload Tab -->
                            <div class="tab-pane fade show active" id="file-upload" role="tabpanel">
                                <div class="upload-area border-2 border-dashed rounded p-4 text-center">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Kéo thả file vào đây hoặc click để chọn</h6>
                                    <p class="text-muted small mb-3">Hỗ trợ: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, GIF, MP4 (Tối đa 20MB)</p>
                                    <input type="file" 
                                           id="file" 
                                           name="file" 
                                           class="d-none"
                                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.avi">
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('file').click()">
                                        <i class="fas fa-plus"></i> Chọn file
                                    </button>
                                </div>
                                <div id="filePreview" class="mt-3" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fas fa-file"></i> <span id="fileName"></span>
                                        <button type="button" class="btn-close float-end" onclick="clearFile()"></button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Link Input Tab -->
                            <div class="tab-pane fade" id="link-input" role="tabpanel">
                                <input type="url" 
                                       class="form-control <?php $__errorArgs = ['external_link'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="external_link" 
                                       name="external_link" 
                                       value="<?php echo e(old('external_link')); ?>" 
                                       placeholder="https://example.com/document.pdf">
                                <?php $__errorArgs = ['external_link'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <div class="form-text">Nhập link Google Drive, Dropbox, YouTube,...</div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
                
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-cog"></i> Cài đặt</h6>
            </div>
            <div class="card-body">
                <!-- Câu lạc bộ -->
                <div class="mb-3">
                    <label for="club_id" class="form-label fw-bold">
                        <i class="fas fa-users text-secondary"></i> Câu lạc bộ 
                        <span class="text-danger">*</span>
                    </label>
                    <select class="form-select <?php $__errorArgs = ['club_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="club_id" name="club_id" required>
                        <option value="">Chọn câu lạc bộ</option>
                        <?php $__currentLoopData = $clubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $club): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($club->id); ?>" <?php echo e(old('club_id') == $club->id ? 'selected' : ''); ?>>
                                <?php echo e($club->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['club_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Loại tài nguyên -->
                <div class="mb-3">
                    <label for="resource_type" class="form-label fw-bold">
                        <i class="fas fa-tag text-secondary"></i> Loại tài nguyên 
                        <span class="text-danger">*</span>
                    </label>
                    <select class="form-select <?php $__errorArgs = ['resource_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="resource_type" name="resource_type" required>
                        <option value="form" <?php echo e(old('resource_type') == 'form' ? 'selected' : ''); ?>>📋 Mẫu đơn</option>
                        <option value="image" <?php echo e(old('resource_type') == 'image' ? 'selected' : ''); ?>>🖼️ Hình ảnh</option>
                        <option value="video" <?php echo e(old('resource_type') == 'video' ? 'selected' : ''); ?>>🎥 Video</option>
                        <option value="pdf" <?php echo e(old('resource_type') == 'pdf' ? 'selected' : ''); ?>>📄 PDF</option>
                        <option value="document" <?php echo e(old('resource_type') == 'document' ? 'selected' : ''); ?>>📝 Tài liệu</option>
                        <option value="guide" <?php echo e(old('resource_type') == 'guide' ? 'selected' : ''); ?>>📖 Hướng dẫn</option>
                        <option value="other" <?php echo e(old('resource_type') == 'other' ? 'selected' : ''); ?>>📦 Khác</option>
                    </select>
                    <?php $__errorArgs = ['resource_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Trạng thái -->
                <div class="mb-3">
                    <label for="status" class="form-label fw-bold">
                        <i class="fas fa-toggle-on text-secondary"></i> Trạng thái 
                        <span class="text-danger">*</span>
                    </label>
                    <select class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="status" name="status" required>
                        <option value="active" <?php echo e(old('status') == 'active' ? 'selected' : ''); ?>>✅ Hoạt động</option>
                        <option value="inactive" <?php echo e(old('status') == 'inactive' ? 'selected' : ''); ?>>⏸️ Tạm dừng</option>
                        <option value="archived" <?php echo e(old('status') == 'archived' ? 'selected' : ''); ?>>📦 Lưu trữ</option>
                    </select>
                    <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        <!-- Nút lưu -->
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Lưu Tài nguyên
            </button>
            <a href="<?php echo e(route('admin.club-resources.index')); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
        </div>
    </div>
</div>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
// File upload handling
document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        document.getElementById('fileName').textContent = file.name + ' (' + formatFileSize(file.size) + ')';
        document.getElementById('filePreview').style.display = 'block';
    }
});

function clearFile() {
    document.getElementById('file').value = '';
    document.getElementById('filePreview').style.display = 'none';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Drag and drop
const uploadArea = document.querySelector('.upload-area');

uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadArea.classList.add('border-primary', 'bg-light');
});

uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('border-primary', 'bg-light');
});

uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('border-primary', 'bg-light');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('file').files = files;
        document.getElementById('fileName').textContent = files[0].name + ' (' + formatFileSize(files[0].size) + ')';
        document.getElementById('filePreview').style.display = 'block';
    }
});

// Form validation
document.getElementById('resourceForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const clubId = document.getElementById('club_id').value;
    const resourceType = document.getElementById('resource_type').value;
    const file = document.getElementById('file').files[0];
    const externalLink = document.getElementById('external_link').value.trim();
    
    if (title.length < 5) {
        alert('Tiêu đề phải có ít nhất 5 ký tự');
        e.preventDefault();
        return false;
    }
    
    if (!clubId) {
        alert('Vui lòng chọn câu lạc bộ');
        e.preventDefault();
        return false;
    }
    
    if (!resourceType) {
        alert('Vui lòng chọn loại tài nguyên');
        e.preventDefault();
        return false;
    }
    
    if (!file && !externalLink) {
        alert('Vui lòng tải lên file hoặc nhập link');
        e.preventDefault();
        return false;
    }
    
    if (file && file.size > 20 * 1024 * 1024) {
        alert('File không được vượt quá 20MB');
        e.preventDefault();
        return false;
    }
    
    return true;
});
</script>

<style>
.upload-area {
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #0d6efd !important;
    background-color: #f8f9fa;
}

.upload-area.border-primary {
    border-color: #0d6efd !important;
    background-color: #e7f3ff;
}

.nav-tabs .nav-link {
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    font-weight: 600;
}
</style>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\xampp\htdocs\DATN_Uniclubs\resources\views/admin/club-resources/create.blade.php ENDPATH**/ ?>